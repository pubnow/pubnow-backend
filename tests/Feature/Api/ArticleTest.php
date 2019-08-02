<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Clap;
use App\Models\Comment;
use App\Models\Tag;
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
            'clapped' => false,
            'bookmarked' => false,
        ]);
    }
    // TODO: Xem 1 article, clapped, bookmarked

    public function test_can_view_an_exists_article_clapped_bookmarked() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $clap = Clap::create([
            'user_id' => $this->user->id,
            'article_id' => $article->id,
            'count' => 1,
        ]);
        $bookmark = Bookmark::create([
            'user_id' => $this->user->id,
            'article_id' => $article->id,
        ]);
        $response = $this->actingAs($this->user)->json('GET', '/api/articles/'.$article->slug);

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => $article->title,
            'content' => $article->content,
            'clapped' => true,
            'bookmarked' => true,
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
                    'id', 'slug', 'title', 'content', 'excerpt', 'seen_count', 'thumbnail', 'clapped', 'bookmarked',
                    'author', 'category', 'tags', 'claps', 'publishedAt', 'createdAt', 'updatedAt'
                ]
            ]
        ]);
    }

    //TODO: lay list featured articles, guest -> 200
    public function test_can_get_list_featured_articles()
    {
        $category = factory(Category::class)->create();
        $articles = factory(Article::class, 10)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->json('GET', '/api/articles/featured');
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'slug', 'title', 'content', 'excerpt', 'seen_count', 'thumbnail', 'clapped', 'bookmarked',
                    'author', 'category', 'tags', 'claps', 'publishedAt', 'createdAt', 'updatedAt'
                ]
            ]
        ]);
    }

    // TODO: Lấy bài viết có filter draft và private mà chưa đăng nhập
    public function test_filter_draft_private_method_unauthorize()
    {
        $category = factory(Category::class)->create();
        factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => true,
            'private' => false
        ]);
        factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => true
        ]);
        $notDraft = [
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => false
            ]),
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => false
            ])
        ];
        $response = $this->json('GET', '/api/articles');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        foreach ($notDraft as $article) {
            $response->assertJsonFragment([
                'draft' => $article->draft,
                'private' => $article->private,
            ]);
        }
    }

    // TODO: Lấy bài viết có filter draft và private mà đã đăng nhập
    public function test_filter_draft_private_method_authorize()
    {
        $category = factory(Category::class)->create();
        factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => true,
            'private' => false
        ]);
        $notDraftNPrivate = [
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => false
            ]),
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => false
            ]),
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => true
            ])
        ];
        $response = $this->actingAs($this->user)->json('GET', '/api/articles');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        foreach ($notDraftNPrivate as $article) {
            $response->assertJsonFragment([
                'draft' => $article->draft,
                'private' => $article->private,
            ]);
        }
    }

    // TODO: Lấy bài viết popular có filter draft và private mà đã đăng nhập
    public function test_get_popular_filter_draft_private_method_authorize()
    {
        $category = factory(Category::class)->create();
        factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => true,
            'private' => false
        ]);
        $notDraftNPrivate = [
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => false
            ]),
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => false
            ]),
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => true
            ])
        ];
        $response = $this->actingAs($this->user)->json('GET', '/api/articles/popular');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        foreach ($notDraftNPrivate as $article) {
            $response->assertJsonFragment([
                'draft' => $article->draft,
                'private' => $article->private,
            ]);
        }
    }

    // TODO: Lấy bài viết featured có filter draft và private mà đã đăng nhập
    public function test_get_feature_filter_draft_private_method_authorize()
    {
        $category = factory(Category::class)->create();
        factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => true,
            'private' => false
        ]);
        $notDraftNPrivate = [
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => false
            ]),
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => false
            ]),
            factory(Article::class)->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'draft' => false,
                'private' => true
            ])
        ];
        $response = $this->actingAs($this->user)->json('GET', '/api/articles/featured');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        foreach ($notDraftNPrivate as $article) {
            $response->assertJsonFragment([
                'draft' => $article->draft,
                'private' => $article->private,
            ]);
        }
    }

    // TODO: Lấy 1 bài viết private mà đã chưa đăng nhập hoặc k đúng người đăng => 401
    public function test_get_a_articles_filter_private_method_Unauthorized()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => true
        ]);
        $response = $this->json('GET', '/api/articles/'.$article->slug);

        $response->assertStatus(401);
    }

    // TODO: Lấy bài viết theo user mà có filter draft và private
    public function test_get_user_articles_filter_draft_private()
    {
        $otherUsers = factory(User::class)->create();
        $category = factory(Category::class)->create();
        factory(Article::class, 3)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => false
        ]);
        // bài viết của user có private
        factory(Article::class, 2)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => true
        ]);
        factory(Article::class)->create([
            'user_id' => $otherUsers->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => false
        ]);
        factory(Article::class)->create([
            'user_id' => $otherUsers->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => true
        ]);
        $response = $this->json('GET', '/api/users/' .$this->user->username. '/articles');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    // TODO: Lấy bài viết theo tag mà có filter draft và private
    public function test_get_tag_articles_filter_draft_private()
    {
        $tag = factory(Tag::class)->create();
        $category = factory(Category::class)->create();
        $tags = [$tag->id];
        $article_1 = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => false
        ]);
        $article_2 = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => false
        ]);
        $article_3 = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'draft' => false,
            'private' => true
        ]);
        $article_1->tags()->attach($tags);
        $article_2->tags()->attach($tags);
        $article_3->tags()->attach($tags);
        $response = $this->json('GET', '/api/tags/' .$tag->slug. '/articles');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }
}
