<?php

namespace Tests\Feature\Api;

use Illuminate\Http\UploadedFile;
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
    // Tao tag, neu da login, -> ok
    public function test_can_create_tag_if_logged_in()
    {
        $tag = factory(Tag::class)->make();
        $image = UploadedFile::fake()->create('tag_image.png');

        $response = $this->actingAs($this->member)->json('POST', '/api/tags', [
            'name' => $tag->name,
            'description' => $tag->description,
            'image' => $image,
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'name' => $tag->name,
            'description' => $tag->description,
        ]);
    }
    // Tag tag, chua login -> 403
    public function test_cannot_create_tag_if_not_logged_in()
    {
        $tag = factory(Tag::class)->make();

        $response = $this->json('POST', '/api/tags', [
            'name' => $tag->name,
            'description' => $tag->description,
        ]);

        $response->assertStatus(401);
    }
    // Tao tag, da login, nhung truyen thieu data required (name) => 422
    public function test_cannot_create_tag_if_logged_in_but_missing_name()
    {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->member)->json('POST', '/api/tags', [
            'description' => $tag->description,
        ]);

        $response->assertStatus(422);
    }
    // ----
    // Xem 1 tag, ton tai -> ok
    public function test_can_get_an_exists_tag()
    {
        $tag = factory(Tag::class)->create();

        $response = $this->json('GET', '/api/tags/' . $tag->slug);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $tag->name,
            'slug' => $tag->slug,
            'description' => $tag->description,
            'image' => $tag->image,
        ]);
    }
    // Xem 1 tag, khong ton tai -> 404 not found
    public function test_cannot_get_a_not_exists_tag()
    {
        $tag = factory(Tag::class)->make();

        $response = $this->json('GET', '/api/tags/' . $tag->slug);

        $response->assertStatus(404);
    }
    // ----
    // TODO: Sua 1 tag, ton tai + user la admin -> ok
    public function test_can_update_a_exists_tag_with_admin_logged_in()
    {
        $tag = factory(Tag::class)->create();
        $updateTag = factory(Tag::class)->make();
        $image = UploadedFile::fake()->create('tag_image.png');

        $response = $this->actingAs($this->admin)->json('PUT', '/api/tags/' . $tag->slug, [
            'name' => $updateTag->name,
            'description' => $updateTag->description,
            'image' => $image,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $updateTag->name,
            'slug' => $tag->slug,
            'description' => $updateTag->description,
        ]);
    }
    // TODO: Sua 1 tag, ton tai + user k phai admin -> 403
    public function test_cannot_update_a_exists_tag_with_member_logged_in()
    {
        $tag = factory(Tag::class)->create();
        $updateTag = factory(Tag::class)->make();

        $response = $this->actingAs($this->member)->json('PUT', '/api/tags/' . $tag->slug, [
            'name' => $updateTag->name,
            'description' => $updateTag->description,
        ]);

        $response->assertStatus(403);
    }
    // Sua 1 tag, ton tai + user la admin, nhung data sai (name bi trung) -> 422
    public function test_cannot_update_a_exists_tag_with_admin_logged_in_but_dupplicate_name()
    {
        $tags = factory(Tag::class, 2)->create();
        $updateTag = factory(Tag::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/tags/' . $tags[0]->slug, [
            'name' => $tags[1]->name,
            'description' => $updateTag->description,
        ]);

        $response->assertStatus(422);
    }
    // Sua 1 tag, khong ton tai -> 404 not found
    public function test_cannot_update_a_not_exists_tag()
    {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/tags/' . $tag->slug, [
            'name' => $tag->name,
            'description' => $tag->description,
        ]);

        $response->assertStatus(404);
    }
    // ----
    // Xoa 1 tag, ton tai + user la admin -> ok
    public function test_can_delete_an_exists_tag_with_admin_logged_in()
    {
        $tag = factory(Tag::class)->create();

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/tags/' . $tag->slug);

        $response->assertStatus(204);
    }
    // TODO: Xoa 1 tag, ton tai + user k phai admin -> 403
    public function test_cannot_delete_an_exists_tag_with_member_logged_in()
    {
        $tag = factory(Tag::class)->create();

        $response = $this->actingAs($this->member)->json('DELETE', '/api/tags/' . $tag->slug);

        $response->assertStatus(403);
    }
    // Xoa 1 tag, khong ton tai -> 404 not found
    public function test_cannot_delete_a_not_exists_tag()
    {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/tags/' . $tag->slug);

        $response->assertStatus(404);
    }

    // --- Follow tag
    // Follow 1 tag, ton tai, user da dang nhap -> ok
    public function test_user_can_follow_a_tag() {
        $tag = factory(Tag::class)->create();

        $response = $this->actingAs($this->member)->json('POST', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.followingTags');
    }

    // Follow 1 tag, ton tai, user chua dang nhap -> 401
    public function test_cannot_follow_a_tag_if_not_logged_in() {
        $tag = factory(Tag::class)->create();

        $response = $this->json('POST', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(401);
    }

    // Follow 1 tag, ton tai, da follow roi, user da dang nhap -> 422
    public function test_user_cannot_follow_a_tag_if_followed() {
        $tag = factory(Tag::class)->create();
        $this->member->followingTags()->attach($tag);

        $response = $this->actingAs($this->member)->json('POST', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(422);
    }

    // Follow 1 tag, khong ton tai, user da dang nhap -> 404
    public function test_user_cannot_follow_a_tag_if_not_exists() {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->member)->json('POST', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(404);
    }

    // --- Unfollow
    // Unfollow 1 tag, ton tai, user da dang nhap -> ok
    public function test_user_can_unfollow_a_followed_tag() {
        $tag = factory(Tag::class)->create();
        $this->member->followingtags()->attach($tag);

        $response = $this->actingAs($this->member)->json('DELETE', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data.followingTags');
    }

    // Unfollow 1 tag, ton tai, user chua dang nhap -> 401
    public function test_cannot_unfollow_a_followed_tag_if_not_logged_in() {
        $tag = factory(Tag::class)->create();
        $this->member->followingtags()->attach($tag);

        $response = $this->json('DELETE', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(401);
    }

    // Unfollow 1 tag, khong ton tai, user da dang nhap -> 404
    public function test_user_cannot_unfollow_a_followed_tag_if_not_exists() {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->member)->json('DELETE', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(404);
    }

    // Unfollow 1 tag, khong ton tai, user da dang nhap -> 422
    public function test_user_cannot_unfollow_a_not_followed_tag() {
        $tag = factory(Tag::class)->create();

        $response = $this->actingAs($this->member)->json('DELETE', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(422);
    }

    // Unfollow 1 tag, khong ton tai, user da dang nhap -> 404
    public function test_user_cannot_unfollow_a_not_exists_tag() {
        $tag = factory(Tag::class)->make();

        $response = $this->actingAs($this->member)->json('DELETE', 'api/tags/'.$tag->slug.'/follow');

        $response->assertStatus(404);
    }

    // --- Followers
    // Get list followers of a tag
    public function test_can_get_list_followers_of_a_tag() {
        $tag = factory(Tag::class)->create();
        $tag->followers()->attach($this->member);

        $response = $this->json('GET', 'api/tags/'.$tag->slug.'/followers');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }
    // ----
}
