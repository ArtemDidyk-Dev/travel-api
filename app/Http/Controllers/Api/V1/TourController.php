<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\TourFilterRequests;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use App\Models\Travel;
use App\Services\TourServiceInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class TourController extends Controller
{
    public function __construct(
        private readonly TourServiceInterface $tourService
    ) {
    }

    #[OA\Get(path: '/api/v1/travels/{travel}/tours', summary: 'Tours get', tags: ['Tours'])]
    #[OA\Parameter(name: 'travel', description: 'Slug travel', in: 'path', required: true, example: 'aut-totam')]
    #[OA\Parameter(
        name: 'page',
        description: 'The page number to retrieve',
        in: 'query',
        required: false,
        example: '1'
    )]
    #[OA\Parameter(
        name: 'start_date',
        description: 'The Start Date tours',
        in: 'query',
        required: false,
        example: '2025-02-09'
    )]
    #[OA\Parameter(
        name: 'end_date',
        description: 'The End Date tours',
        in: 'query',
        required: false,
        example: '2025-02-15'
    )]
    #[OA\Parameter(
        name: 'price_from',
        description: 'The price from tours',
        in: 'query',
        required: false,
        example: '204.32'
    )]
    #[OA\Parameter(
        name: 'price_to',
        description: 'The price to tours',
        in: 'query',
        required: false,
        example: '726.63'
    )]
    #[OA\Parameter(
        name: 'sort_by',
        description: 'Field to sort by',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['price', 'start_date', 'end_date']),
        example: 'price'
    )]
    #[OA\Parameter(
        name: 'sort_order',
        description: 'Sorting order',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']),
        example: 'asc'
    )]
    #[OA\Response(
        response: '200',
        description: 'Tours get',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 11),
                            new OA\Property(property: 'name', type: 'string', example: 'vel eaque'),
                            new OA\Property(property: 'start_date', type: 'string', example: '2025-02-09'),
                            new OA\Property(property: 'end_date', type: 'string', example: '2025-02-14'),
                            new OA\Property(property: 'price', type: 'string', example: '204.32'),
                        ],
                        type: 'object'
                    )
                ),
                new OA\Property(
                    property: 'links',
                    properties: [
                        new OA\Property(
                            property: 'first',
                            type: 'string',
                            example: 'http://api-laravel.localhost/api/travels/aut-totam/tours?page=1'
                        ),
                        new OA\Property(
                            property: 'last',
                            type: 'string',
                            example: 'http://api-laravel.localhost/api/travels/aut-totam/tours?page=4'
                        ),
                        new OA\Property(property: 'prev', type: 'string', example: null, nullable: true),
                        new OA\Property(
                            property: 'next',
                            type: 'string',
                            example: 'http://api-laravel.localhost/api/travels/aut-totam/tours?page=2'
                        ),
                    ],
                    type: 'object'
                ),
                new OA\Property(
                    property: 'meta',
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'from', type: 'integer', example: 1),
                        new OA\Property(property: 'last_page', type: 'integer', example: 4),
                        new OA\Property(property: 'per_page', type: 'integer', example: 5),
                        new OA\Property(property: 'to', type: 'integer', example: 5),
                        new OA\Property(property: 'total', type: 'integer', example: 17),
                        new OA\Property(
                            property: 'links',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'url', type: 'string'),
                                    new OA\Property(property: 'label', type: 'string'),
                                    new OA\Property(property: 'active', type: 'boolean'),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation errors',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(
                    property: 'errors',
                    properties: [
                        new OA\Property(
                            property: 'sort_by',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['The sort by field must be one of price, start_date, end_date']
                        ),
                        new OA\Property(
                            property: 'sort_order',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['The sort order field must be one of asc, desc']
                        ),
                    ],
                    type: 'object'
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Record not found.')],
            type: 'object'
        )
    )]
    public function index(Travel $travel, TourFilterRequests $filterRequests): AnonymousResourceCollection
    {
        $tours = $this->tourService->filters($travel, $filterRequests->all());
        return TourResource::collection($tours);
    }

    #[OA\Get(
        path: '/api/v1/travels/{travel}/tours/{tour}',
        description: 'Shows all comments of the resource. Only visible to users with the Admin or Editor roles.',
        summary: 'Tour get',
        tags: ['Tours']
    )]
    #[OA\Parameter(name: 'travel', description: 'Slug travel', in: 'path', required: true, example: 'aut-totam')]
    #[OA\Parameter(name: 'tour', description: 'Tour id', in: 'path', required: true, example: '12')]
    #[OA\Response(
        response: '200',
        description: 'Tour get',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 11),
                            new OA\Property(property: 'name', type: 'string', example: 'vel eaque'),
                            new OA\Property(property: 'start_date', type: 'string', example: '2025-02-09'),
                            new OA\Property(property: 'end_date', type: 'string', example: '2025-02-14'),
                            new OA\Property(property: 'price', type: 'string', example: '204.32'),
                            new OA\Property(
                                property: 'images',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 11),
                                        new OA\Property(
                                            property: 'url',
                                            type: 'string',
                                            example: 'http://travel-api.localhost/storage/public/images/tours/67ade644283f63.07998705.png'
                                        ),
                                    ],
                                    type: 'object'
                                ),
                                example: [
                                    [
                                        'id' => 11,
                                        'url' => 'http://travel-api.localhost/storage/public/images/tours/67ade644283f63.07998705.png',
                                    ],
                                    [
                                        'id' => 12,
                                        'url' => 'http://travel-api.localhost/storage/public/images/tours/anotherimage.png',
                                    ],
                                ]
                            ),
                            new OA\Property(
                                property: 'comments',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 11),
                                        new OA\Property(
                                            property: 'text',
                                            type: 'string',
                                            example: 'Deserunt ut explicabo quis expedita. Dolorem fuga accusamus qui nemo minima. Rem et nam blanditiis commodi ex'
                                        ),
                                        new OA\Property(
                                            property: 'images',
                                            type: 'array',
                                            items: new OA\Items(
                                                properties: [
                                                    new OA\Property(property: 'id', type: 'integer', example: 11),
                                                    new OA\Property(
                                                        property: 'url',
                                                        type: 'string',
                                                        example: 'http://travel-api.localhost/storage/public/images/tours/67ade644283f63.07998705.png'
                                                    ),
                                                ],
                                                type: 'object'
                                            ),
                                            example: [
                                                [
                                                    'id' => 11,
                                                    'url' => 'http://travel-api.localhost/storage/public/images/comments/67ade644283f63.07998705.png',
                                                ],
                                                [
                                                    'id' => 12,
                                                    'url' => 'http://travel-api.localhost/storage/public/images/comments/anotherimage.png',
                                                ],
                                            ]
                                        ),
                                        new OA\Property(property: 'created_at', type: 'string', example: '2025 Feb 09'),
                                        new OA\Property(property: 'user', type: 'string', example: 'Luz Christiansen'),
                                        new OA\Property(
                                            property: 'is_public',
                                            description: 'Shows the public status of the resource. Only visible to users with the Admin or Editor roles.',
                                            type: 'boolean',
                                            example: true
                                        ),
                                    ],
                                    type: 'object'
                                ),
                            ),
                        ],
                        type: 'object'
                    )
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Record not found.')],
            type: 'object'
        )
    )]
    public function show(Travel $travel, Tour $tour): TourResource
    {
        if ($tour->travels->isNot($travel)) {
            abort(404);
        }
        $tour->load(['images', 'comments.images', 'comments.user']);
        return new TourResource($tour);
    }
}
