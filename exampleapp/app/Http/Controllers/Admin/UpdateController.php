<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class UpdateController extends Controller
{
    public function __construct(private readonly AppUpdateService $appUpdateService)
    {
    }

    public function index(): View
    {
        $status = $this->appUpdateService->getStatus();

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

        if (($status['is_up_to_date'] ?? null) === true) {
            return redirect()
                ->route('admin.update.index')
                ->with('update_success', 'App is already updated to the latest version (' . ($status['current_version'] ?? 'current') . ').');
        }

        $steps = [
            'Run database migrations' => ['migrate', ['--force' => true]],
            'Clear application cache' => ['cache:clear', []],
            'Clear config cache' => ['config:clear', []],
            'Clear view cache' => ['view:clear', []],
        ];

        $logs = [];

        foreach ($steps as $label => [$command, $arguments]) {
            $exitCode = Artisan::call($command, $arguments);
            $output = trim(Artisan::output());

            $logs[] = [
                'label' => $label,
                'command' => $command,
                'exit_code' => $exitCode,
                'output' => $output,
            ];

            if ($exitCode !== 0) {
                return redirect()
                    ->route('admin.update.index')
                    ->with('update_error', 'Update failed while running: ' . $label)
                    ->with('update_logs', $logs);
            }
        }

        $this->appUpdateService->clearStatusCache();

        return redirect()
            ->route('admin.update.index')
            ->with('update_success', 'Update steps completed successfully. If new code was deployed, this tenant is now updated.')
            ->with('update_logs', $logs);
    }
}
