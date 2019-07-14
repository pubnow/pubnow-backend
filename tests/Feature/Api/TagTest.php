<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tag;

class TagTest extends TestCase
{
    protected $admin;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::where(['username' => 'admin'])->first();
        $this->member = factory(User::class)->create();
    }
    // Get danh sach tag
    public function test_can_get_list_tags()
    {
        $tags = factory(Tag::class, 5)->create();

        $response = $this->json('GET', '/api/tags');

        $response->assertStatus(200);

        $response->assertJsonCount(count($tags), 'data');

        $tags->each(function ($tag) use ($response) {
            $response->assertJsonFragment([
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'image' => $tag->image,
            ]);
        });
    }
    // ------
    // TODO: Tao tag, neu da login, -> ok
    public function test_can_create_tag_if_logged_in()
    {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->member)->json('POST', '/api/tags', [
            'tag' => [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'image' => $tag->image,
            ]
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'name' => $tag->name,
            'slug' => $tag->slug,
            'description' => $tag->description,
            'image' => $tag->image,
        ]);
    }
    // TODO: Tag tag, chua login -> 403
    public function test_cannot_create_tag_if_not_logged_in() {
        $tag = factory(Tag::class)->make();

        $response = $this->json('POST', '/api/tags', [
            'tag' => [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'image' => $tag->image,
            ]
        ]);

        $response->assertStatus(401);
    }
    // TODO: Tao tag, da login, nhung truyen thieu data required (name || slug) => 422
    public function test_cannot_create_tag_if_logged_in_but_missing_name() {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->member)->json('POST', '/api/tags', [
            'tag' => [
                'slug' => $tag->slug,
                'description' => $tag->description,
                'image' => $tag->image,
            ]
        ]);

        $response->assertStatus(422);
    }
    // ----
    // TODO: Xem 1 tag, ton tai -> ok
    public function test_can_get_an_exists_tag() {
        $tag = factory(Tag::class)->create();

        $response = $this->json('GET', '/api/tags/'.$tag->slug);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $tag->name,
            'slug' => $tag->slug,
            'description' => $tag->description,
            'image' => $tag->image,
        ]);
    }
    // TODO: Xem 1 tag, khong ton tai -> 404 not found
    public function test_cannot_get_a_not_exists_tag() {
        $tag = factory(Tag::class)->make();

        $response = $this->json('GET', '/api/tags/'.$tag->slug);

        $response->assertStatus(404);
    }
    // ----
    // TODO: Sua 1 tag, ton tai + user la admin -> ok
    public function test_can_update_a_exists_tag_with_admin_logged_in() {
        $tag = factory(Tag::class)->create();
        $updateTag = factory(Tag::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/tags/'.$tag->slug, [
            'tag' => [
                'name' => $updateTag->name,
                'slug' => $updateTag->slug,
                'description' => $updateTag->description,
                'image' => $updateTag->image,
            ]
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $tag->name,
            'slug' => $tag->slug,
            'description' => $tag->description,
            'image' => $tag->image,
        ]);
    }
    // TODO: Sua 1 tag, ton tai + user k phai admin -> 403
    public function test_cannot_update_a_exists_tag_with_member_logged_in() {
        $tag = factory(Tag::class)->create();
        $updateTag = factory(Tag::class)->make();

        $response = $this->actingAs($this->member)->json('PUT', '/api/tags/'.$tag->slug, [
            'tag' => [
                'name' => $updateTag->name,
                'slug' => $updateTag->slug,
                'description' => $updateTag->description,
                'image' => $updateTag->image,
            ]
        ]);

        $response->assertStatus(403);
    }
    // TODO: Sua 1 tag, ton tai + user la admin, nhung data sai (name || slug bi trung) -> 422
    public function test_cannot_update_a_exists_tag_with_admin_logged_in_but_dupplicate_name() {
        $tags = factory(Tag::class, 2)->create();
        $updateTag = factory(Tag::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/tags/'.$tags[0]->slug, [
            'tag' => [
                'name' => $tags[1]->name,
                'slug' => $updateTag->slug,
                'description' => $updateTag->description,
                'image' => $updateTag->image,
            ]
        ]);

        $response->assertStatus(422);
    }
    // TODO: Sua 1 tag, khong ton tai -> 404 not found
    public function test_cannot_update_a_not_exists_tag() {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/tags/'.$tag->slug, [
            'tag' => [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'image' => $tag->image,
            ]
        ]);

        $response->assertStatus(404);
    }
    // ----
    // TODO: Xoa 1 tag, ton tai + user la admin -> ok
    public function test_can_delete_an_exists_tag_with_admin_logged_in() {
        $tag = factory(Tag::class)->create();

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/tags/'.$tag->slug);

        $response->assertStatus(204);
    }
    // TODO: Xoa 1 tag, ton tai + user k phai admin -> 403
    public function test_cannot_delete_an_exists_tag_with_member_logged_in() {
        $tag = factory(Tag::class)->create();

        $response = $this->actingAs($this->member)->json('DELETE', '/api/tags/'.$tag->slug);

        $response->assertStatus(403);
    }
    // TODO: Xoa 1 tag, khong ton tai -> 404 not found
    public function test_cannot_delete_a_not_exists_tag() {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/tags/'.$tag->slug);

        $response->assertStatus(404);
    }
    // ----
}
