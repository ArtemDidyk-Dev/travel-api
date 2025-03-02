<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTravelRequest;
use App\Http\Requests\Admin\UpdateTravelRequest;
use App\Http\Resources\TravelResource;
use App\Models\Travel;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Travel Management', description: 'Manage Travel')]
class TravelController extends Controller
{
    #[OA\Post(
        path: '/api/v1/admin/travels/store',
        operationId: 'storeTravel',
        description: 'Create a new travel with the specified parameters',
        summary: 'Create a new travel',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Travel Management'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['name', 'description', 'number_of_days'],
                properties: [
                    new OA\Property(property: 'name', description: 'Travel name', type: 'string', example: 'vel eaque'),
                    new OA\Property(
                        property: 'description',
                        description: 'Travel description',
                        type: 'string',
                        example: 'Vel vel minima sunt earum. Temporibus sint voluptas aliquam dolor aut voluptatem delectus.'
                    ),
                    new OA\Property(
                        property: 'slug',
                        description: 'URL slug for the travel',
                        type: 'string',
                        example: 'vel-eaque'
                    ),
                    new OA\Property(
                        property: 'is_public',
                        description: 'Visibility of the travel',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'number_of_days',
                        description: 'Number of days for the travel',
                        type: 'integer',
                        example: 12
                    ),
                ],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Travel created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 11),
                        new OA\Property(property: 'name', type: 'string', example: 'vel eaque'),
                        new OA\Property(property: 'slug', type: 'string', example: 'vel-eaque'),
                        new OA\Property(
                            property: 'description',
                            type: 'string',
                            example: 'Vel vel minima sunt earum. Temporibus sint voluptas aliquam dolor aut voluptatem delectus.'
                        ),
                        new OA\Property(property: 'number_of_days', type: 'integer', example: 7),
                        new OA\Property(property: 'number_of_nights', type: 'integer', example: 6),
                        new OA\Property(property: 'is_public', type: 'boolean', example: true),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'An error occurred while creating the travel',
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
    #[OA\Response(
        response: 401,
        description: 'Unauthorized',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation errors',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                    new OA\Property(property: 'errors', type: 'object'),
                ],
                type: 'object'
            )
        )
    )]
    public function store(StoreTravelRequest $request): TravelResource|JsonResponse
    {
        try {
            $travel = Travel::create($request->validated());
            return TravelResource::make($travel)->response()->setStatusCode(201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    #[OA\Put(
        path: '/api/v1/admin/travels/{travel}',
        operationId: 'updateTravel',
        description: 'Update the specified travel with new data',
        summary: 'Update a specific travel',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Travel Management'],
        parameters: [
            new OA\Parameter(
                name: 'travel',
                description: 'ID of the travel to be updated',
                in: 'path',
                required: true,
            ),
        ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['name', 'description', 'number_of_days'],
                properties: [
                    new OA\Property(property: 'name', description: 'Travel name', type: 'string', example: 'vel eaque'),
                    new OA\Property(
                        property: 'description',
                        description: 'Travel description',
                        type: 'string',
                        example: 'Vel vel minima sunt earum. Temporibus sint voluptas aliquam dolor aut voluptatem delectus.'
                    ),
                    new OA\Property(
                        property: 'slug',
                        description: 'URL slug for the travel',
                        type: 'string',
                        example: 'vel-eaque'
                    ),
                    new OA\Property(
                        property: 'is_public',
                        description: 'Visibility of the travel',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'number_of_days',
                        description: 'Number of days for the travel',
                        type: 'integer',
                        example: 12
                    ),
                ],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Travel updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 11),
                        new OA\Property(property: 'name', type: 'string', example: 'vel eaque'),
                        new OA\Property(property: 'slug', type: 'string', example: 'vel-eaque'),
                        new OA\Property(
                            property: 'description',
                            type: 'string',
                            example: 'Vel vel minima sunt earum. Temporibus sint voluptas aliquam dolor aut voluptatem delectus.'
                        ),
                        new OA\Property(property: 'number_of_days', type: 'integer', example: 7),
                        new OA\Property(property: 'number_of_nights', type: 'integer', example: 6),
                        new OA\Property(property: 'is_public', type: 'boolean', example: true),
                    ],
                    type: 'object'
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
        response: 422,
        description: 'Validation errors',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                    new OA\Property(property: 'errors', type: 'object'),
                ],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'An error occurred while updating the travel',
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
    public function update(UpdateTravelRequest $request, Travel $travel): TravelResource|JsonResponse
    {
        try {
            $travel->update($request->validated());
            return TravelResource::make($travel);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    #[OA\Delete(
        path: '/api/v1/admin/travels/{travel}',
        operationId: 'deleteTravel',
        description: 'Delete a specific travel',
        summary: 'Delete a specific travel',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Travel Management'],
        parameters: [
            new OA\Parameter(
                name: 'travel',
                description: 'ID of the travel to be deleted',
                in: 'path',
                required: true,
            ),
        ]
    )]
    #[OA\Response(response: 204, description: 'Travel deleted successfully')]
    #[OA\Response(
        response: 404,
        description: 'Travel not found',
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
        description: 'An error occurred while deleting the travel',
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
    public function destroy(Travel $travel): JsonResponse
    {
        try {
            $travel->delete();
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }
}
