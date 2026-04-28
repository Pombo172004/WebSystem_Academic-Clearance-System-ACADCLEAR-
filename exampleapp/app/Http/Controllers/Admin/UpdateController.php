<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

class UpdateController extends Controller
{
    public function __construct(private readonly AppUpdateService $appUpdateService)
    {
    }

    public function index(Request $request): View
    {
        $forceRefresh = $request->boolean('refresh');
        $status = $this->appUpdateService->getStatus($forceRefresh);

        return view('admin.update.index', [
            'currentVersion' => $status['current_version'] ?? config('app.version', '1.0.0'),
            'latestVersion' => $status['latest_version'] ?? null,
            'hasUpdate' => (bool) ($status['has_update'] ?? false),
            'isUpToDate' => $status['is_up_to_date'] ?? null,
            'updateError' => $status['error'] ?? null,
        ]);
    }

    public function install(Request $request): RedirectResponse
    {
        $status = $this->appUpdateService->getStatus(true);
        $latestVersion = isset($status['latest_version']) ? trim((string) $status['latest_version']) : '';

        if (($status['is_up_to_date'] ?? null) === true) {
            return redirect()
                ->route('admin.update.index')
                ->with('update_success', 'App is already updated to the latest version (' . ($status['current_version'] ?? 'current') . ').');
        }

        $repo = trim((string) config('services.app_updates.github_repo', ''));
        if ($repo === '') {
            return redirect()
                ->route('admin.update.index')
                ->with('update_error', 'GitHub repository is not configured. Set APP_GITHUB_REPO in .env.');
        }

        $logs = [];
        $workspace = storage_path('app/update-temp/' . now()->format('Ymd_His') . '_' . Str::random(8));
        $archivePath = $workspace . DIRECTORY_SEPARATOR . 'release.zip';
        $extractPath = $workspace . DIRECTORY_SEPARATOR . 'extract';

        try {
            File::ensureDirectoryExists($extractPath);

            $download = $this->downloadReleaseArchive($repo, $latestVersion, $archivePath);
            $logs[] = $download['log'];
            if (! $download['success']) {
                return $this->redirectWithLogs('Update failed while downloading the GitHub release archive.', $logs);
            }

            $extract = $this->extractArchive($archivePath, $extractPath);
            $logs[] = $extract['log'];
            if (! $extract['success']) {
                return $this->redirectWithLogs('Update failed while extracting the release archive.', $logs);
            }

            $sourceRoot = $this->detectExtractedRoot($extractPath);
            if ($sourceRoot === null) {
                $logs[] = [
                    'label' => 'Locate extracted release files',
                    'command' => 'scan extracted archive',
                    'exit_code' => 1,
                    'output' => 'Unable to locate the extracted release folder.',
                ];

                return $this->redirectWithLogs('Update failed because the extracted release files could not be found.', $logs);
            }

            $copy = $this->applyReleaseFiles($sourceRoot, base_path());
            $logs[] = $copy['log'];
            if (! $copy['success']) {
                return $this->redirectWithLogs('Update failed while applying the new release files.', $logs);
            }

            foreach ($this->postInstallSteps() as $step) {
                $result = $this->runProcessStep($step['label'], $step['command'], (int) config('services.app_updates.install_timeout_seconds', 1800));
                $logs[] = $result['log'];

                if (! $result['success']) {
                    return $this->redirectWithLogs('Update failed while running: ' . $step['label'], $logs);
                }
            }

            if ($latestVersion !== '') {
                File::put(base_path('VERSION'), $latestVersion . PHP_EOL);
                $logs[] = [
                    'label' => 'Persist installed version',
                    'command' => 'write VERSION',
                    'exit_code' => 0,
                    'output' => 'VERSION updated to ' . $latestVersion,
                ];
            }

            $this->appUpdateService->clearStatusCache();

            return redirect()
                ->route('admin.update.index')
                ->with('update_success', 'GitHub release installed successfully' . ($latestVersion !== '' ? ' to ' . $latestVersion : '') . '.')
                ->with('update_logs', $logs);
        } catch (\Throwable $e) {
            $logs[] = [
                'label' => 'Unhandled update error',
                'command' => 'update release workflow',
                'exit_code' => 1,
                'output' => $e->getMessage(),
            ];

            return $this->redirectWithLogs('Update failed unexpectedly while installing the release.', $logs);
        } finally {
            if (File::exists($workspace)) {
                File::deleteDirectory($workspace);
            }
        }
    }

    private function downloadReleaseArchive(string $repo, string $version, string $archivePath): array
    {
        $headers = $this->githubHeaders();
        $verifySsl = (bool) config('services.app_updates.verify_ssl', true);
        $ref = $version !== '' ? $version : 'HEAD';
        $url = "https://api.github.com/repos/{$repo}/zipball/{$ref}";

        try {
            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => $verifySsl])
                ->timeout(120)
                ->sink($archivePath)
                ->get($url);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'log' => [
                    'label' => 'Download latest release archive',
                    'command' => $url,
                    'exit_code' => 1,
                    'output' => $e->getMessage(),
                ],
            ];
        }

        return [
            'success' => $response->successful() && File::exists($archivePath),
            'log' => [
                'label' => 'Download latest release archive',
                'command' => $url,
                'exit_code' => $response->status(),
                'output' => $response->successful() ? 'Release archive downloaded successfully.' : ('GitHub returned HTTP ' . $response->status()),
            ],
        ];
    }

    private function extractArchive(string $archivePath, string $extractPath): array
    {
        $zip = new ZipArchive();
        $openResult = $zip->open($archivePath);

        if ($openResult !== true) {
            return [
                'success' => false,
                'log' => [
                    'label' => 'Extract release archive',
                    'command' => $archivePath,
                    'exit_code' => (int) $openResult,
                    'output' => 'ZipArchive could not open the downloaded release archive.',
                ],
            ];
        }

        $success = $zip->extractTo($extractPath);
        $zip->close();

        return [
            'success' => $success,
            'log' => [
                'label' => 'Extract release archive',
                'command' => $archivePath,
                'exit_code' => $success ? 0 : 1,
                'output' => $success ? 'Release archive extracted successfully.' : 'ZipArchive failed to extract the release archive.',
            ],
        ];
    }

    private function detectExtractedRoot(string $extractPath): ?string
    {
        $entries = collect(File::directories($extractPath))
            ->filter(fn (string $path) => File::exists($path . DIRECTORY_SEPARATOR . 'composer.json'))
            ->values();

        if ($entries->isNotEmpty()) {
            return $entries->first();
        }

        if (File::exists($extractPath . DIRECTORY_SEPARATOR . 'composer.json')) {
            return $extractPath;
        }

        return null;
    }

    private function applyReleaseFiles(string $sourceRoot, string $targetRoot): array
    {
        $preserved = $this->preservedPaths();
        $copied = [];

        foreach (File::allFiles($sourceRoot, true) as $file) {
            $sourcePath = $file->getPathname();
            $relativePath = str_replace('\\', '/', ltrim(Str::after($sourcePath, $sourceRoot), DIRECTORY_SEPARATOR));

            if ($relativePath === '' || $this->shouldPreservePath($relativePath, $preserved)) {
                continue;
            }

            $targetPath = $targetRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($sourcePath, $targetPath);
            $copied[] = $relativePath;
        }

        return [
            'success' => true,
            'log' => [
                'label' => 'Apply release files to application',
                'command' => 'copy extracted release into app',
                'exit_code' => 0,
                'output' => 'Updated ' . count($copied) . ' files from the release archive.',
            ],
        ];
    }

    private function postInstallSteps(): array
    {
        $steps = [];

        if (File::exists(base_path('composer.json'))) {
            $steps[] = [
                'label' => 'Install PHP dependencies',
                'command' => $this->composerCommand(),
            ];
        }

        if (File::exists(base_path('package.json'))) {
            $steps[] = [
                'label' => 'Install Node dependencies',
                'command' => $this->nodeCommand(['install']),
            ];
            $steps[] = [
                'label' => 'Build frontend assets',
                'command' => $this->nodeCommand(['run', 'build']),
            ];
        }

        $steps[] = [
            'label' => 'Run database migrations',
            'command' => $this->phpCommand(['artisan', 'migrate', '--force']),
        ];
        $steps[] = [
            'label' => 'Clear optimized caches',
            'command' => $this->phpCommand(['artisan', 'optimize:clear']),
        ];
        $steps[] = [
            'label' => 'Refresh config cache after version update',
            'command' => $this->phpCommand(['artisan', 'config:clear']),
        ];

        return $steps;
    }

    private function runProcessStep(string $label, array $command, int $timeout): array
    {
        try {
            $result = \Illuminate\Support\Facades\Process::timeout($timeout)
                ->path(base_path())
                ->run($command);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'log' => [
                    'label' => $label,
                    'command' => $this->stringifyCommand($command),
                    'exit_code' => 1,
                    'output' => $e->getMessage(),
                ],
            ];
        }

        $output = trim($result->output() . PHP_EOL . $result->errorOutput());

        return [
            'success' => $result->successful(),
            'log' => [
                'label' => $label,
                'command' => $this->stringifyCommand($command),
                'exit_code' => $result->exitCode(),
                'output' => $output,
            ],
        ];
    }

    private function githubHeaders(): array
    {
        $headers = [
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => (string) config('app.name', 'Laravel') . '-release-installer',
        ];

        $token = trim((string) config('services.app_updates.github_token', ''));
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }

    private function preservedPaths(): array
    {
        return [
            '.env',
            '.git/*',
            '.git',
            'storage/*',
            'storage',
            'vendor/*',
            'vendor',
            'node_modules/*',
            'node_modules',
        ];
    }

    private function shouldPreservePath(string $relativePath, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $relativePath)) {
                return true;
            }
        }

        return false;
    }

    private function phpCommand(array $arguments): array
    {
        return array_merge([$this->resolvePhpBinary()], $arguments);
    }

    private function composerCommand(): array
    {
        return [
            $this->resolveExecutable(PHP_OS_FAMILY === 'Windows' ? 'composer.bat' : 'composer'),
            'install',
            '--no-interaction',
            '--prefer-dist',
        ];
    }

    private function nodeCommand(array $arguments): array
    {
        return array_merge([$this->resolveExecutable(PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm')], $arguments);
    }

    private function resolvePhpBinary(): string
    {
        return PHP_BINARY !== '' ? PHP_BINARY : 'php';
    }

    private function resolveExecutable(string $command): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return $command;
        }

        $candidates = [
            $command,
            'C:/ProgramData/ComposerSetup/bin/' . $command,
            'C:/Program Files/nodejs/' . $command,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === $command || File::exists($candidate)) {
                return $candidate;
            }
        }

        return $command;
    }

    private function parseGitStatusOutput(string $output): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($output));
        $files = [];

        foreach ($lines as $line) {
            $line = rtrim((string) $line);
            if ($line === '') {
                continue;
            }

            if (! preg_match('/^(..)\s+(.*)$/', $line, $matches)) {
                continue;
            }

            $files[] = [
                'code' => $matches[1],
                'path' => trim($matches[2]),
                'raw' => $line,
            ];
        }

        return $files;
    }

    private function formatDirtyFilesForLog(array $dirtyFiles): string
    {
        return implode(PHP_EOL, array_map(fn (array $file) => (string) ($file['raw'] ?? $file['path'] ?? ''), $dirtyFiles));
    }

    private function stringifyCommand(array $command): string
    {
        return implode(' ', array_map(function ($part) {
            $part = (string) $part;

            return str_contains($part, ' ') ? '"' . $part . '"' : $part;
        }, Arr::flatten($command)));
    }

    private function redirectWithLogs(string $message, array $logs): RedirectResponse
    {
        return redirect()
            ->route('admin.update.index')
            ->with('update_error', $message)
            ->with('update_logs', $logs);
    }
}
