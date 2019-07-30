<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationTest extends TestCase
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
    // --- Get all
    // Test can get all Organizations -> ok
    public function test_can_get_list_organizations() {
        $organizations = factory(Organization::class, 5)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/organizations');

        $response->assertStatus(200);

        $response->assertJsonCount(count($organizations), 'data');

        $organizations->each(function ($organization) use ($response) {
            $response->assertJsonFragment([
                'name' => $organization->name,
                'description' => $organization->description,
                'email' => $organization->email,
            ]);
        });
    }

    // --- Create
    // Test can create a organization -> 201
    public function test_can_create_an_organization_if_logged_in() {
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations', [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => $organization->name,
            'description' => $organization->description,
            'email' => $organization->email,
        ]);
    }

    // Test create organization, not logged in -> 401
    public function test_cannot_create_an_organization_if_not_logged_in() {
        $organization = factory(Organization::class)->make();

        $response = $this->json('POST', '/api/organizations', [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(401);
    }

    // Test create organization, logged in, missing name field -> 401
    public function test_cannot_create_an_organization_if_logged_in_but_missing_name() {
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations', [
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // Test create a organization, logged in, dupplicate name -> 422
    public function test_cannot_create_an_organization_if_logged_in_but_dupplicate_name() {
        $exists = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations', [
            'name' => $exists->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // Test create a organization, logged in, email is not in format -> 201
    public function test_cannot_create_an_organization_if_logged_in_but_email_not_right_format() {
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations', [
            'name' => $organization->name,
            'email' => 'abc',
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // --- Update
    // Test update a organization, logged in, owner -> 201
    public function test_can_update_an_organization_if_logged_in() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$created->id, [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $organization->name,
            'description' => $organization->description,
            'email' => $organization->email,
        ]);
    }

    // Test update organization, not logged in -> 401
    public function test_cannot_update_an_organization_if_not_logged_in() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->json('PUT', '/api/organizations/'.$created->id, [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(401);
    }


    // Test update organization, logged in, not owner -> 401
    public function test_cannot_update_an_organization_if_not_owner() {
        $user = factory(User::class)->create();
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($user)->json('PUT', '/api/organizations/'.$created->id, [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(403);
    }

    // Test update organization, logged in, organization not exists -> 404
    public function test_cannot_update_an_organization_if_logged_in_but_organization_not_exists() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $id = $created->id;
        $created->delete();
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$id, [
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(404);
    }

    // Test update a organization, logged in, dupplicate name -> 422
    public function test_cannot_update_an_organization_if_logged_in_but_dupplicate_name() {
        $createds = factory(Organization::class, 2)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$createds[0]->id, [
            'name' => $createds[1]->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // Test update a organization, logged in, email is not in format -> 201
    public function test_cannot_update_an_organization_if_logged_in_but_email_not_right_format() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$created->id, [
            'name' => $organization->name,
            'email' => 'abc',
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // --- Delete
    // Test delete organization, logged in, owner
    public function test_can_delete_organization_if_logged_in_and_admin() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/organizations/'.$created->id);

        $response->assertStatus(204);
    }

    // Test delete organization, logged in, owner
    public function test_can_delete_organization_if_logged_in_and_owner() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->json('DELETE', '/api/organizations/'.$created->id);

        $response->assertStatus(204);
    }


    // Test delete organization, logged in, owner
    public function test_cannot_delete_organization_if_not_logged_in() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->json('DELETE', '/api/organizations/'.$created->id);

        $response->assertStatus(401);
    }


    // Test delete organization, logged in, owner
    public function test_cannot_delete_organization_if_logged_in_but_not_owner() {
        $user = factory(User::class)->create();
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->actingAs($user)->json('DELETE', '/api/organizations/'.$created->id);

        $response->assertStatus(403);
    }

    // Active
    // Test active organization, admin -> ok
    public function test_can_active_organization_if_logged_in_as_admin() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->json('POST', '/api/organizations/'.$created->id.'/active');

        $response->assertStatus(200);
    }

    // Test active organization, user -> 403
    public function test_cannot_active_organization_if_logged_in_as_user() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations/'.$created->id.'/active');

        $response->assertStatus(403);
    }

    // Test active organization, not logged in -> 401
    public function test_cannot_active_organization_if_not_logged() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->json('POST', '/api/organizations/'.$created->id.'/active');

        $response->assertStatus(401);
    }


}
