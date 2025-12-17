<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: 'ContactFormRequest',
    description: 'Dane formularza kontaktowego',
    required: ['fullName', 'email', 'message', 'consent']
)]
class ContactFormRequest
{
    #[OA\Property(property: 'fullName', description: 'Imię i nazwisko', type: 'string', example: 'Jan Kowalski')]
    #[Assert\NotBlank(message: 'Imię i nazwisko jest wymagane.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Imię i nazwisko musi mieć co najmniej {{ limit }} znaki.',
        maxMessage: 'Imię i nazwisko nie może mieć więcej niż {{ limit }} znaków.'
    )]
    public ?string $fullName = null;

    #[OA\Property(
        property: 'email',
        description: 'Adres e-mail',
        type: 'string',
        format: 'email',
        example: 'jan@example.com'
    )]
    #[Assert\NotBlank(message: 'Adres e-mail jest wymagany.')]
    #[Assert\Email(message: 'Podany adres e-mail jest nieprawidłowy.')]
    public ?string $email = null;

    #[OA\Property(property: 'message', description: 'Treść wiadomości', type: 'string', example: 'Treść wiadomości')]
    #[Assert\NotBlank(message: 'Treść wiadomości jest wymagana.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Treść wiadomości musi mieć co najmniej {{ limit }} znaków.'
    )]
    public ?string $message = null;

    #[OA\Property(
        property: 'consent',
        description: 'Zgoda na przetwarzanie danych osobowych',
        type: 'boolean',
        example: true
    )]
    #[Assert\NotNull(message: 'Zgoda na przetwarzanie danych osobowych jest wymagana.')]
    #[Assert\IsTrue(message: 'Musisz wyrazić zgodę na przetwarzanie danych osobowych.')]
    public ?bool $consent = null;
}
