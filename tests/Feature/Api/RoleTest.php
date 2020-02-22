<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    protected $admin;
    protected $user;
    protected $role;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::where(['username' => 'admin'])->first();
        $this->user = factory(User::class)->create();
        $this->role = factory(Role::class)->create();
    }

    //--- Get list
    // Test can get list of role - admin login
    public function test_admin_can_get_list_of_roles() {
        $roles = Role::all();
        $response = $this->actingAs($this->admin)->json('GET', '/api/roles');

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');

        $roles->each(function ($role) use ($response) {
            $response->assertJsonFragment([
                'name' => $role->name,
                'description' => $role->description,
            ]);
        });
    }

    // Test cannot get list of role - user login
    public function test_member_cannot_get_list_of_roles() {
        $response = $this->actingAs($this->user)->json('GET', '/api/roles');

        $response->assertStatus(403);
    }

    // Test cannot get list of role - not login
    public function test_guest_cannot_get_list_of_roles() {
        $response = $this->json('GET', '/api/roles');

        $response->assertStatus(401);
    }

    //--- Create
    // Test can create new role - admin login
    public function test_admin_can_create_new_role() {
        $reponse = $this->actingAs($this->admin)->json('POST', '/api/roles', [
            'name' => 'sample',
            'description' => 'sample'
        ]);

        $reponse->assertStatus(201);
        $reponse->assertJsonFragment([
            'name' => 'sample',
            'description' => 'sample'
        ]);
    }

    // Test cannot create new role - user login
    public function test_member_cannot_create_new_role() {
        $response = $this->actingAs($this->user)->json('POST', '/api/roles', [
            'name' => 'sample',
            'description' => 'sample'
        ]);

        $response->assertStatus(403);
    }

    // Test cannot create new role - not login
    public function test_guest_cannot_cannot_create_new_role() {
        $response = $this->json('POST', '/api/roles', [
            'name' => 'sample',
            'description' => 'sample'
        ]);

        $response->assertStatus(401);
    }

    // Test cannot create list - missing name
    public function test_admin_cannot_create_new_role_if_missing_name() {
        $reponse = $this->actingAs($this->admin)->json('POST', '/api/roles', [
            'description' => 'sample'
        ]);

        $reponse->assertStatus(422);
    }

    // Test cannot create role - dupplicate name
    public function test_admin_cannot_create_new_role_if_dupplicate_name() {
        $reponse = $this->actingAs($this->admin)->json('POST', '/api/roles', [
            'name' => 'admin',
            'description' => 'sample'
        ]);

        $reponse->assertStatus(422);
    }

    //--- Update
    // Test can update role - admin login
    public function test_admin_can_update_role() {
        $reponse = $this->actingAs($this->admin)->json('PUT', '/api/roles/'.$this->role->id, [
            'name' => 'sample',
            'description' => 'sample'
        ]);

        $reponse->assertStatus(200);
        $reponse->assertJsonFragment([
            'name' => 'sample',
            'description' => 'sample'
        ]);
    }

    // Test cannot update role - user login
    public function test_member_cannot_update_role() {
        $response = $this->actingAs($this->user)->json('PUT', '/api/roles/'.$this->role->id, [
            'name' => 'sample',
            'description' => 'sample'
        ]);

        $response->assertStatus(403);
    }

    // Test cannot update role - not login
    public function test_guest_cannot_cannot_update_role() {
        $response = $this->json('PUT', '/api/roles/'.$this->role->id, [
            'name' => 'sample',
            'description' => 'sample'
        ]);

        $response->assertStatus(401);
    }

    // Test cannot update role - dupplicate name
    public function test_admin_cannot_update_role_if_dupplicate_name() {
        $reponse = $this->actingAs($this->admin)->json('PUT', '/api/roles/'.$this->role->id, [
            'name' => 'admin',
            'description' => 'sample'
        ]);

        $reponse->assertStatus(422);
    }

    // Test cannot update role - not exist
    public function test_admin_cannot_update_role_if_not_exist() {
        $role = factory(Role::class)->create();
        $id = $role->id;
        $role->delete();
        $response = $this->actingAs($this->admin)->json('PUT', '/api/roles/'.$id, [
            'name' => 'admin',
            'description' => 'sample'
        ]);

        $response->assertStatus(404);
    }

    // Test can update role - admin login, role admin
    public function test_admin_cannot_update_role_admin() {
        $role_admin = Role::where(['name' => 'admin'])->first();

        $reponse = $this->actingAs($this->admin)->json('PUT', '/api/roles/'.$role_admin->id, [
            'name' => 'sample',
            'description' => 'sample'
        ]);

        $reponse->assertStatus(422);
    }

    // Test can update role - admin login, role member
    public function test_admin_cannot_update_role_member() {
        $role_admin = Role::where(['name' => 'member'])->first();

        $reponse = $this->actingAs($this->admin)->json('PUT', '/api/roles/'.$role_admin->id, [
            'name' => 'sample',
            'description' => 'sample'
        ]);

        $reponse->assertStatus(422);
    }

    //--- Delete
    // Test can delete role - admin login
    public function test_admin_can_delete_role() {
        $reponse = $this->actingAs($this->admin)->json('DELETE', '/api/roles/'.$this->role->id);

        $reponse->assertStatus(204);
    }

    // Test cannot delete role - user login
    public function test_member_cannot_delete_role() {
        $response = $this->actingAs($this->user)->json('DELETE', '/api/roles/'.$this->role->id);

        $response->assertStatus(403);
    }

    // Test cannot delete role - not login
    public function test_guest_cannot_cannot_delete_role() {
        $response = $this->json('DELETE', '/api/roles/'.$this->role->id);

        $response->assertStatus(401);
    }

    // Test cannot delete role - not exist
    public function test_admin_cannot_delete_role_if_not_exist() {
        $role = factory(Role::class)->create();
        $id = $role->id;
        $role->delete();
        $response = $this->actingAs($this->admin)->json('DELETE', '/api/roles/'.$id);

        $response->assertStatus(404);
    }

}
