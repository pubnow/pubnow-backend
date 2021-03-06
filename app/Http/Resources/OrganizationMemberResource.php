<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationMemberResource extends JsonResource
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
            'status' => $this->pivot->status,
            'invite_id' => $this->pivot->id,
            'bio' => $this->bio,
            'avatar' => $this->avatarUrl,
            'role' => new RoleResource($this->role),
        ];
    }
}
