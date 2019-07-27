<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
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

    // ------
    // TODO: Tao article, neu da login, -> ok
    public function test_can_create_article_if_logged_in() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->make();
        $response = $this->actingAs($this->user)->json('POST', '/api/articles', [
            'title' => $article->title,
            'content' => $article->content,
            'category' => $category->id,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'title' => $article->title,
            'content' => $article->content,
        ]);
    }
    // TODO: Tag article, chua login -> 403
    public function test_cannot_create_article_if_not_logged_in() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->make();
        $response = $this->json('POST', '/api/articles', [
            'title' => $article->title,
            'content' => $article->content,
            'category' => $category->id,
        ]);

        $response->assertStatus(401);
    }
    // TODO: Tao article, da login, nhung truyen thieu data required (name || content || category) => 422
    public function test_cannot_create_article_if_logged_in_but_missing_title() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->make();
        $response = $this->actingAs($this->user)->json('POST', '/api/articles', [
            'content' => $article->content,
            'category' => $category->id,
        ]);

        $response->assertStatus(422);
    }
    // TODO: Tao article, da login, nhung truyen sai data (category != uuid, hoac category k ton tai) => 422
    public function test_cannot_create_article_if_logged_in_but_category_not_exists() {
        $category = factory(Category::class)->make();
        $article = factory(Article::class)->make();
        $response = $this->actingAs($this->user)->json('POST', '/api/articles', [
            'content' => $article->content,
            'category' => $category->id,
        ]);

        $response->assertStatus(422);
    }
    // ----
    // TODO: Xem 1 article, ton tai -> ok
    public function test_can_view_an_exists_article() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $response = $this->json('GET', '/api/articles/'.$article->slug);

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => $article->title,
            'content' => $article->content,
        ]);
    }
    // TODO: Xem 1 article, khong ton tai -> 404 not found
    public function test_cannot_view_a_not_exists_article() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->make([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $response = $this->json('GET', '/api/articles/'.$article->slug);

        $response->assertStatus(404);
    }
    // ----
    // TODO: Sua 1 article, ton tai + user la admin -> ok
    public function test_can_edit_an_exists_article_with_admin_logged_in()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $updateArticle = factory(Article::class)->make();
        $response = $this->actingAs($this->admin)->json('PUT', '/api/articles/'.$article->slug, [
            'title' => $updateArticle->title,
            'content' => $updateArticle->content,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => $updateArticle->title,
            'content' => $updateArticle->content,
        ]);
    }
    // TODO: Sua 1 article, ton tai + user la tac gia -> ok
    public function test_can_edit_an_exists_article_with_author_logged_in()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $updateArticle = factory(Article::class)->make();
        $response = $this->actingAs($this->user)->json('PUT', '/api/articles/'.$article->slug, [
            'title' => $updateArticle->title,
            'content' => $updateArticle->content,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => $updateArticle->title,
            'content' => $updateArticle->content,
        ]);
    }
    // TODO: Sua 1 article, ton tai + user khong phai tac gia -> 403
    public function test_cannot_edit_an_exists_article_with_not_author_logged_in()
    {
        $category = factory(Category::class)->create();
        $updater = factory(User::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $updateArticle = factory(Article::class)->make();
        $response = $this->actingAs($updater)->json('PUT', '/api/articles/'.$article->slug, [
            'title' => $updateArticle->title,
            'content' => $updateArticle->content,
        ]);

        $response->assertStatus(403);
    }
    // TODO: Sua 1 article, ton tai + user la admin, nhung data sai (category moi khong ton tai, hoac sai) -> 422
    public function test_cannot_edit_an_exists_article_with_admin_logged_in_but_category_not_exists()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $updateCategory = factory(Category::class)->make();
        $response = $this->actingAs($this->admin)->json('PUT', '/api/articles/'.$article->slug, [
            'category' => $updateCategory->id
        ]);

        $response->assertStatus(422);
    }
    // TODO: Sua 1 article, khong ton tai -> 404 not found
    public function test_cannot_edit_a_not_exists_article()
    {
        $article = factory(Article::class)->make();
        $updateArticle = factory(Article::class)->make();
        $response = $this->actingAs($this->admin)->json('PUT', '/api/articles/'.$article->slug, [
            'title' => $updateArticle->title,
            'content' => $updateArticle->content,
        ]);

        $response->assertStatus(404);
    }
    // ----
    // TODO: Xoa 1 article, ton tai + user la admin -> ok
    public function test_can_delete_an_exists_article_with_admin_logged_in()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $response = $this->actingAs($this->admin)->json('DELETE', '/api/articles/'.$article->slug);

        $response->assertStatus(204);
    }
    // TODO: Xoa 1 article, ton tai + user la tac gia -> ok
    public function test_can_delete_an_exists_article_with_author_logged_in()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $response = $this->actingAs($this->user)->json('DELETE', '/api/articles/'.$article->slug);

        $response->assertStatus(204);
    }
    // TODO: Xoa 1 article, ton tai + user khong phai tac gia -> 403
    public function test_cannot_delete_an_exists_article_with_not_author_logged_in()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $deleter = factory(User::class)->create();
        $response = $this->actingAs($deleter)->json('DELETE', '/api/articles/'.$article->slug);

        $response->assertStatus(403);
    }
    // TODO: Xoa 1 article, khong ton tai -> 404 not found

    public function test_cannot_delete_a_not_exists_article()
    {
        $article = factory(Article::class)->make();
        $response = $this->actingAs($this->admin)->json('DELETE', '/api/articles/'.$article->slug);

        $response->assertStatus(404);
    }

    // --- popular, featured articles
    //TODO: lay list popular articles, guest -> 200
    public function test_can_get_list_popular_articles() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class, 10)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->json('GET', '/api/articles/popular');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'slug', 'title', 'content', 'excerpt', 'seen_count', 'thumbnail',
                    'author', 'category', 'tags', 'claps', 'publishedAt', 'createdAt', 'updatedAt'
                ]
            ]
        ]);
    }

    //TODO: lay list featured articles, guest -> 200

    public function test_can_get_list_featured_articles() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class, 10)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->json('GET', '/api/articles/featured');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'slug', 'title', 'content', 'excerpt', 'seen_count', 'thumbnail',
                    'author', 'category', 'tags', 'claps', 'publishedAt', 'createdAt', 'updatedAt'
                ]
            ]
        ]);
    }
}
