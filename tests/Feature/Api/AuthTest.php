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

    // ---
    // Update user
    // Test user can update his/she profile
    public function test_user_can_update_own_profile() {
        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($user)->json('PUT', '/api/auth/update/'.$user->username, [
            'user' => [
                'name' => $updateUser->name,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'email' => $user->email,
            'name' => $updateUser->name,
            'username' => $user->username,
        ]);
    }

    // Test update user, dang nhap bang admin
    public function test_update_user_profile_if_logged_in_as_admin() {
        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/auth/update/'.$user->username, [
            'user' => [
                'name' => $updateUser->name,
                'password' => 'password'
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'email' => $user->email,
            'name' => $updateUser->name,
            'username' => $user->username,
        ]);
    }

    // Test update user, dang nhap bang admin
    public function test_cannot_update_other_user_profile() {
        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($user)->json('PUT', '/api/auth/update/'.$this->user->username, [
            'user' => [
                'name' => $updateUser->name,
                'password' => 'password'
            ],
        ]);

        $response->assertStatus(403);
    }

    // Test cannot update user if update email or username
    public function test_cannot_update_user_profile_if_logged_in_as_admin_but_update_email() {

        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/auth/update/'.$user->username, [
            'user' => [
                'name' => $updateUser->name,
                'email' => $updateUser->email,
                'password' => 'password'
            ],
        ]);

        $response->assertStatus(403);
    }

    // Test update user if not logged in
    public function test_cannot_update_user_profile_if_not_logged_in() {
        $updateUser = factory(User::class)->make();

        $response = $this->json('PUT', '/api/auth/update/'.$this->user->username, [
            'user' => [
                'name' => $updateUser->name,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(401);
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
