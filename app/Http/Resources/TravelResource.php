<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enum\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

final class TravelResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'number_of_days' => $this->number_of_days,
            'number_of_nights' => $this->number_of_nights,
            'is_public' => $this->when($this->userHasRole([Role::ADMIN, Role::EDITOR]), $this->is_public),
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
