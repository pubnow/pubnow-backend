<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClapTest extends TestCase
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
    // --- Clap
    // Test clap a exists article, logged in -> 201
    // Test clap a exists article, logged in, clapped -> 200
    // Test clap a exists article, not logged in -> 401
    // Test clap a not exists article, logged in -> 404

    // --- Unclap
    // Test unclap a exists article, clapped -> 204
    // Test unclap a exists article, not clapped -> 404
    // Test
}
