<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserWithFollowingTagsResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'isAdmin' => $this->isAdmin(),
            'bio' => $this->bio,
            'avatar' => $this->avatar_url,
            'role' => new RoleResource($this->role),
            'followingTags' => TagOnlyResource::collection($this->followingTags)
        ];
    }
}
