<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enum\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'is_public' => $this->when($this->userHasRole([Role::ADMIN, Role::EDITOR]), (bool) $this->is_public),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at->format('Y M d'),
            'user' => $this->whenLoaded('user', function () {
                return $this->user->name;
            }),
            'tour' => TourResource::make($this->whenLoaded('tour')),
        ];
    }

    private function userHasRole(array $roles): bool
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return false;
        }

        return $user->newQuery()
            ->hasRoles($roles)
            ->exists();
    }
}
