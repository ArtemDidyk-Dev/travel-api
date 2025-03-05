<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyFileRequest;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Http\Resources\TourResource;
use App\Http\Resources\TravelResource;
use App\Models\Tour;
use App\Models\Travel;
use App\Services\ImageInterface;
use App\Services\TourServiceInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tour Management', description: 'Manage Tour')]
class TourController extends Controller
{
    public function __construct(
        private readonly TourServiceInterface $tourService,
        private readonly ImageInterface $imageService,
    ) {
    }

    #[OA\Post(
        path: '/api/v1/admin/travels/{travel}/tours/store',
        operationId: 'storeTours',
        description: 'Stores a tour for a specific tour that belongs to a travel, with optional image attachments.',
        summary: 'Add a new tour',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Tour Management'],
    )]
    #[OA\Parameter(name: 'travel', description: 'ID of the travel to retrieve', in: 'path', required: true, example: 4)]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['name', 'price', 'start_date', 'end_date'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 5000,
                        minLength: 1,
                        example: 'This tour was amazing!'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        exclusiveMinimum: 0.1,
                        example: 2.2
                    ),
                    new OA\Property(property: 'start_date', type: 'string', example: '2025-02-09'),
                    new OA\Property(property: 'end_date', type: 'string', example: '2025-02-10'),
                    new OA\Property(
                        property: 'images[]',
                        type: 'array',
                        items: new OA\Items(type: 'string', format: 'binary')
                    ),
                ]
            )
        )
    )]
    #[OA\Response(
        response: '201',
        description: 'Tour update',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
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
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Tour does not belong to the specified travel',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Not found')]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation failed',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(
                    property: 'errors',
                    properties: [
                        new OA\Property(
                            property: 'name',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The name field is required')
                        ),
                        new OA\Property(
                            property: 'price',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The price field is required'),
                            example: [
                                'The price field is required',
                                'The numeric field is required',
                                'The price min 0.01',
                            ]
                        ),
                        new OA\Property(
                            property: 'start_date',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The start_date field is required')
                        ),
                        new OA\Property(
                            property: 'end_date',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The end_date field is required'),
                            example: [
                                'The end date field must be a valid date.',
                                'The end date field must be a date after start date.',
                            ]
                        ),
                        new OA\Property(
                            property: 'images.0',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'The image must be a file of type: jpg, jpeg, png'
                            )
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'An error occurred. Please try again later.'
                ),
            ]
        )
    )]
    public function store(Travel $travel, StoreTourRequest $request): TravelResource|JsonResponse
    {

        try {
            $tour = $this->tourService->store($travel, $request->validated());
            return TourResource::make($tour)->response()->setStatusCode(201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.' . $e->getMessage(),
            ], 500);
        }
    }

    #[OA\POST(
        path: '/api/v1/admin/travels/{travel}/tours/{tour}',
        operationId: 'updateTour',
        description: 'Update the specified tour with new data',
        summary: 'Update a specific tour',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Tour Management'],
        parameters: [
            new OA\Parameter(
                name: 'travel',
                description: 'ID of the travel to be updated',
                in: 'path',
                required: true,
                example: 12
            ),
            new OA\Parameter(
                name: 'tour',
                description: 'ID of the tour to be deleted',
                in: 'path',
                required: true,
                example: 55,
            ),
        ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['name', 'price', 'start_date', 'end_date'],
                properties: [
                    new OA\Property(property: '_method', type: 'string', example: 'PUT'),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 5000,
                        minLength: 1,
                        example: 'This tour was amazing!'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        exclusiveMinimum: 0.1,
                        example: 2.2
                    ),
                    new OA\Property(property: 'start_date', type: 'string', example: '2025-02-09'),
                    new OA\Property(property: 'end_date', type: 'string', example: '2025-02-10'),
                    new OA\Property(property: 'images[132]', type: 'string', format: 'binary'),
                    new OA\Property(property: 'images[133]', type: 'string', format: 'binary'),

                ]
            )
        )
    )]
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
                        ],
                        type: 'object'
                    )
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Tour does not belong to the specified travel',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Not found')]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation failed',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(
                    property: 'errors',
                    properties: [
                        new OA\Property(
                            property: 'name',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The name field is required')
                        ),
                        new OA\Property(
                            property: 'price',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The price field is required'),
                            example: [
                                'The price field is required',
                                'The numeric field is required',
                                'The price min 0.01',
                            ]
                        ),
                        new OA\Property(
                            property: 'start_date',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The start_date field is required')
                        ),
                        new OA\Property(
                            property: 'end_date',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The end_date field is required'),
                            example: [
                                'The end date field must be a valid date.',
                                'The end date field must be a date after start date.',
                            ]
                        ),
                        new OA\Property(
                            property: 'images.0',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'The image must be a file of type: jpg, jpeg, png'
                            )
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'An error occurred. Please try again later.'
                ),
            ]
        )
    )]
    public function update(Travel $travel, Tour $tour, UpdateTourRequest $request): TourResource|JsonResponse
    {

        if ($tour->travels->isNot($travel)) {
            abort(404);
        }
        $this->tourService->update($tour, $request->validated());
        return TourResource::make($tour);

    }

    #[OA\Delete(
        path: '/api/v1/admin/travels/{travel}/tours/{tour}',
        operationId: 'deleteTour',
        description: 'Delete a specific tour',
        summary: 'Delete a specific tour',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Tour Management'],
        parameters: [
            new OA\Parameter(
                name: 'travel',
                description: 'ID of the travel to be deleted',
                in: 'path',
                required: true,
                example: 12,
            ),
            new OA\Parameter(
                name: 'tour',
                description: 'ID of the tour to be deleted',
                in: 'path',
                required: true,
                example: 55,
            ),
        ]
    )]
    #[OA\Response(response: 204, description: 'Tour deleted successfully')]
    #[OA\Response(
        response: 404,
        description: 'Tour not found',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Record not found.')],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'An error occurred while deleting the Tour',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'An error occurred. Please try again later.'
                    ),
                ],
                type: 'object'
            )
        )
    )]
    public function destroy(Travel $travel, Tour $tour): JsonResponse
    {
        if ($tour->travels->isNot($travel)) {
            abort(404);
        }
        try {
            $this->tourService->destroy($tour);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later. ' . $e->getMessage(),
            ], 500);
        }
    }

    #[OA\Delete(
        path: '/api/v1/admin/travels/{travel}/tours/{tour}/files',
        operationId: 'deleteTourFiles',
        description: 'Delete specific tour files',
        summary: 'Delete specific files',
        security: [[
            'sanctum' => [],
        ]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['images'],
                properties: [
                    new OA\Property(
                        property: 'images',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2]
                    ),
                ]
            )
        ),
        tags: ['Tour Management'],
        parameters: [
            new OA\Parameter(
                name: 'travel',
                description: 'ID of the travel',
                in: 'path',
                required: true,
                example: 12,
            ),
            new OA\Parameter(name: 'tour', description: 'ID of the tour', in: 'path', required: true, example: 55),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Files deleted successfully'),
            new OA\Response(
                response: 422,
                description: 'Validation failed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'An error occurred. Please try again later.'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function destroyFiles(Travel $travel, Tour $tour, DestroyFileRequest $request): JsonResponse
    {
        if ($tour->travels->isNot($travel)) {
            abort(404);
        }
        try {
            $this->imageService->delete($tour, $request->input('images'));
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later. ' . $e->getMessage(),
            ], 500);
        }

    }
}
