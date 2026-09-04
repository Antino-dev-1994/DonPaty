<?php

namespace App\Modules\Backups\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Backups\Domain\Models\BackupRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadBackupController extends Controller
{
    public function __invoke(Request $request, BackupRun $backup, RecordAuditEvent $audit): StreamedResponse
    {
        abort_unless($request->user()->hasPermission('backups.manage'), 403);
        abort_unless($backup->status === 'completed' && $backup->path, 404);
        $audit->execute('backup.downloaded', $backup, $request->user(), after: ['sha256' => $backup->sha256]);

        return Storage::disk($backup->disk)->download($backup->path, "donpaty-backup-{$backup->id}.zip", ['X-Content-Type-Options' => 'nosniff']);
    }
}
