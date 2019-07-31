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
        $response->assertJsonStructure(['data' => ['id', 'title', 'link', 'size', 'uploadedTime']]);

        $response = $this->actingAs($user)->json('GET', '/api/gallery');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure(['data' => [['id', 'title', 'link', 'size', 'uploadedTime']]]);
    }

    public function test_image_api_for_editor()
    {
        $uploadedFile = UploadedFile::fake()->create('sexy_girl.jpg');
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->json('POST', '/api/editor-upload', [
            'file' => $uploadedFile
        ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'title', 'link', 'size', 'uploaded_time']);

        $response = $this->actingAs($user)->json('GET', '/api/editor-gallery');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure([['id', 'title', 'url', 'size', 'uploaded_time']]);
    }
}
