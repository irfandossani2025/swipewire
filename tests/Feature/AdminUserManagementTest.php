<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_non_administrator_cannot_view_customer_administration(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_an_administrator_can_disable_a_customer_account(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_active' => true]);

        $this->actingAs($administrator)
            ->patch(route('admin.users.toggle-status', $customer))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'is_active' => false,
        ]);
    }

    public function test_an_administrator_cannot_disable_another_administrator(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        $otherAdministrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)
            ->patch(route('admin.users.toggle-status', $otherAdministrator))
            ->assertUnprocessable();
    }
}
