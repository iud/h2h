<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application;

use App\Application\ContactMessageCreator;
use App\DTO\ContactFormRequest;
use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ContactMessageCreatorTest extends TestCase
{
    private ContactMessageCreator $creator;
    private ContactMessageRepositoryInterface&MockObject $contactMessageRepositoryMock;

    protected function setUp(): void
    {
        $this->contactMessageRepositoryMock = $this->createMock(ContactMessageRepositoryInterface::class);
        $this->creator = new ContactMessageCreator($this->contactMessageRepositoryMock);
    }

    public function testCreateMapsAllFieldsFromDtoToEntity(): void
    {
        $dto = new ContactFormRequest();
        $dto->fullName = 'Jan Kowalski';
        $dto->email = 'jan@example.com';
        $dto->message = 'Treść wiadomości testowej';
        $dto->consent = true;

        $this->contactMessageRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(fn(ContactMessage $entity): bool =>
                    $entity->getFullName() === 'Jan Kowalski' &&
                    $entity->getEmail() === 'jan@example.com' &&
                    $entity->getMessage() === 'Treść wiadomości testowej' &&
                    $entity->isConsent() === true),
                true
            );

        $result = $this->creator->create($dto);

        $this->assertInstanceOf(ContactMessage::class, $result);
        $this->assertSame('Jan Kowalski', $result->getFullName());
        $this->assertSame('jan@example.com', $result->getEmail());
        $this->assertSame('Treść wiadomości testowej', $result->getMessage());
        $this->assertTrue($result->isConsent());
    }

    public function testCreateSetsCreatedAtAutomatically(): void
    {
        $dto = new ContactFormRequest();
        $dto->fullName = 'Test User';
        $dto->email = 'test@example.com';
        $dto->message = 'Test message content';
        $dto->consent = true;

        $this->contactMessageRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(ContactMessage::class), true);

        $result = $this->creator->create($dto);

        $this->assertInstanceOf(\DateTimeImmutable::class, $result->getCreatedAt());
    }

    public function testCreateCallsRepositorySaveWithFlush(): void
    {
        $dto = new ContactFormRequest();
        $dto->fullName = 'Test User';
        $dto->email = 'test@example.com';
        $dto->message = 'Test message content';
        $dto->consent = true;

        $this->contactMessageRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(ContactMessage::class), true);

        $this->creator->create($dto);
    }

    public function testCreateConvertsNullConsentToFalse(): void
    {
        $dto = new ContactFormRequest();
        $dto->fullName = 'Test User';
        $dto->email = 'test@example.com';
        $dto->message = 'Test message content';
        $dto->consent = null;

        $this->contactMessageRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(fn(ContactMessage $entity): bool => $entity->isConsent() === false),
                true
            );

        $result = $this->creator->create($dto);

        $this->assertFalse($result->isConsent());
    }
}
