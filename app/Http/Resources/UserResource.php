<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles', RoleResource::collection($this->roles)),
            'created_at' => Carbon::make($this->created_at)?->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::make($this->updated_at)?->format('Y-m-d H:i:s'),
        ];
    }
}
