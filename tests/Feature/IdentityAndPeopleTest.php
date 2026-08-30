<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Application\BlockUser;
use App\Modules\Identity\Application\CreateAuthorizationRequest;
use App\Modules\Identity\Application\DecideAuthorizationRequest;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentityAndPeopleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_person_with_multiple_classifications(): void
    {
        $owner = $this->userWithRole('owner');

        $response = $this->actingAs($owner)->post(route('people.store'), [
            'name' => 'María Pérez',
            'document_type' => 'CC',
            'document_number' => '123456789',
            'email' => 'maria@example.com',
            'phone' => '3001234567',
            'notes' => null,
            'is_active' => true,
            'classifications' => ['resident', 'employee'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('people', 1);
        $this->assertDatabaseCount('person_classifications', 2);
        $this->assertDatabaseHas('audit_events', ['action' => 'person.created']);
    }

    public function test_blocking_a_user_revokes_sessions_and_prevents_login(): void
    {
        $owner = $this->userWithRole('owner');
        $user = User::factory()->create(['password' => 'password']);

        app(BlockUser::class)->execute($user, $owner, 'Acceso suspendido por seguridad.');

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authorization_requires_a_different_user_with_the_required_permission(): void
    {
        $requester = $this->userWithRole('production');
        $approver = $this->userWithRole('owner');
        $resource = Person::factory()->create();

        $authorization = app(CreateAuthorizationRequest::class)->execute(
            'negative-stock',
            'inventory.authorize-negative',
            $resource,
            $requester,
            'Se requiere terminar la producción.',
        );

        app(DecideAuthorizationRequest::class)->execute(
            $authorization,
            $approver,
            AuthorizationStatus::Approved,
            'Aprobado por inventario en tránsito.',
        );

        $this->assertDatabaseHas('authorization_requests', [
            'id' => $authorization->id,
            'status' => AuthorizationStatus::Approved->value,
            'approved_by' => $approver->id,
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        app(EnsureAccessControlCatalog::class)->execute();
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->sole());

        return $user;
    }
}
