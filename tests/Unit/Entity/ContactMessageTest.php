<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ContactMessage;
use PHPUnit\Framework\TestCase;

final class ContactMessageTest extends TestCase
{
    public function testConstructorSetsCreatedAt(): void
    {
        $before = new \DateTimeImmutable();
        $entity = new ContactMessage();
        $after = new \DateTimeImmutable();

        $createdAt = $entity->getCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $createdAt);
        $this->assertGreaterThanOrEqual($before, $createdAt);
        $this->assertLessThanOrEqual($after, $createdAt);
    }

    public function testIdIsNullByDefault(): void
    {
        $entity = new ContactMessage();

        $this->assertNull($entity->getId());
    }

    public function testSetAndGetFullName(): void
    {
        $entity = new ContactMessage();

        $result = $entity->setFullName('Jan Kowalski');

        $this->assertSame($entity, $result);
        $this->assertSame('Jan Kowalski', $entity->getFullName());
    }

    public function testSetAndGetEmail(): void
    {
        $entity = new ContactMessage();

        $result = $entity->setEmail('jan@example.com');

        $this->assertSame($entity, $result);
        $this->assertSame('jan@example.com', $entity->getEmail());
    }

    public function testSetAndGetMessage(): void
    {
        $entity = new ContactMessage();

        $result = $entity->setMessage('Test message content');

        $this->assertSame($entity, $result);
        $this->assertSame('Test message content', $entity->getMessage());
    }

    public function testSetAndGetConsent(): void
    {
        $entity = new ContactMessage();

        $result = $entity->setConsent(true);

        $this->assertSame($entity, $result);
        $this->assertTrue($entity->isConsent());

        $entity->setConsent(false);
        $this->assertFalse($entity->isConsent());
    }
}
