<?php

namespace Tests\Feature\Api;

use App\Models\InviteRequest;
use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InviteRequestTest extends TestCase
{
    protected $admin;
    protected $user;
    protected $organization;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::where(['username' => 'admin'])->first();
        $this->user = factory(User::class)->create();
        $this->organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);
    }

    // --- Get list
    // Test can get list invite request, admin
    public function test_can_get_list_invite_request() {
        $users = factory(User::class, 5)->create();

        $users->each(function ($user) {
            InviteRequest::create([
                'user_id' => $user->id,
                'organization_id' => $this->organization->id,
                'status' => 'pending'
            ]);
        });

        $response = $this->actingAs($this->admin)->json('GET', 'api/invite-requests');

        $response->assertStatus(200);

        $response->assertJsonCount(count($users), 'data');
    }

    // Test get list invite request, user
    public function test_cannot_get_list_invite_request_user_logged_in() {
        $users = factory(User::class, 5)->create();

        $users->each(function ($user) {
            InviteRequest::create([
                'user_id' => $user->id,
                'organization_id' => $this->organization->id,
                'status' => 'pending'
            ]);
        });

        $response = $this->actingAs($this->user)->json('GET', 'api/invite-requests');

        $response->assertStatus(403);
    }

    // Test can get list invite request, not logged in
    public function test_cannot_get_list_invite_request_if_not_logged_in() {
        $users = factory(User::class, 5)->create();

        $users->each(function ($user) {
            InviteRequest::create([
                'user_id' => $user->id,
                'organization_id' => $this->organization->id,
                'status' => 'pending'
            ]);
        });

        $response = $this->json('GET', 'api/invite-requests');

        $response->assertStatus(401);
    }

    // --- Create
    // Test create invite request, organization owner
    public function test_can_create_invite_request() {
        $user = factory(User::class)->create();

        $response = $this->actingAs($this->user)->json('POST', 'api/invite-requests', [
            'user_id' => $user->id,
            'organization_id' => $this->organization->id
        ]);

        $response->assertStatus(201);

        $response->assertJson([
            'data' => [
                'status' => 'pending',
                'organization' => [
                    'id' => $this->organization->id,
                ],
                'user' => [
                    'id' => $user->id
                ],
            ]
        ]);
    }

    // Test create invite request, organization owner, invite request exists, denied
    public function test_can_create_invite_request_if_invite_request_exists_but_denied() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'denied'
        ]);

        $response = $this->actingAs($this->user)->json('POST', 'api/invite-requests', [
            'user_id' => $user->id,
            'organization_id' => $this->organization->id
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'status' => 'pending',
                'organization' => [
                    'id' => $this->organization->id,
                ],
                'user' => [
                    'id' => $user->id
                ],
            ]
        ]);
    }

    // Test create invite request, organization owner, invite request exists, pending
    public function test_cannot_create_invite_request_if_invite_request_exists_and_not_denied() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)->json('POST', 'api/invite-requests', [
            'user_id' => $user->id,
            'organization_id' => $this->organization->id
        ]);

        $response->assertStatus(422);
    }
    // Test create invite request, organization owner, organization not active
    public function test_can_create_invite_request_organization_not_active() {
        $user = factory(User::class)->create();
        $this->organization->update([
            'active' => false
        ]);

        $response = $this->actingAs($this->user)->json('POST', 'api/invite-requests', [
            'user_id' => $user->id,
            'organization_id' => $this->organization->id
        ]);

        $response->assertStatus(422);
    }

    // Test create invite request, not logged in
    public function test_cannot_create_invite_request_if_not_logged_in() {
        $user = factory(User::class)->create();

        $response = $this->json('POST', 'api/invite-requests', [
            'user_id' => $user->id,
            'organization_id' => $this->organization->id
        ]);

        $response->assertStatus(401);
    }

    // Test create invite request, not organization owner
    public function test_cannot_create_invite_request_if_not_organization_owner() {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('POST', 'api/invite-requests', [
            'user_id' => $user->id,
            'organization_id' => $this->organization->id
        ]);

        $response->assertStatus(422);
    }

    // Test create invite request, organization owner, user_id not exists
    public function test_cannot_create_invite_request_if_user_id_not_exists() {
        $user = factory(User::class)->create();
        $id = $user->id;
        $user->delete();

        $response = $this->actingAs($user)->json('POST', 'api/invite-requests', [
            'user_id' => $id,
            'organization_id' => $this->organization->id
        ]);

        $response->assertStatus(422);
    }

    // Test create invite request, logged in, organization_id not exists
    public function test_cannot_create_invite_request_if_organization_id_not_exists() {
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id
        ]);
        $id = $organization->id;
        $organization->delete();

        $response = $this->actingAs($this->user)->json('POST', 'api/invite-requests', [
            'user_id' => $this->user->id,
            'organization_id' => $id
        ]);

        $response->assertStatus(422);
    }

    // --- Update
    // Test update invite request, user is invited
    public function test_can_update_invite_request() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)->json('PUT', 'api/invite-requests/'.$inviteRequest->id, [
            'status' => 'accepted',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'status' => 'accepted',
                'organization' => [
                    'id' => $this->organization->id,
                ],
                'user' => [
                    'id' => $user->id
                ],
            ]
        ]);
    }

    // Test update invite request, not logged in
    public function test_cannot_update_invite_request_if_not_logged_in() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);

        $response = $this->json('PUT', 'api/invite-requests/'.$inviteRequest->id, [
            'status' => 'accepted',
        ]);

        $response->assertStatus(401);
    }

    // Test update invite request, not organization owner
    public function test_cannot_update_invite_request_if_not_invited_user() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)->json('PUT', 'api/invite-requests/'.$inviteRequest->id, [
            'status' => 'accepted',
        ]);

        $response->assertStatus(403);
    }

    // Test update invite request, invited user, status is not accepted or denied
    public function test_cannot_create_invite_request_if_status_is_wrong() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)->json('PUT', 'api/invite-requests/'.$inviteRequest->id, [
            'status' => 'abc',
        ]);

        $response->assertStatus(422);
    }

    // Test update invite request, logged in, invite request not exists
    public function test_cannot_create_invite_request_if_invite_request_not_exists() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);
        $id = $inviteRequest->id;
        $inviteRequest->delete();

        $response = $this->actingAs($user)->json('PUT', 'api/invite-requests/'.$id, [
            'status' => 'accepted',
        ]);

        $response->assertStatus(404);
    }

    // Test update invite request, logged in, invite request is replied
    public function test_cannot_create_invite_request_if_invite_request_is_replied() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'accepted'
        ]);

        $response = $this->actingAs($user)->json('PUT', 'api/invite-requests/'.$inviteRequest->id, [
            'status' => 'accepted',
        ]);

        $response->assertStatus(422);
    }

    // --- Delete
    // Test delete invite request, user is organization owner
    public function test_can_delete_invite_request_if_organization_owner() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)->json('DELETE', 'api/invite-requests/'.$inviteRequest->id);

        $response->assertStatus(204);
    }

    // Test delete invite request, not logged in
    public function test_cannot_delete_invite_request_if_not_logged_in() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);

        $response = $this->json('DELETE', 'api/invite-requests/'.$inviteRequest->id);

        $response->assertStatus(401);
    }

    // Test delete invite request, user is not organization owner
    public function test_cannot_delete_invite_request_if_not_organization_owner() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($user)->json('DELETE', 'api/invite-requests/'.$inviteRequest->id);

        $response->assertStatus(403);
    }

    // Test delete invite request, user is organization owner
    public function test_cannot_delete_invite_request_if_not_exists() {
        $user = factory(User::class)->create();
        $inviteRequest = InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'status' => 'pending'
        ]);
        $id = $inviteRequest->id;
        $inviteRequest->delete();

        $response = $this->actingAs($this->user)->json('DELETE', 'api/invite-requests/'.$id);

        $response->assertStatus(404);
    }


}
