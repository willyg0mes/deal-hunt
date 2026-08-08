<?php

use App\Models\User;

test('non-admin cannot broadcast notifications to all users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->postJson('/api/notifications/broadcast-all', [
            'message' => 'teste',
        ])
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

test('admin can broadcast notifications to all users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(2)->create();

    $this->actingAs($admin)
        ->postJson('/api/notifications/broadcast-all', [
            'message' => 'Aviso global de teste',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('stats.success_count', 3);
});
