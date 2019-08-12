<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeedbackTest extends TestCase
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
    // test: get list feedback, unauthorize => 401
    public function test_get_list_feedback_unauthorize()
    {
        $response = $this->json('GET', '/api/feedback');

        $response->assertStatus(401);
    }

    // test: get list feedback, đã đăng nhập nhưng không phải admin => 401
    public function test_get_list_feedback_not_admin()
    {
        $response = $this->actingAs($this->user)->json('GET', '/api/feedback');

        $response->assertStatus(403);
    }

    // test: get list feedback, đã đăng nhập as admin => 200
    public function test_get_list_feedback_as_admin()
    {
        $response = $this->actingAs($this->admin)->json('GET', '/api/feedback');

        $response->assertStatus(200);
    }

    // test: get a feedback, unauthorize => 401
    public function test_get_a_feedback_unauthorize()
    {
        $feedback = factory(Feedback::class)->create();
        $response = $this->json('GET', '/api/feedback/' . $feedback->id);

        $response->assertStatus(401);
    }

    // test: get a feedback, đã đăng nhập nhưng không phải admin => 401
    public function test_get_a_feedback_not_admin()
    {
        $feedback = factory(Feedback::class)->create();
        $response = $this->actingAs($this->user)->json('GET', '/api/feedback/' . $feedback->id);

        $response->assertStatus(403);
    }

    // test: get a feedback, đã đăng nhập as admin => 200
    public function test_get_a_feedback_as_admin()
    {
        $feedback = factory(Feedback::class)->create();
        $response = $this->actingAs($this->admin)->json('GET', '/api/feedback/' . $feedback->id);

        $response->assertStatus(200);
    }

    // test: get article feedback, đã đăng nhập as admin => 200
    public function test_get_article_feedback_as_admin()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $feedbackFake = factory(Feedback::class)->make();
        $feedback = factory(Feedback::class, 3)->create([
            'article_id' => $article->id,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'reference' => $feedbackFake->reference,
            'content' => $feedbackFake->content,
        ]);
        $response = $this->actingAs($this->admin)->json('GET', '/api/feedback');
        $articleFeedback = $article->feedback;
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    // test: create a feedback, chưa đăng nhập + k truyền username || email
    public function test_create_a_feedback_unauthorize_invalid_params()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $feedback = factory(Feedback::class)->make();
        $response = $this->json('POST', '/api/feedback', [
            'article_id' => $article->id,
            'reference' => $feedback->reference,
            'content' => $feedback->content,
            'type' => 0,
        ]);
        $response->assertStatus(422);
    }

    // test: create a feedback, chưa đăng nhập + truyền username || email valid
    public function test_create_a_feedback_unauthorize_valid_params()
    {
        $feedback = factory(Feedback::class)->make();
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $response = $this->json('POST', '/api/feedback', [
            'article_id' => $article->id,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'title' => $feedback->title,
            'reference' => $feedback->reference,
            'content' => $feedback->content,
            'type' => 0,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'reference' => $feedback->reference,

            'content' => $feedback->content,
        ]);
    }

    // test: create a feedback, đã đăng nhập + truyền username || email valid
    public function test_create_a_feedback_authorize_valid_params()
    {
        $feedback = factory(Feedback::class)->make();
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        $response = $this->actingAs($this->user)->json('POST', '/api/feedback', [
            'article_id' => $article->id,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'title' => $feedback->title,
            'reference' => $feedback->reference,
            'content' => $feedback->content,
            'type' => 0,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'reference' => $feedback->reference,
            'content' => $feedback->content,
        ]);
    }

    // test: edit a feedback, chưa đăng nhập
    public function test_edit_a_feedback_unauthorize()
    {
        $feedback = factory(Feedback::class)->create();
        $editFeedback = factory(Feedback::class)->make();
        $response = $this->json('PUT', '/api/feedback/' . $feedback->id, [
            'reference' => $editFeedback->reference,
            'content' => $editFeedback->content,
        ]);

        $response->assertStatus(401);
    }

    // test: edit a feedback, đã đăng nhập nhưng đ phải là admin thế mới cay
    public function test_edit_a_feedback_authorize_not_admin()
    {
        $feedback = factory(Feedback::class)->create();
        $editFeedback = factory(Feedback::class)->make();
        $response = $this->actingAs($this->user)->json('PUT', '/api/feedback/' . $feedback->id, [
            'reference' => $editFeedback->reference,
            'content' => $editFeedback->content,
        ]);

        $response->assertStatus(403);
    }

    // test: edit a feedback, đã đăng nhập và là admin
    public function test_edit_a_feedback_authorize_as_admin()
    {
        $feedback = factory(Feedback::class)->create();
        $editFeedback = factory(Feedback::class)->make();
        $response = $this->actingAs($this->admin)->json('PUT', '/api/feedback/' . $feedback->id, [
            'resolve' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'resolve' => true,
        ]);
    }

    // test: xóa a feedback, đã đăng nhập và là admin
    public function test_delete_a_feedback_authorize_as_admin()
    {
        $feedback = factory(Feedback::class)->create();
        $response = $this->actingAs($this->admin)->json('DELETE', '/api/feedback/' . $feedback->id);
        $response->assertStatus(204);
    }

    // test: xóa a feedback, đã đăng nhập nhưng không phải là admin
    public function test_delete_a_feedback_authorize_not_admin()
    {
        $feedback = factory(Feedback::class)->create();
        $response = $this->actingAs($this->user)->json('DELETE', '/api/feedback/' . $feedback->id);
        $response->assertStatus(403);
    }


    // test: xóa a feedback, chưa đăng nhập
    public function test_delete_a_feedback_unauthorize()
    {
        $feedback = factory(Feedback::class)->create();
        $response = $this->json('DELETE', '/api/feedback/' . $feedback->id);
        $response->assertStatus(401);
    }
}
