<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $following = false;
        $logged = $request->user();
        if ($logged && $this->followers()->find($logged->id)) {
            $following = true;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->photo ? $this->photo->url : '',
            'articles_count' => $this->articles()->count(),
            'followers_count' => $this->followers()->count(),
            'articles' => ArticleOnlyResource::collection($this->articles->take(5)),
            'following' => $following,
            'publishedAt' => $this->created_at->diffForHumans(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
