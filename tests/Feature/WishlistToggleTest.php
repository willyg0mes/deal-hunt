<?php

use App\Models\Game;
use App\Models\User;

test('verified user can add and remove game from wishlist', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'is_active' => true,
        'deal_ends_at' => now()->addDay(),
    ]);

    $this->actingAs($user)
        ->post(route('wishlist.toggle', $game), [], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('isInWishlist', true);

    expect($user->fresh()->wishlist()->where('game_id', $game->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->post(route('wishlist.toggle', $game), [], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('isInWishlist', false);

    expect($user->fresh()->wishlist()->where('game_id', $game->id)->exists())->toBeFalse();
});

test('guest cannot toggle wishlist', function () {
    $game = Game::factory()->create();

    $this->post(route('wishlist.toggle', $game))
        ->assertRedirect(route('login'));
});
