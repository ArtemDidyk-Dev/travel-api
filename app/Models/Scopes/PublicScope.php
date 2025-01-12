<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enum\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class PublicScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user?->roles()->where('name', Role::ADMIN->name)->orWhere('name', Role::EDITOR->name)->exists()) {
            $builder->where('is_public', true);
        }

    }
}
