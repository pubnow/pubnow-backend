<?php

namespace Tests\Feature\Api;

use App\Models\InviteRequest;
use App\Models\Organization;
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

    // --- Delete
    // Test delete user, dang nhap bang admin
    public function test_can_delete_user_profile_if_logged_in_as_admin() {
        $user = factory(User::class)->create();

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/users/'.$user->username);

        $response->assertStatus(204);
    }

    // Test delete user, dang nhap bang owner
    public function test_can_delete_user_profile_if_logged_in_as_owner() {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('DELETE', '/api/users/'.$user->username);

        $response->assertStatus(204);
    }

    // Test delete user, chua dang nhap
    public function test_cannot_delete_user_profile_if_not_logged_in() {
        $user = factory(User::class)->create();

        $response = $this->json('DELETE', '/api/users/'.$user->username);

        $response->assertStatus(401);
    }

    // Test delete user, dang nhap bang admin, khong ton tai
    public function test_admin_cannot_delete_user_profile_if_not_exist() {
        $user = factory(User::class)->make();

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/users/'.$user->username);

        $response->assertStatus(404);
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

    // --- Get joined organizations
    // Test get joined organizations, logged in
    public function test_can_get_list_joined_organizations() {
        $organizations = factory(Organization::class, 5)->create([
            'owner' => $this->user->id
        ]);

        $organizations->each(function ($organization) {
            InviteRequest::create([
                'user_id' => $this->user->id,
                'organization_id' => $organization->id,
                'status' => 'accepted'
            ]);
        });

        $response = $this->actingAs($this->user)->json('GET', 'api/users/organizations');

        $response->assertStatus(200);

        $response->assertJsonCount(count($organizations), 'data');
    }

    // Test get joined organizations, not logged in
    public function test_cannot_get_list_joined_organizations_if_not_logged_in()
    {
        $organizations = factory(Organization::class, 5)->create([
            'owner' => $this->user->id
        ]);

        $organizations->each(function ($organization) {
            InviteRequest::create([
                'user_id' => $this->user->id,
                'organization_id' => $organization->id,
                'status' => 'accepted'
            ]);
        });

        $response = $this->json('GET', 'api/users/organizations');
        $response->assertStatus(401);
    }

    // --- Follow
    // Test follow user, logged in, user exists
    public function test_user_can_follow_an_exists_user() {
        $user = factory(User::class)->create();

        $response = $this->actingAs($this->user)->json('POST', 'api/users/'.$user->username.'/follow');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => $this->user->name,
            'username' => $this->user->username,
            'email' => $this->user->email
        ]);

        $response->assertJsonCount(1, 'data.followingUsers');

        $response->assertJsonStructure([
            'data' => [
                'id', 'username', 'name', 'email', 'isAdmin', 'bio', 'avatar', 'role', 'followingUsers'
            ]
        ]);
    }

    // Test follow user, not logged in, user exists
    public function test_guest_cannot_follow_an_exists_user() {
        $user = factory(User::class)->create();

        $response = $this->json('POST', 'api/users/'.$user->username.'/follow');

        $response->assertStatus(401);
    }

    // Test follow user, logged in, not user exists
    public function test_user_cannot_follow_a_not_exists_user() {
        $user = factory(User::class)->make();

        $response = $this->actingAs($this->user)->json('POST', 'api/users/'.$user->username.'/follow');

        $response->assertStatus(404);
    }

    // Test follow user, logged in, user exists, followed
    public function test_user_can_follow_an_followed_user() {
        $user = factory(User::class)->create();
        $this->user->followingUsers()->attach($user);

        $response = $this->actingAs($this->user)->json('POST', 'api/users/'.$user->username.'/follow');

        $response->assertStatus(422);
    }

    // --- Unfollow
    // Test unfollow user, logged in, user exists, followed
    public function test_user_can_unfollow_a_followed_user() {
        $user = factory(User::class)->create();
        $this->user->followingUsers()->attach($user);

        $response = $this->actingAs($this->user)->json('DELETE', 'api/users/'.$user->username.'/follow');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => $this->user->name,
            'username' => $this->user->username,
            'email' => $this->user->email
        ]);

        $response->assertJsonCount(0, 'data.followingUsers');

        $response->assertJsonStructure([
            'data' => [
                'id', 'username', 'name', 'email', 'isAdmin', 'bio', 'avatar', 'role', 'followingUsers'
            ]
        ]);
    }

    // Test unfollow user, not logged in, user exists
    public function test_guest_cannot_unfollow_an_user() {
        $user = factory(User::class)->create();
        $this->user->followingUsers()->attach($user);

        $response = $this->json('DELETE', 'api/users/'.$user->username.'/follow');


        $response->assertStatus(401);
    }

    // --- Get invite requests
    // Test can get list invite requests, logged in
    public function test_can_get_list_invite_requests() {
        $organizations = factory(Organization::class, 5)->create([
            'owner' => $this->user->id
        ]);

        $organizations->each(function ($organization) {
            InviteRequest::create([
                'user_id' => $this->user->id,
                'organization_id' => $organization->id,
                'status' => 'pending'
            ]);
        });

        $response = $this->actingAs($this->user)->json('GET', 'api/users/invite-requests');

        $response->assertStatus(200);

        $response->assertJsonCount(count($organizations), 'data');
    }

    // Test can get list invite requests, not logged in
    public function test_cannot_get_list_invite_requests_if_not_logged_in() {
        $organizations = factory(Organization::class, 5)->create([
            'owner' => $this->user->id
        ]);

        $organizations->each(function ($organization) {
            InviteRequest::create([
                'user_id' => $this->user->id,
                'organization_id' => $organization->id,
                'status' => 'pending'
            ]);
        });

        $response = $this->json('GET', 'api/users/invite-requests');

        $response->assertStatus(401);
    }

    // Test unfollow user, logged in, not user exists
    public function test_user_cannot_unfollow_a_not_exists_user() {
        $user = factory(User::class)->make();

        $response = $this->actingAs($this->user)->json('DELETE', 'api/users/'.$user->username.'/follow');

        $response->assertStatus(404);
    }

    // Test unfollow user, logged in, user exists, followed
    public function test_user_can_unfollow_a_not_followed_user() {
        $user = factory(User::class)->create();

        $response = $this->actingAs($this->user)->json('DELETE', 'api/users/'.$user->username.'/follow');

        $response->assertStatus(422);
    }

    // --- Following Users
    // Test get list followers
    public function test_can_get_list_following_users() {
        $users = factory(User::class, 5)->create();

        $users->each(function ($following) {
            $this->user->followingUsers()->attach($following);
        });

        $response = $this->json('GET', 'api/users/'.$this->user->username.'/following-users');

        $response->assertJsonCount(count($users), 'data');

        $users->each(function ($user) use ($response) {
            $response->assertJsonFragment([
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email
            ]);
        });
    }

    // --- Followers
    // Test get list followers
    public function test_can_get_list_followers() {
        $users = factory(User::class, 5)->create();

        $users->each(function ($follower) {
            $this->user->followers()->attach($follower);
        });

        $response = $this->json('GET', 'api/users/'.$this->user->username.'/followers');

        $response->assertJsonCount(count($users), 'data');

        $users->each(function ($user) use ($response) {
            $response->assertJsonFragment([
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email
            ]);
        });
    }

}
