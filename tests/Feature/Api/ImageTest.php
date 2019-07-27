<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;

class ImageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3');
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_image_api()
    {
        $uploadedFile = UploadedFile::fake()->create('sexy_girl.jpg');
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('POST', '/api/upload', [
            'file' => $uploadedFile
        ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'title', 'file', 'size', 'uploadedTime']]);

        $response = $this->actingAs($user)->json('GET', '/api/gallery');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure(['data' => [['id', 'title', 'file', 'size', 'uploadedTime']]]);
    }
}
