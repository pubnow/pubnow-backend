<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Faker\Factory;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use WithFaker;
    protected $admin;
    protected $user;
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::where(['username' => 'admin'])->first();
        $this->user = factory(User::class)->create();
        $this->faker = new Factory();
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

        $avatar = UploadedFile::fake()->create('tuan_avatar.png');

        $response = $this->actingAs($user)->json('PUT', '/api/users/'.$user->username, [
            'name' => $updateUser->name,
            'avatar' => $avatar,
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
            'name' => $updateUser->name,
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'email' => $user->email,
            'name' => $updateUser->name,
            'username' => $user->username,
        ]);
    }

    // Test update user, dang nhap bang user khac khong phai admin
    public function test_cannot_update_other_user_profile() {
        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($user)->json('PUT', '/api/users/'.$this->user->username, [
            'name' => $updateUser->name,
            'password' => 'password'
        ]);

        $response->assertStatus(403);
    }

    // Test cannot update user if update email or username
    public function test_cannot_update_user_profile_if_logged_in_as_admin_but_update_email() {

        $user = factory(User::class)->create();
        $updateUser = factory(User::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/users/'.$user->username, [
            'name' => $updateUser->name,
            'email' => $updateUser->email,
            'password' => 'password'
        ]);

        $response->assertStatus(403);
    }

    // Test update user if not logged in
    public function test_cannot_update_user_profile_if_not_logged_in() {
        $updateUser = factory(User::class)->make();

        $response = $this->json('PUT', '/api/users/'.$this->user->username, [
            'name' => $updateUser->name,
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    // --- Change password
    // Test user can update own password
    public function test_can_update_own_password_if_logged_in() {
        $this->user->update([
            'password' => '111111'
        ]);

        $response = $this->actingAs($this->user)->json('PUT', '/api/users/change-password', [
            'old_password' => '111111',
            'new_password' => '123456'
        ]);

        $response->assertStatus(200);
    }

    // Test user cannot update password if not logged in
    public function test_cannot_update_password_if_not_logged_in() {
        $this->user->update([
            'password' => '111111'
        ]);

        $response = $this->json('PUT', '/api/users/change-password', [
            'old_password' => '111111',
            'new_password' => '123456'
        ]);

        $response->assertStatus(401);
    }

    // Test user cannot update password if wrong password
    public function test_cannot_update_password_if_logged_in_but_wrong_password() {
        $this->user->update([
            'password' => '111111'
        ]);

        $response = $this->actingAs($this->user)->json('PUT', '/api/users/change-password', [
            'old_password' => '222222',
            'new_password' => '123456'
        ]);

        $response->assertStatus(422);
    }

    // Test user cannot update password if new password length < 6
    public function test_cannot_update_password_if_logged_in_but_new_password_too_short() {
        $this->user->update([
            'password' => '111111'
        ]);

        $response = $this->actingAs($this->user)->json('PUT', '/api/users/change-password', [
            'old_password' => '222222',
            'new_password' => '123'
        ]);

        $response->assertStatus(422);
    }

}
