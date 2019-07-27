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
        $response = $this->json('POST', '/api/articles/'.$article->id.'/bookmark');
//        dd($response);
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
        $response = $this->actingAs($this->user)->json('DELETE', '/api/articles/'.$article->id.'/bookmark');
        $response->assertStatus(204);
    }

    // test: tạo bookmark khi bài viết không tồn tại => 500
    public function test_add_bookmark_with_wrong_article_id()
    {
        $fake = '293b47f4-a01b-427d-ae43-d61092f021fa';
        $response = $this->actingAs($this->user)->json('POST', '/api/articles/'.$fake.'/bookmark');
        $response->assertStatus(500);
    }
}
