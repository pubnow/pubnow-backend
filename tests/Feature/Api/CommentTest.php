<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // --- Create
    //TODO: test user can create comment (parent_id = null) -> 201
    //TODO: test guest cannot create comment -> 401
    //TODO: test user cannot create comment if content is empty -> 422
    //TODO: test user cannot create comment if article not exists -> 404
    //TODO: test user cannot reply comment if parent comment not exists -> 422

    // --- Update
    //TODO: test user can update a comment -> 200
    //TODO: test user cannot update a comment if content is empty -> 200 (content not change)
    //TODO: test guest cannot update a comment -> 401
    //TODO: test user cannot update a comment if not the owner -> 403
    //TODO: test user cannot update a not exists comment -> 404

    // --- Delete
    //TODO: test admin can delete a comment -> 204
    //TODO: test user can delete own comment -> 204
    //TODO: test user can delete a comment has child comments -> 204
    //TODO: test user cannot delete a comment if not the owner -> 403
    //TODO: test guest cannot delete a comment -> 401
    //TODO: test user cannot delete a comment if not exists -> 404

}
