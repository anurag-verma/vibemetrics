<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_admin_cannot_delete_their_account_via_admin_profile(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->delete(route('admin.profile.destroy'), [
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('error', 'You cannot delete the last admin account.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
