<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use App\Http\Resources\TravelResource;
use App\Models\Travel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'My Doc Api')]
#[OA\PathItem(path: '/api/v1/')]
class TravelController extends Controller
{
    #[OA\Get(path: '/api/v1/travels', description: 'Shows the public status of the resource. Only visible to users with the Admin or Editor roles.', summary: 'Travels get', tags: ['Travels'])]
    #[OA\Parameter(
        name: 'page',
        description: 'The page number to retrieve',
        in: 'query',
        required: false,
        example: '1'
    )]
    #[OA\Response(
        response: '200',
        description: 'Travels get',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
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
                            new OA\Property(
                                property: 'is_public',
                                description: 'Shows the public status of the resource. Only visible to users with the Admin or Editor roles.',
                                type: 'boolean',
                                example: true
                            ),
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
                            example: 'http://api-laravel.localhost/api/v1/travels?page=1'
                        ),
                        new OA\Property(
                            property: 'last',
                            type: 'string',
                            example: 'http://api-laravel.localhost/api/v1/travels?page=4'
                        ),
                        new OA\Property(property: 'prev', type: 'string', example: null, nullable: true),
                        new OA\Property(
                            property: 'next',
                            type: 'string',
                            example: 'http://api-laravel.localhost/api/v1/travels?page=2'
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
    public function index(): AnonymousResourceCollection
    {
        $travels = Travel::paginate();

        return TravelResource::collection($travels);
    }
}
