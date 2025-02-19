<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\ImagePath;
use App\Http\Filters\TourFilter;
use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Support\Facades\DB;

final readonly class TourService implements TourServiceInterface
{
    public function __construct(
        private ImageInterface $image,
    ) {
    }

    public function filters(Travel $travel, array $request)
    {
        $filters = new TourFilter(array_filter($request));
        return $travel
            ->tours()
            ->filter($filters)
            ->orderBy($request['sort_by'], $request['sort_order'])
            ->orderBy('start_date')
            ->paginate();
    }

    public function store(Travel $travel, array $data): Tour
    {
        $tour = $travel->tours()
            ->create([
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'price' => $data['price'],
            ]);
        if (isset($data['images'])) {
            $this->image->save(model: $tour, files: $data['images'], path: ImagePath::TOUR_PATH);
        }
        $tour->load('images');
        return $tour;
    }

    public function destroy(Tour $tour)
    {

        $filesId = $tour->images?->pluck('id')
            ->toArray();
        if ($filesId !== []) {
            $this->image->delete($tour, $filesId);
        }

        $tour->delete();
    }

    public function update(Tour $tour, array $data): void
    {
        DB::transaction(function () use ($tour, $data) {
            $tour->update([
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'price' => $data['price'],
            ]);
            if (isset($data['images'])) {
                $this->image->update($tour, files: $data['images'], path: ImagePath::TOUR_PATH, async: true);
            }
            $tour->load('images');

        });
    }
}
