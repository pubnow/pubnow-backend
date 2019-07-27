<?php

namespace Tests\Feature\Api;

use Illuminate\Http\UploadedFile;
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
                'description' => $category->description,
                'image' => $category->image,
            ]);
        });
    }

    // Tao category, neu user la admin
    public function test_can_create_category_if_user_is_admin()
    {
        $categoryData = factory(Category::class)->make();
        $image = UploadedFile::fake()->create('category_image.png');

        $response = $this->actingAs($this->admin)->json('POST', '/api/categories', [
            'name' => $categoryData->name,
            'description' => $categoryData->description,
            'image' => $image,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => $categoryData->name,
            'description' => $categoryData->description,
        ]);
    }

    // Tao category, neu user khong phai admin -> 403
    public function test_cant_create_category_if_user_is_not_admin()
    {
        $categoryData = factory(Category::class)->make();

        $response = $this->actingAs($this->member)->json('POST', '/api/categories', [
            'name' => $categoryData->name,
            'description' => $categoryData->description,
            'image' => $categoryData->image,
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
    // Tao category, da login, nhung truyen thieu data required (name) => 422
    public function test_logged_in_but_lack_of_data()
    {
        $categoryData = factory(Category::class)->make();

        $response = $this->actingAs($this->admin)->json('POST', '/api/categories', [
            'description' => $categoryData->description,
            'image' => $categoryData->image,
        ]);

        $response->assertStatus(422);
    }
    //----
    // Sua 1 category, ton tai + user la admin -> ok
    public function test_can_update_exists_category_with_admin_logged_in()
    {
        $categoryData = factory(Category::class)->create();
        $updateCategoryData = factory(Category::class)->make();
        $image = UploadedFile::fake()->create('tag_image.png');

        $response = $this->actingAs($this->admin)->json('PUT', '/api/categories/' . $categoryData->slug, [
            'name' => $updateCategoryData->name,
            'description' => $updateCategoryData->description,
            'image' => $image,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $updateCategoryData->name,
            'description' => $updateCategoryData->description,
        ]);
    }
    // Sua 1 category, ton tai + user k phai admin -> 403
    public function test_cannot_update_exists_category_with_member_logged_in()
    {
        $categoryData = factory(Category::class)->create();
        $updateCategoryData = factory(Category::class)->make();

        $response = $this->actingAs($this->member)->json('PUT', '/api/categories/' . $categoryData->slug, [
            'name' => $updateCategoryData->name,
            'description' => $updateCategoryData->description,
            'image' => $updateCategoryData->image,
        ]);

        $response->assertStatus(403);
    }
    // Sua 1 category, ton tai + user la admin, nhung data sai (name bi trung) -> 422
    public function test_cannot_update_exists_category_with_admin_logged_in_but_dupplicate_name()
    {
        $categoryDatas = factory(Category::class, 2)->create();
        $updateCategoryData = factory(Category::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/categories/' . $categoryDatas[0]->slug, [
            'name' => $categoryDatas[1]->name,
            'description' => $updateCategoryData->description,
            'image' => $updateCategoryData->image,
        ]);

        $response->assertStatus(422);
    }
    // Sua 1 category, khong ton tai -> 404 not found
    public function test_cannot_update_not_exists_category()
    {
        $categoryData = factory(Category::class)->make();

        $response = $this->actingAs($this->member)->json('PUT', '/api/categories/' . $categoryData->slug, [
            'name' => $categoryData->name,
            'description' => $categoryData->description,
            'image' => $categoryData->image,
        ]);

        $response->assertStatus(404);
    }
    //----
    // Xoa 1 category, ton tai + user la admin -> ok
    public function test_delete_exists_category_with_admin_logged_in()
    {
        $categoryData = factory(Category::class)->create();

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/categories/' . $categoryData->slug);

        $response->assertStatus(204);
    }
    // Xoa 1 category, ton tai + user k phai admin -> 403
    public function test_cannot_delete_exists_category_with_member_logged_in()
    {
        $categoryData = factory(Category::class)->create();

        $response = $this->actingAs($this->member)->json('DELETE', '/api/categories/' . $categoryData->slug);

        $response->assertStatus(403);
    }
    // Xoa 1 category, khong ton tai -> 404 not found
    public function test_cannot_delete_not_exists_category_with_admin_logged_in()
    {
        $categoryData = factory(Category::class)->make();

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/categories/' . $categoryData->slug);

        $response->assertStatus(404);
    }

    // --- Follow category
    // Follow 1 category, ton tai, user da dang nhap -> ok
    public function test_user_can_follow_a_category() {
        $category = factory(Category::class)->create();

        $response = $this->actingAs($this->member)->json('POST', 'api/categories/'.$category->slug.'/follow');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.followingCategories');
    }

    // Follow 1 category, ton tai, user chua dang nhap -> 401
    public function test_cannot_follow_a_category_if_not_logged_in() {
        $category = factory(Category::class)->create();

        $response = $this->json('POST', 'api/categories/'.$category->slug.'/follow');

        $response->assertStatus(401);
    }

    // Follow 1 category, khong ton tai, user da dang nhap -> 404
    public function test_user_cannot_follow_a_category_if_not_exists() {
        $category = factory(Category::class)->make();

        $response = $this->actingAs($this->member)->json('POST', 'api/categories/'.$category->slug.'/follow');

        $response->assertStatus(404);
    }

    // --- Unfollow
    // Unfollow 1 category, ton tai, user da dang nhap -> ok
    public function test_user_can_unfollow_a_followed_category() {
        $category = factory(Category::class)->create();
        $this->member->followingCategories()->attach($category);

        $response = $this->actingAs($this->member)->json('DELETE', 'api/categories/'.$category->slug.'/follow');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data.followingCategories');
    }

    // Unfollow 1 category, ton tai, user chua dang nhap -> 401
    public function test_cannot_unfollow_a_followed_category_if_not_logged_in() {
        $category = factory(Category::class)->create();
        $this->member->followingCategories()->attach($category);

        $response = $this->json('DELETE', 'api/categories/'.$category->slug.'/follow');

        $response->assertStatus(401);
    }

    // Unfollow 1 category, khong ton tai, user da dang nhap -> 404
    public function test_user_cannot_unfollow_a_followed_category_if_not_exists() {
        $category = factory(Category::class)->make();

        $response = $this->actingAs($this->member)->json('DELETE', 'api/categories/'.$category->slug.'/follow');

        $response->assertStatus(404);
    }

    // Unfollow 1 category, khong ton tai, user da dang nhap -> 404
    public function test_user_cannot_unfollow_a_not_followed_category() {
        $category = factory(Category::class)->create();

        $response = $this->actingAs($this->member)->json('DELETE', 'api/categories/'.$category->slug.'/follow');

        $response->assertStatus(422);
    }

    // --- Followers
    // Get list followers of a category
    public function test_can_get_list_followers_of_a_category() {
        $category = factory(Category::class)->create();
        $category->followers()->attach($this->member);

        $response = $this->json('GET', 'api/categories/'.$category->slug.'/followers');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    //----
}
