<?php

namespace App\Modules\Backups\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backups\Domain\Models\BackupRun;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListBackupsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('backups.manage'), 403);

        return Inertia::render('backups/Index', [
            'backups' => BackupRun::query()->with('creator:id,name')->latest()->paginate(20)->through(fn (BackupRun $backup) => [
                'id' => $backup->id, 'status' => $backup->status, 'driver' => $backup->database_driver,
                'size' => $backup->size, 'sha256' => $backup->sha256,
                'verification_status' => $backup->verification_status, 'error_message' => $backup->error_message,
                'creator' => $backup->creator?->name ?? 'Sistema',
                'created_at' => $backup->created_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i:s'),
                'verified_at' => $backup->verified_at?->timezone(config('regional.display_timezone'))->format('Y-m-d H:i:s'),
                'download_url' => $backup->status === 'completed' ? route('backups.download', $backup) : null,
                'restore_command' => $request->user()->hasPermission('backups.restore') && $backup->verification_status === 'valid'
                    ? "php artisan backups:restore {$backup->id} --confirm=RESTAURAR-{$backup->id}" : null,
            ]),
        ]);
    }
}
