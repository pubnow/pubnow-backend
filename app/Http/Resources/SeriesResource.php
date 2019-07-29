<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        dd($this->articles);
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'content' => $this->content,
//            'articles' => ArticleOnlyResource::collection($this->articles),
            'author' => new UserResource($this->author),
            'publishedAt' => $this->created_at->diffForHumans(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
