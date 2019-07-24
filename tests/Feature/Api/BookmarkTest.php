<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class BookmarkTest extends TestCase
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

    // test: tạo bookmark. khi đã đăng nhập? => 201 OK
    public function test_check_login_before_create_bookmark()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $response = $this->actingAs($this->user)->json('POST', '/api/bookmarks', [
            'article_id' => $article->id,
        ]);
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'content' => $article->content,
        ]);
    }

    // test: tạo bookmark. khi chưa đăng nhập? => 401 error
    public function test_check_not_login_before_create_bookmark()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $response = $this->json('POST', '/api/bookmarks', [
            'article_id' => $article->id,
        ]);
        $response->assertStatus(401);
    }

    // test: remove bookmark => 204
    public function test_remove_bookmark()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $bookmark = factory(Bookmark::class)->create([
            'user_id' => $this->user->id,
            'article_id' => $article->id,
        ]);
        $response = $this->actingAs($this->user)->json('DELETE', '/api/bookmarks/'.$bookmark->id);
        $response->assertStatus(204);
    }
}
