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
        $response = $this->actingAs($this->user)->json('POST', '/api/articles/'.$article->id.'/bookmark');
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'id' => $article->id,
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
        $response = $this->json('POST', '/api/articles/'.$article->id.'/bookmark');
        $response->assertStatus(401);
    }

    // test: remove bookmark đúng người tạo => 204
    public function test_remove_bookmark()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        factory(Bookmark::class)->create([
            'user_id' => $this->user->id,
            'article_id' => $article->id,
        ]);
        $response = $this->actingAs($this->user)->json('DELETE', '/api/articles/'.$article->id.'/bookmark');
        $response->assertStatus(204);
    }

    // test: remove bookmark sai người tạo || sai article => 403
    public function test_remove_bookmark_wrong_creator()
    {
        $otherUser = factory(User::class)->create();
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        factory(Bookmark::class)->create([
            'user_id' => $this->user->id,
            'article_id' => $article->id,
        ]);
        $response = $this->actingAs($otherUser)->json('DELETE', '/api/articles/'.$article->id.'/bookmark');
        $response->assertStatus(403);
    }

    // test: remove bookmark chưa đăng nhâp => 401
    public function test_remove_bookmark_unauthorize()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        factory(Bookmark::class)->create([
            'user_id' => $this->user->id,
            'article_id' => $article->id,
        ]);
        $response = $this->json('DELETE', '/api/articles/'.$article->id.'/bookmark');
        $response->assertStatus(401);
    }
}
