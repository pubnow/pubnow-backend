<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuthTest extends TestCase
{
    public function test_can_register_new_user()
    {
        $user = factory(User::class)->make();

        $response = $this->json('POST', '/api/auth/register', [
            'user' => [
                'email' => $user->email,
                'name' => $user->name,
                'username' => $user->username,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'email' => $user->email,
            'name' => $user->name,
            'username' => $user->username,
        ]);
    }

    public function test_can_login()
    {
        $user = factory(User::class)->create();

        $response = $this->json('POST', '/api/auth/login', [
            'user' => [
                'username' => $user->username,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'email' => $user->email,
            'name' => $user->name,
            'username' => $user->username,
        ]);
    }

    public function test_can_get_user_profile()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/api/auth/me');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
        ]);
    }

    public function test_cant_get_user_profile_if_user_not_login()
    {
        $response = $this->json('GET', '/api/auth/me');
        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
    }
}
