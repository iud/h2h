<?php

namespace App\Controller;

use App\DTO\ContactFormRequest;
use App\Application\ContactMessageCreator;
use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepositoryInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/api/contact', name: 'api_contact_')]
#[OA\Tag(name: 'Contact Form')]
class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContactMessageCreator $creator,
        private readonly ContactMessageRepositoryInterface $contactMessageRepository,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'], format: 'json')]
    #[OA\Post(
        path: '/api/contact',
        summary: 'Zapisz wiadomość z formularza kontaktowego',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: ContactFormRequest::class)
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Wiadomość została zapisana pomyślnie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'fullName', type: 'string', example: 'Jan Kowalski'),
                        new OA\Property(property: 'email', type: 'string', example: 'jan@example.com'),
                        new OA\Property(property: 'message', type: 'string', example: 'Treść wiadomości'),
                        new OA\Property(
                            property: 'createdAt',
                            type: 'string',
                            format: 'date-time',
                            example: '2025-12-17T10:30:00+00:00'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Błędy walidacji',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'type',
                            type: 'string',
                            example: 'https://tools.ietf.org/html/rfc4918#section-11.2'
                        ),
                        new OA\Property(
                            property: 'title',
                            type: 'string',
                            example: 'Validation Failed'
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 422
                        ),
                        new OA\Property(
                            property: 'detail',
                            type: 'string',
                            example: 'Validation failed for the submitted data.'
                        ),
                        new OA\Property(
                            property: 'violations',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'propertyPath', type: 'string', example: 'fullName'),
                                    new OA\Property(
                                        property: 'title',
                                        type: 'string',
                                        example: 'Imię i nazwisko jest wymagane.'
                                    ),
                                    new OA\Property(property: 'parameters', type: 'object'),
                                    new OA\Property(
                                        property: 'type',
                                        type: 'string',
                                        example: 'urn:uuid:c1051bb4-d103-4f74-8988-acbcafc7f831'
                                    ),
                                ]
                            ),
                            example: [
                                [
                                    'propertyPath' => 'fullName',
                                    'title' => 'Imię i nazwisko jest wymagane.',
                                    'parameters' => [],
                                    'type' => 'urn:uuid:c1051bb4-d103-4f74-8988-acbcafc7f831',
                                ],
                                [
                                    'propertyPath' => 'email',
                                    'title' => 'Podany adres e-mail jest nieprawidłowy.',
                                    'parameters' => [],
                                    'type' => 'urn:uuid:c1051bb4-d103-4f74-8988-acbcafc7f832',
                                ],
                                [
                                    'propertyPath' => 'consent',
                                    'title' => 'Musisz wyrazić zgodę na przetwarzanie danych osobowych.',
                                    'parameters' => [],
                                    'type' => 'urn:uuid:c1051bb4-d103-4f74-8988-acbcafc7f833',
                                ],
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function create(#[MapRequestPayload] ContactFormRequest $contactRequest): JsonResponse
    {
        $contactMessage = $this->creator->create($contactRequest);

        return $this->json($contactMessage, Response::HTTP_CREATED);
    }

    #[Route('', name: 'list', methods: ['GET'], format: 'json')]
    #[OA\Get(
        path: '/api/contact',
        summary: 'Pobierz listę wiadomości z paginacją',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 20, minimum: 1, maximum: 100)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista wiadomości (paginowana)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(
                                        property: 'fullName',
                                        type: 'string',
                                        example: 'Jan Kowalski'
                                    ),
                                    new OA\Property(
                                        property: 'email',
                                        type: 'string',
                                        example: 'jan@example.com'
                                    ),
                                    new OA\Property(
                                        property: 'message',
                                        type: 'string',
                                        example: 'Treść wiadomości'
                                    ),
                                    new OA\Property(
                                        property: 'createdAt',
                                        type: 'string',
                                        format: 'date-time',
                                        example: '2025-12-17T10:30:00+00:00'
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'page', type: 'integer', example: 1),
                                new OA\Property(property: 'limit', type: 'integer', example: 20),
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                                new OA\Property(property: 'pages', type: 'integer', example: 3),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', '1'));
        $limit = (int) $request->query->get('limit', '20');
        $limit = max(1, min($limit, 100));

        $items = $this->contactMessageRepository->findPageOrderedByDate($page, $limit);
        $total = $this->contactMessageRepository->countAll();
        $pages = max(1, (int) ceil($total / $limit));

        return $this->json([
            'items' => $items,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $pages,
            ],
        ]);
    }
}
