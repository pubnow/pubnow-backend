<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'isAdmin' => $this->isAdmin(),
            'bio' => $this->bio,
            'following' => $following,
            'avatar' => $this->avatar_url,
            'role' => new RoleResource($this->role),
            'articles' => $this->articles()->count(),
            'followers' => $this->followers()->count(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
