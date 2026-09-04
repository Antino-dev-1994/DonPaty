<?php

namespace App\Modules\Backups\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backups\Application\VerifyBackup;
use App\Modules\Backups\Domain\Models\BackupRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyBackupController extends Controller
{
    public function __invoke(Request $request, BackupRun $backup, VerifyBackup $verify): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('backups.manage'), 403);
        $verify->execute($backup, $request->user());

        return back()->with('success', 'Respaldo e integridad verificados.');
    }
}
