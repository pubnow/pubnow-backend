<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuthTest extends TestCase
{
    protected $admin;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::where(['username' => 'admin'])->first();
        $this->user = factory(User::class)->create();
    }
    // Dang ki
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
//        $user = factory(User::class)->create();
        $newUser = factory(User::class)->make();

        $response = $this->json('POST', '/api/auth/register', [
            'user' => [
                'email' => $newUser->email,
                'name' => $newUser->name,
                'username' => $this->user->username,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(422);
    }

    // ----
    // Dang nhap
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

    // ---
    // Update user
    // Test user can update his/she profile
    public function test_user_can_update_own_profile() {
        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($user)->json('POST', '/api/auth/update', [
            'user' => [
                'email' => $updateUser->email,
                'name' => $updateUser->name,
                'username' => $updateUser->username,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'email' => $updateUser->email,
            'name' => $updateUser->name,
            'username' => $updateUser->username,
        ]);
    }

    // Test update user if not logged in
    public function test_cannot_update_user_profile_if_not_logged_in() {
        $updateUser = factory(User::class)->make();

        $response = $this->json('POST', '/api/auth/update', [
            'user' => [
                'email' => $updateUser->email,
                'name' => $updateUser->name,
                'username' => $updateUser->username,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(401);
    }

    // Test update user, da dang nhap, nhung email bi trung
    public function test_cannot_update_user_profile_if_logged_in_but_email_exists() {
        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/auth/update', [
            'user' => [
                'email' => $user->email,
                'name' => $updateUser->name,
                'username' => $updateUser->username,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(422);
    }

    // Test update user, dang nhap bang admin
    public function test_update_user_profile_if_logged_in_as_admin() {
        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($this->admin)->json('POST', '/api/auth/update/'.$user->username, [
            'user' => [
                'email' => $updateUser->email,
                'name' => $updateUser->name,
                'username' => $updateUser->username,
                'password' => 'password'
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'email' => $updateUser->email,
            'name' => $updateUser->name,
            'username' => $updateUser->username,
        ]);
    }


    // ---
    // Get user profile
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
