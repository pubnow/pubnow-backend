<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Category;
use App\Models\Series;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeriesTest extends TestCase
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

    // test: lấy danh sách series, khi chưa đăng nhập
    public function test_get_series_without_log_in() {
        $series1 = factory(Series::class)->make();
        $series2 = factory(Series::class)->make();
        $listSeries = [
            factory(Series::class)->create([
                'user_id' => $this->user->id,
                'title' => $series1->title,
                'content' => $series1->content
            ]),
            factory(Series::class)->create([
                'user_id' => $this->user->id,
                'title' => $series2->title,
                'content' => $series2->content
            ])
        ];
        $response = $this->json('GET', '/api/series');

        $response->assertStatus(200);
        foreach ($listSeries as $series) {
            $response->assertJsonFragment([
                'id' => $series->id,
            ]);
        }
    }

    // test: lấy danh sách series, khi đã đăng nhập
    public function test_get_series_logged_in() {
        $series1 = factory(Series::class)->make();
        $series2 = factory(Series::class)->make();
        $listSeries = [
            factory(Series::class)->create([
                'user_id' => $this->user->id,
                'title' => $series1->title,
                'content' => $series1->content
            ]),
            factory(Series::class)->create([
                'user_id' => $this->user->id,
                'title' => $series2->title,
                'content' => $series2->content
            ])
        ];
        $response = $this->actingAs($this->user)->json('GET', '/api/series');

        $response->assertStatus(200);
        foreach ($listSeries as $series) {
            $response->assertJsonFragment([
                'id' => $series->id,
            ]);
        }
    }

    // test: lấy 1 series, khi đăng nhập
    public function test_get_a_series_logged_in() {
        $series = factory(Series::class)->make();
        $seriesReal = factory(Series::class)->create([
            'user_id' => $this->user->id,
            'title' => $series->title,
            'content' => $series->content
        ]);
        $response = $this->actingAs($this->user)->json('GET', '/api/series/'.$seriesReal->slug);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $seriesReal->id
        ]);
    }

    // test: tạo series, khi chưa đăng nhập
    public function test_create_series_without_log_in() {
        $series = factory(Series::class)->make();
        $response = $this->json('POST', '/api/series', [
            'title' => $series->title,
            'content' => $series->content,
        ]);

        $response->assertStatus(401);
    }

    // test: tạo series, khi đã đăng nhập
    public function test_create_series_logged_in() {
        $series = factory(Series::class)->make();
        $response = $this->actingAs($this->user)->json('POST', '/api/series', [
            'title' => $series->title,
            'content' => $series->content,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'title' => $series->title,
            'content' => $series->content,
        ]);
    }

    // test: tạo series with article, khi đã đăng nhập
    public function test_create_series_with_article_logged_in() {
        $series = factory(Series::class)->make();
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $arrayArticlesId = [$article->id];
        $response = $this->actingAs($this->user)->json('POST', '/api/series', [
            'title' => $series->title,
            'content' => $series->content,
            'articles' => $arrayArticlesId
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'title' => $series->title,
            'content' => $series->content,
        ]);
        foreach ($arrayArticlesId as $id) {
            $response->assertJsonFragment([
                'id' => $id,
            ]);
        }
    }

    // test: tạo series with wrong id article, khi đã đăng nhập
    public function test_create_series_with_wrong_article_logged_in() {
        $series = factory(Series::class)->make();
        $arrayArticlesId = [$series->id]; // fake id series for article id
        $response = $this->actingAs($this->user)->json('POST', '/api/series', [
            'title' => $series->title,
            'content' => $series->content,
            'articles' => $arrayArticlesId
        ]);

        $response->assertStatus(500);
    }

    // test: xóa series, khi đã đăng nhập + đúng tác giả
    public function test_delete_series_logged_in_right_author() {
        $series = factory(Series::class)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->json('DELETE', '/api/series/'.$series->slug);

        $response->assertStatus(204);
    }

    // test: xóa 1 series không tồn tại, khi đã đăng nhập + đúng tác giả
    public function test_delete_not_exist_series_logged_in_right_author() {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($this->user)->json('DELETE', '/api/series/'.$article->slug);

        $response->assertStatus(404);
    }

    // test: xóa series, khi đã đăng nhập + không phải là tác giả
    public function test_delete_series_logged_in_wrong_author() {
        $series = factory(Series::class)->create([
            'user_id' => $this->user->id
        ]);

        $deleter = factory(User::class)->create();
        $response = $this->actingAs($deleter)->json('DELETE', '/api/series/'.$series->slug);

        $response->assertStatus(403);
    }

    // test: xóa series, khi chưa đăng nhập -> 401
    public function test_delete_series_without_logged_in() {
        $series = factory(Series::class)->make();

        $response = $this->json('DELETE', '/api/series/'.$series->slug);

        $response->assertStatus(401);
    }

    // test: sửa series, khi chưa đăng nhập -> 401
    public function test_update_series_without_logged_in() {
        $series = factory(Series::class)->make();

        $response = $this->json('PUT', '/api/series/'.$series->slug);

        $response->assertStatus(401);
    }

    // test: sửa series, khi đã đăng nhập + có bài viết + đúng dạng data
    public function test_update_series_logged_in_exist_valid() {
        $series = factory(Series::class)->create([
            'user_id' => $this->user->id
        ]);
        $updateSeries = factory(Series::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/series/'.$series->slug, [
            'title' => $updateSeries->title,
            'content' => $updateSeries->content
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => $updateSeries->title,
            'content' => $updateSeries->content,
        ]);
    }

    // test: sửa series, khi đã đăng nhập + có bài viết + thêm article + đúng dạng data
    public function test_update_series_add_article_logged_in_exist_valid() {
        $series = factory(Series::class)->create([
            'user_id' => $this->user->id
        ]);
        $updateSeries = factory(Series::class)->make();
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $arrayArticlesId = [$article->id];
        $response = $this->actingAs($this->admin)->json('PUT', '/api/series/'.$series->slug, [
            'title' => $updateSeries->title,
            'content' => $updateSeries->content,
            'articles' => $arrayArticlesId
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => $updateSeries->title,
            'content' => $updateSeries->content,
        ]);
        foreach ($arrayArticlesId as $id) {
            $response->assertJsonFragment([
                'id' => $id,
            ]);
        }
    }

    // test: sửa series, khi đã đăng nhập + bài viết không tồn tại
    public function test_update_series_logged_in_not_exist() {
        $series = factory(Series::class)->make();
        $updateSeries = factory(Series::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/series/'.$series->slug, [
            'title' => $updateSeries->title,
            'content' => $updateSeries->content
        ]);

        $response->assertStatus(404);
    }
}
