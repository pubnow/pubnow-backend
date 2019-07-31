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
        $clapped = false;
        $bookmarked = false;
        $logged = $request->user();
        if ($logged) {
            if ($this->claps()->where('user_id', $logged->id)->first()) {
                $clapped = true;
            }

            if ($this->usersBookmarked()->find($logged->id)) {
                $bookmarked = true;
            }
        }
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
            'claps' => $this->claps()->sum('count'),
            'clapped' => $clapped,
            'bookmaked' => $bookmarked,
            'publishedAt' => $this->created_at->diffForHumans(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'draft' => $this->draft,
            'private' => $this->private,
        ];
    }
}
