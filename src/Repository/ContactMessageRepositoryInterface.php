<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ContactMessage;

interface ContactMessageRepositoryInterface
{
    public function save(ContactMessage $entity, bool $flush = false): void;

    /**
     * @return array<ContactMessage>
     */
    public function findPageOrderedByDate(int $page, int $limit): array;

    public function countAll(): int;
}
