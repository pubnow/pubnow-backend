<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'content' => $this->content,
            'excerpt' => excerpt($this->content, 200),
            'seen_count' => $this->seen_count,
            'thumbnail' => thumbnail($this->content),
            'author' => new UserResource($this->author),
            'category' => new CategoryOnlyResource($this->category),
            'tags' => TagOnlyResource::collection($this->tags),
            'comments_count' => $this->comments()->count(),
            'claps' => $this->claps()->sum('count'),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
