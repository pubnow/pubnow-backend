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

    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::where(['username' => 'admin'])->first();
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
            'name' => $categoryData->name,
            'slug' => $categoryData->slug,
            'description' => $categoryData->description,
            'image' => $categoryData->image,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $categoryData->name,
            'slug' => $categoryData->slug,
            'description' => $categoryData->description,
            'image' => $categoryData->image,
        ]);
    }
}
