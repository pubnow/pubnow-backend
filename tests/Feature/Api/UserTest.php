<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
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
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    // Get list of user
    public function test_can_get_list_users()
    {
        $users = factory(User::class, 5)->create();

        $response = $this->json('GET', '/api/users');

        $response->assertStatus(200);

        $response->assertJsonCount(count($users) + 2, 'data');

        $users->each(function ($user) use ($response) {
            $response->assertJsonFragment([
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email
            ]);
        });
    }

    // Get user profile
    public function test_can_get_a_user()
    {
        $user = factory(User::class)->create();

        $response = $this->json('GET', '/api/users/'.$user->username);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email
        ]);
    }
    // ---
    // Update user
    // Test user can update his/she profile
    public function test_user_can_update_own_profile() {
        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($user)->json('PUT', '/api/users/'.$user->username, [
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

        $response = $this->actingAs($this->admin)->json('PUT', '/api/users/'.$user->username, [
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

        $response = $this->actingAs($user)->json('PUT', '/api/users/'.$this->user->username, [
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

        $response = $this->actingAs($this->admin)->json('PUT', '/api/users/'.$user->username, [
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

        $response = $this->json('PUT', '/api/users/'.$this->user->username, [
            'user' => [
                'name' => $updateUser->name,
                'password' => 'password',
            ],
        ]);

        $response->assertStatus(401);
    }

}
