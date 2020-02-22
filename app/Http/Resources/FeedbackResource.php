<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'reference' => $this->reference,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'resolve' => $this->resolve,
            'article' => new ArticleOnlyResource($this->article),
            'user' => new UserResource($this->user),
            'publishedAt' => $this->created_at->diffForHumans(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
