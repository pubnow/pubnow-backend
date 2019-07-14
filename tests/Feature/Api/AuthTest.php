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

    // Dang ki user moi, truyen thieu 1 trong cac truong username | name | password | email
    public function test_cannot_register_new_user_if_missing_field_email() {
        $user = factory(User::class)->make();

        $response = $this->json('POST', '/api/auth/register', [
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(422);
    }

    // Dang ki user moi, truyen du cac truong, user name da ton tai
    public function test_cannot_register_new_user_if_username_exists() {
        $user = factory(User::class)->create();
        $newUser = factory(User::class)->make();

        $response = $this->json('POST', '/api/auth/register', [
            'user' => [
                'email' => $newUser->email,
                'name' => $newUser->name,
                'username' => $user->username,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(422);
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

    // Dang nhap sai mat khau hoac username
    public function test_cannot_login_if_wrong_password()
    {
        $user = factory(User::class)->create();

        $response = $this->json('POST', '/api/auth/login', [
            'user' => [
                'username' => $user->username,
                'password' => '123',
            ],
        ]);

        $response->assertStatus(500);
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
