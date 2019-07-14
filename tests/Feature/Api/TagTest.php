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
    // TODO: Tag tag, chua login -> 403
    // TODO: Tao tag, da login, nhung truyen thieu data required (name || slug) => 422
    // ----
    // TODO: Xem 1 tag, ton tai -> ok
    // TODO: Xem 1 tag, khong ton tai -> 404 not found
    // ----
    // TODO: Sua 1 tag, ton tai + user la admin -> ok
    // TODO: Sua 1 tag, ton tai + user k phai admin -> 403
    // TODO: Sua 1 tag, ton tai + user la admin, nhung data sai (name || slug bi trung) -> 422
    // TODO: Sua 1 tag, khong ton tai -> 404 not found
    // ----
    // TODO: Xoa 1 tag, ton tai + user la admin -> ok
    // TODO: Xoa 1 tag, ton tai + user k phai admin -> 403
    // TODO: Xoa 1 tag, khong ton tai -> 404 not found
    // ----
}
