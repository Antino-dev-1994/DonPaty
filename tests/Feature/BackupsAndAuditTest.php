<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Backups\Application\CreateBackup;
use App\Modules\Backups\Application\VerifyBackup;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupsAndAuditTest extends TestCase
{
    use DatabaseTruncation;

    public function test_sqlite_backup_contains_a_valid_manifest(): void
    {
        Storage::fake('local');
        config(['backups.disk' => 'local']);
        $user = User::factory()->create();

        $backup = app(CreateBackup::class)->execute($user);
        $manifest = app(VerifyBackup::class)->execute($backup, $user);

        $this->assertSame('completed', $backup->status);
        $this->assertSame('sqlite', $manifest['database']['driver']);
        $this->assertSame('valid', $backup->fresh()->verification_status);
        Storage::disk('local')->assertExists($backup->path);
    }
}
