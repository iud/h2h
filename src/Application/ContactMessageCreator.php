<?php

declare(strict_types=1);

namespace App\Application;

use App\DTO\ContactFormRequest;
use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepositoryInterface;

readonly class ContactMessageCreator
{
    public function __construct(private ContactMessageRepositoryInterface $repository)
    {
    }

    public function create(ContactFormRequest $dto): ContactMessage
    {
        $entity = new ContactMessage();
        $entity->setFullName($dto->fullName);
        $entity->setEmail($dto->email);
        $entity->setMessage($dto->message);
        $entity->setConsent((bool) $dto->consent);

        $this->repository->save($entity, true);

        return $entity;
    }
}
