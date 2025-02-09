<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $start_date
 * @property string $end_date
 * @property int $price
 */
final class TourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'price' => number_format($this->price, 2),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
