<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuthApiTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCanGetCurrentUserProfd()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/api/auth/me');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
        ]);
    }
}
