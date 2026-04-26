<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppUpdateService
{
    public function getStatus(bool $forceRefresh = false): array
    {
        $currentVersion = (string) config('app.version', '1.0.0');
        $repo = trim((string) config('services.app_updates.github_repo', ''));
        $cacheMinutes = (int) config('services.app_updates.cache_minutes', 15);

        if ($repo === '') {
            return [
                'current_version' => $currentVersion,
                'latest_version' => null,
                'has_update' => false,
                'is_up_to_date' => null,
                'error' => 'GitHub repository is not configured. Set APP_GITHUB_REPO in .env (owner/repo).',
            ];
        }

        $cacheKey = $this->makeCacheKey($repo, $currentVersion);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        $status = Cache::remember($cacheKey, now()->addMinutes(max($cacheMinutes, 1)), function () use ($repo, $currentVersion) {
            $latestVersion = $this->fetchLatestVersion($repo);

            if ($latestVersion === null) {
                return [
                    'current_version' => $currentVersion,
                    'latest_version' => null,
                    'has_update' => false,
                    'is_up_to_date' => null,
                    'error' => 'Unable to check GitHub releases right now.',
                ];
            }

            $normalizedCurrent = $this->normalizeVersion($currentVersion);
            $normalizedLatest = $this->normalizeVersion($latestVersion);

            // Guard against stale/lagging API or cache responses.
            if (version_compare($normalizedLatest, $normalizedCurrent, '<')) {
                return [
                    'current_version' => $currentVersion,
                    'latest_version' => $currentVersion,
                    'has_update' => false,
                    'is_up_to_date' => true,
                    'error' => null,
                ];
            }

            $hasUpdate = version_compare($normalizedLatest, $normalizedCurrent, '>');

            return [
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'has_update' => $hasUpdate,
                'is_up_to_date' => !$hasUpdate,
                'error' => null,
            ];
        });

        $latestVersion = isset($status['latest_version']) ? (string) $status['latest_version'] : null;
        if ($latestVersion !== null && $latestVersion !== '') {
            $normalizedCurrent = $this->normalizeVersion($currentVersion);
            $normalizedLatest = $this->normalizeVersion($latestVersion);

            if (version_compare($normalizedLatest, $normalizedCurrent, '<')) {
                $status['latest_version'] = $currentVersion;
                $status['has_update'] = false;
                $status['is_up_to_date'] = true;
                $status['error'] = null;
            }
        }

        return $status;
    }

    public function clearStatusCache(): void
    {
        $repo = trim((string) config('services.app_updates.github_repo', ''));
        $currentVersion = (string) config('app.version', '1.0.0');

        if ($repo === '') {
            return;
        }

        $cacheKey = $this->makeCacheKey($repo, $currentVersion);
        Cache::forget($cacheKey);
    }

    private function makeCacheKey(string $repo, string $currentVersion): string
    {
        return 'app_update_status:' . md5($repo . '|' . $currentVersion);
    }

    private function fetchLatestVersion(string $repo): ?string
    {
        $token = trim((string) config('services.app_updates.github_token', ''));
        $verifySsl = (bool) config('services.app_updates.verify_ssl', true);
        $headers = [
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => (string) config('app.name', 'Laravel') . '-update-checker',
        ];

        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        try {
            $releaseResponse = Http::withHeaders($headers)
                ->withOptions(['verify' => $verifySsl])
                ->timeout(8)
                ->get("https://api.github.com/repos/{$repo}/releases/latest");

            if ($releaseResponse->successful()) {
                $tag = trim((string) $releaseResponse->json('tag_name'));
                if ($tag !== '') {
                    return $tag;
                }
            }
        } catch (ConnectionException|Throwable $e) {
            Log::warning('GitHub release check failed.', [
                'repo' => $repo,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $tagsResponse = Http::withHeaders($headers)
                ->withOptions(['verify' => $verifySsl])
                ->timeout(8)
                ->get("https://api.github.com/repos/{$repo}/tags", ['per_page' => 1]);

            if ($tagsResponse->successful()) {
                $first = $tagsResponse->json('0.name');
                if (is_string($first) && trim($first) !== '') {
                    return trim($first);
                }
            }
        } catch (ConnectionException|Throwable $e) {
            Log::warning('GitHub tag check failed.', [
                'repo' => $repo,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function normalizeVersion(string $version): string
    {
        return ltrim(trim($version), "vV ");
    }
}
