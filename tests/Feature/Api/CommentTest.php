<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    protected $user;
    protected $admin;
    protected $article;
    protected $comment;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = factory(User::class)->create();
        $this->admin = User::where(['username' => 'admin'])->first();
        $category = factory(Category::class)->create();
        $this->article = factory(Article::class)->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id
        ]);
        $this->comment = Comment::create([
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
            'content' => 'sample'
        ]);
    }

    // --- Create
    //TODO: test user can create comment (parent_id = null) -> 201
    public function test_user_can_create_comment() {
        $response = $this->actingAs($this->user)->json('POST', 'api/comments', [
            'article_id' => $this->article->id,
            'content' => 'sample'
        ]);
        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'content' => 'sample',
                'user' => [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'bio' => $this->user->bio,
                ],
                'article' => [
                    'id' => $this->article->id,
                    'slug' => $this->article->slug,
                    'title' => $this->article->title,
                    'excerpt' => excerpt($this->article->content, 200),
                ]
            ]
        ]);
    }
    //TODO: test guest cannot create comment -> 401
    public function test_guest_cannot_create_comment() {
        $response = $this->json('POST', 'api/comments', [
            'article_id' => $this->article->id,
            'content' => 'sample'
        ]);
        $response->assertStatus(401);
    }
    //TODO: test user cannot create comment if content is empty -> 422
    public function test_user_cannot_create_comment_if_content_empty() {
        $response = $this->actingAs($this->user)->json('POST', 'api/comments', [
            'article_id' => $this->article->id,
        ]);
        $response->assertStatus(422);
    }
    //TODO: test user cannot create comment if article not exists -> 422
    public function test_user_cannot_create_comment_if_article_not_exists() {
        $article_id = $this->article->id;
        $this->article->delete();
        $response = $this->actingAs($this->user)->json('POST', 'api/comments', [
            'article_id' => $article_id,
            'content' => 'sample'
        ]);
        $response->assertStatus(422);
    }
    //TODO: test user cannot reply comment if parent comment not exists -> 422
    public function test_user_cannot_create_comment_if_parent_not_exists() {
        $parent_id = $this->comment->id;
        $this->article->delete();
        $response = $this->actingAs($this->user)->json('POST', 'api/comments', [
            'article_id' => $this->article->id,
            'parent_id' => $this->comment->id,
            'content' => 'sample'
        ]);
        $response->assertStatus(422);
    }

    // Test create comment, user, comment level 4
    public function test_user_cannot_create_comment_level_4() {
        $comment1 = Comment::create([
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
            'content' => 'abc',
            'parent_id' => $this->comment->id,
        ]);
        $comment2 = Comment::create([
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
            'content' => 'abc',
            'parent_id' => $comment1->id,
        ]);
        $response = $this->actingAs($this->user)->json('POST', 'api/comments', [
            'article_id' => $this->article->id,
            'content' => 'sample',
            'parent_id' => $comment2->id
        ]);
        $response->assertStatus(422);
    }

    // --- Update
    //TODO: test user can update a comment -> 200
    public function test_user_can_update_comment() {
        $response = $this->actingAs($this->user)->json('PUT', 'api/comments/'.$this->comment->id, [
            'content' => 'sample again'
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'content' => 'sample again',
                'user' => [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'bio' => $this->user->bio,
                ],
                'article' => [
                    'id' => $this->article->id,
                    'slug' => $this->article->slug,
                    'title' => $this->article->title,
                    'excerpt' => excerpt($this->article->content, 200),
                ]
            ]
        ]);
    }
    //TODO: test user cannot update a comment if content is empty -> 200 (content not change)
    public function test_user_can_update_comment_if_content_is_empty() {
        $response = $this->actingAs($this->user)->json('PUT', 'api/comments/'.$this->comment->id);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'content' => 'sample',
                'user' => [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'bio' => $this->user->bio,
                ],
                'article' => [
                    'id' => $this->article->id,
                    'slug' => $this->article->slug,
                    'title' => $this->article->title,
                    'excerpt' => excerpt($this->article->content, 200),
                ]
            ]
        ]);
    }
    //TODO: test guest cannot update a comment -> 401
    public function test_guest_cannot_update_comment() {
        $response = $this->json('PUT', 'api/comments/'.$this->comment->id, [
            'content' => 'sample again'
        ]);
        $response->assertStatus(401);
    }
    //TODO: test user cannot update a comment if not the owner -> 403
    public function test_user_cannot_update_comment_if_not_owner() {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('PUT', 'api/comments/'.$this->comment->id, [
            'content' => 'sample again'
        ]);
        $response->assertStatus(403);
    }
    //TODO: test user cannot update a not exists comment -> 404
    public function test_user_cannot_update_comment_if_not_exists() {
        $id = $this->comment->id;
        $this->comment->delete();
        $response = $this->actingAs($this->user)->json('PUT', 'api/comments/'.$id, [
            'content' => 'sample again'
        ]);
        $response->assertStatus(404);
    }

    // --- Delete
    //TODO: test admin can delete a comment -> 204
    public function test_user_can_delete_comment() {
        $response = $this->actingAs($this->user)->json('DELETE', 'api/comments/'.$this->comment->id);
        $response->assertStatus(204);
    }
    //TODO: test user can delete own comment -> 204
    public function test_admin_can_delete_comment() {
        $response = $this->actingAs($this->admin)->json('DELETE', 'api/comments/'.$this->comment->id);
        $response->assertStatus(204);
    }
    //TODO: test user can delete a comment has child comments -> 204
    public function test_user_can_delete_comment_has_child() {
        $comment = Comment::create([
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
            'content' => 'abc',
            'parent_id' => $this->comment->id,
        ]);
        $response = $this->actingAs($this->user)->json('DELETE', 'api/comments/'.$this->comment->id);
        $response->assertStatus(204);
    }
    //TODO: test user cannot delete a comment if not the owner -> 403
    public function test_user_cannot_delete_comment_if_not_owner() {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('DELETE', 'api/comments/'.$this->comment->id);
        $response->assertStatus(403);
    }
    //TODO: test guest cannot delete a comment -> 401
    public function test_guest_cannot_delete_comment() {
        $response = $this->json('DELETE', 'api/comments/'.$this->comment->id);
        $response->assertStatus(401);
    }
    //TODO: test user cannot delete a comment if not exists -> 404
    public function test_user_cannot_delete_comment_if_not_exists() {
        $id = $this->comment->id;
        $this->comment->delete();
        $response = $this->actingAs($this->user)->json('DELETE', 'api/comments/'.$id);
        $response->assertStatus(404);
    }

}
