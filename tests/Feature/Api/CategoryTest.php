<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Role;
use App\Models\User;

class CategoryTest extends TestCase
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

    // Get danh sach category
    public function test_can_get_list_categories()
    {
        $categories = factory(Category::class, 5)->create();

        $response = $this->json('GET', '/api/categories');

        $response->assertStatus(200);

        $response->assertJsonCount(count($categories), 'data');

        $categories->each(function ($category) use ($response) {
            $response->assertJsonFragment([
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
            ]);
        });
    }

    // Tao category, neu user la admin
    public function test_can_create_category_if_user_is_admin()
    {
        $categoryData = factory(Category::class)->make();

        $response = $this->actingAs($this->admin)->json('POST', '/api/categories', [
            'category' => [
                'name' => $categoryData->name,
                'slug' => $categoryData->slug,
                'description' => $categoryData->description,
                'image' => $categoryData->image,
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => $categoryData->name,
            'slug' => $categoryData->slug,
            'description' => $categoryData->description,
            'image' => $categoryData->image,
        ]);
    }

    // Tao category, neu user khong phai admin -> 403
    public function test_cant_create_category_if_user_is_not_admin()
    {
        $categoryData = factory(Category::class)->make();

        $response = $this->actingAs($this->member)->json('POST', '/api/categories', [
            'category' => [
                'name' => $categoryData->name,
                'slug' => $categoryData->slug,
                'description' => $categoryData->description,
                'image' => $categoryData->image,
            ],
        ]);

        $response->assertStatus(403);
    }
    // Xem 1 category, ton tai -> ok
    public function test_can_view_a_category_if_exist()
    {
        $category = factory(Category::class)->create();
        $response = $this->json('GET', '/api/categories/' . $category->slug);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image' => $category->image
        ]);
    }
    // Xem 1 category, khong ton tai -> 404 not found
    public function test_cant_view_a_category_if_not_exist()
    {
        $category = factory(Category::class)->make();
        $response = $this->json('GET', '/api/categories/' . $category->slug);
        $response->assertStatus(404);
    }
    //----
    // TODO: Sua 1 category, ton tai + user la admin -> ok
    // TODO: Sua 1 category, ton tai + user k phai admin -> 403
    // TODO: Sua 1 category, khong ton tai -> 404 not found
    //----
    // TODO: Xoa 1 category, ton tai + user la admin -> ok
    // TODO: Xoa 1 category, ton tai + user k phai admin -> 403
    // TODO: Xoa 1 category, khong ton tai -> 404 not found
    //----
}
