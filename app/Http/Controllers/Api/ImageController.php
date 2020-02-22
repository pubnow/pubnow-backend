<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\Image\StoreImage;
use App\Http\Resources\ImageResource;

class ImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function editorUpload(StoreImage $request)
    {
        $path = Storage::disk('s3')->put('images/originals', $request->file);
        $request->merge([
            'title' => base_convert(time(), 10, 36) . '-' . str_slug($request->file->getClientOriginalName()),
            'size' => $request->file->getClientSize(),
            'path' => $path,
        ]);
        $image = Image::create($request->only('title', 'size', 'path'));
        return $image;
    }

    public function upload(StoreImage $request)
    {
        $image = $this->editorUpload($request);
        return new ImageResource($image);
    }

    public function editorGallery()
    {
        $images = auth()->user()->images;
        return $images;
    }

    public function gallery()
    {
        return ImageResource::collection($this->editorGallery());
    }
}
