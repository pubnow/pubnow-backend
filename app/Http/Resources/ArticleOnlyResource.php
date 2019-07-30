<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleOnlyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => excerpt($this->content, 200),
            'author' => new UserResource($this->author),
            'category' => new CategoryOnlyResource($this->category),
            'tags' => TagOnlyResource::collection($this->tags),
            'seen_count' => $this->seen_count,
            'thumbnail' => thumbnail($this->content),
            'claps' => $this->claps()->sum('count'),
            'comments_count' => $this->comments()->count(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
