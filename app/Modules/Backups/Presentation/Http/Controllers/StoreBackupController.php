<?php

namespace App\Modules\Backups\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backups\Application\CreateBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreBackupController extends Controller
{
    public function __invoke(Request $request, CreateBackup $create): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('backups.manage'), 403);
        $create->execute($request->user());

        return back()->with('success', 'Respaldo creado. Verifica su integridad antes de usarlo.');
    }
}
