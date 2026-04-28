<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;

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
        $status = $this->appUpdateService->getStatus();
        $latestVersion = isset($status['latest_version']) ? trim((string) $status['latest_version']) : '';

        if (($status['is_up_to_date'] ?? null) === true) {
            return redirect()
                ->route('admin.update.index')
                ->with('update_success', 'App is already updated to the latest version (' . ($status['current_version'] ?? 'current') . ').');
        }

        $logs = [];
        $scriptPath = base_path('scripts/apply-latest-update.ps1');
        $branch = (string) config('services.app_updates.branch', 'master');

        if (! File::exists($scriptPath)) {
            return redirect()
                ->route('admin.update.index')
                ->with('update_error', 'Update script not found at: ' . $scriptPath);
        }

        $command = $this->buildUpdateCommand($scriptPath, $branch);

        try {
            $result = Process::timeout((int) config('services.app_updates.install_timeout_seconds', 1800))
                ->path(base_path())
                ->run($command);
        } catch (\Throwable $e) {
            $logs[] = [
                'label' => 'Pull latest code and install release',
                'command' => $this->stringifyCommand($command),
                'exit_code' => 1,
                'output' => $e->getMessage(),
            ];

            return redirect()
                ->route('admin.update.index')
                ->with('update_error', 'Unable to start the update process on this server.')
                ->with('update_logs', $logs);
        }

        $output = trim($result->output() . PHP_EOL . $result->errorOutput());

        $logs[] = [
            'label' => 'Pull latest code and install release',
            'command' => $this->stringifyCommand($command),
            'exit_code' => $result->exitCode(),
            'output' => $output,
        ];

        if (! $result->successful()) {
            return redirect()
                ->route('admin.update.index')
                ->with('update_error', 'Update failed while pulling the latest release from GitHub.')
                ->with('update_logs', $logs);
        }

        // Persist installed release version so the dashboard "Current Version" reflects the update.
        if ($latestVersion !== '') {
            try {
                File::put(base_path('VERSION'), $latestVersion . PHP_EOL);

                $logs[] = [
                    'label' => 'Persist installed version',
                    'command' => 'write VERSION',
                    'exit_code' => 0,
                    'output' => 'VERSION updated to ' . $latestVersion,
                ];
            } catch (\Throwable $e) {
                $logs[] = [
                    'label' => 'Persist installed version',
                    'command' => 'write VERSION',
                    'exit_code' => 1,
                    'output' => $e->getMessage(),
                ];

                return redirect()
                    ->route('admin.update.index')
                    ->with('update_error', 'Update finished but failed to store installed version: ' . $e->getMessage())
                    ->with('update_logs', $logs);
            }
        }

        $this->appUpdateService->clearStatusCache();

        return redirect()
            ->route('admin.update.index')
            ->with('update_success', 'GitHub update installed successfully' . ($latestVersion !== '' ? ' to ' . $latestVersion : '') . '.')
            ->with('update_logs', $logs);
    }

    private function buildUpdateCommand(string $scriptPath, string $branch): array
    {
        $shell = PHP_OS_FAMILY === 'Windows' ? 'powershell.exe' : 'pwsh';

        return [
            $shell,
            '-ExecutionPolicy',
            'Bypass',
            '-File',
            $scriptPath,
            '-Branch',
            $branch,
        ];
    }

    private function stringifyCommand(array $command): string
    {
        return implode(' ', array_map(function (string $part) {
            return str_contains($part, ' ') ? '"' . $part . '"' : $part;
        }, $command));
    }
}
