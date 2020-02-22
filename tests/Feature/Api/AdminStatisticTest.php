<?php

namespace Tests\Feature\Api;

use App\Http\Resources\UserResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminStatisticTest extends TestCase
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

    // lấy statistic thành công
    public function test_get_statistical_admin()
    {
        $from = '2019-7-8';
        $to = '2019-7-8';
        // fake 2 user
        $usersList = [
            $user1 = factory(User::class)->create(),
            $user2 = factory(User::class)->create()
        ];
        // tạo bài viết theo user. cho thằng user1 là featured
        $category = factory(Category::class)->create();
        factory(Article::class, 5)->create([
            'user_id' => $user1->id,
            'category_id' => $category->id,
        ]);
        factory(Article::class, 2)->create([
            'user_id' => $user2->id,
            'category_id' => $category->id,
        ]);
        // đây là bài featured
        $featureArticle = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $this->actingAs($this->user)->json('POST', 'api/comments', [
            'article_id' => $featureArticle->id,
            'content' => 'featured article comment'
        ]);
        // tạo tags
        factory(Tag::class, 3)->create();
        $response = $this->actingAs($this->admin)->json('GET', 'api/admin/statistic', [
            'start' => $from,
            'end' => $to
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'from' => $from,
                'to' => $to,
                'users' => [
                    'new' => 0,
                    'total' => count($usersList) + 2,
                ],
                'tags' => [
                    'new' => 0,
                    'total' => 3
                ],
                'articles' => [
                    'new' => 0,
                    'total' => 8
                ],
                'categories' => [
                    'new' => 0,
                    'total' => 1
                ],
            ]
        ]);
    }

    // lấy statistic nhưng không phải admin
    public function test_get_statistical_not_admin()
    {
        $from = '2019-7-8';
        $to = '2019-7-8';
        $response = $this->actingAs($this->user)->json('GET', 'api/admin/statistic', [
            'start_date' => $from,
            'end_date' => $to
        ]);
        $response->assertStatus(403);
    }

    // lấy statistic không đăng nhập
    public function test_get_statistical_unauthorize()
    {
        $from = '2019-7-8';
        $to = '2019-7-8';
        $response = $this->json('GET', 'api/admin/statistic', [
            'start_date' => $from,
            'end_date' => $to
        ]);
        $response->assertStatus(401);
    }

    // lấy statistic admin nhưng không truyền ngày bắt đầu và kết thúc
    public function test_get_statistical_not_passing_params()
    {
        $response = $this->actingAs($this->admin)->json('GET', 'api/admin/statistic');
        $response->assertStatus(500);
    }
}
