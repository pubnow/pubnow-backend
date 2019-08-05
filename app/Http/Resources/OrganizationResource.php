<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'email' => $this->email,
            'owner' => new UserResource($this->user),
            'members_count' => $this->members()->count(),
            'following' => $following,
            'description' => $this->description,
            'logo' => $this->image ? $this->image->url : '',
            'active' => $this->active,
            'publishedAt' => $this->created_at->diffForHumans(),
            'createdAt' => $this->created_at,
        ];
    }
}
