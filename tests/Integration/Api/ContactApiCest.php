<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api;

use App\Entity\ContactMessage;
use App\Tests\Integration\Support\ApiTester;
use Codeception\Util\HttpCode;

final class ContactApiCest
{
    public function createContactMessage(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'To jest testowa wiadomość kontaktowa',
            'consent' => true,
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'To jest testowa wiadomość kontaktowa',
        ]);
        $I->seeResponseJsonMatchesJsonPath('$.id');
        $I->seeResponseJsonMatchesJsonPath('$.createdAt');

        $I->seeInRepository(ContactMessage::class, [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
        ]);
    }

    public function createContactMessageFailsWithInvalidEmail(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            'fullName' => 'Jan Kowalski',
            'email' => 'invalid-email',
            'message' => 'To jest testowa wiadomość kontaktowa',
            'consent' => true,
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function createContactMessageFailsWithoutConsent(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'To jest testowa wiadomość kontaktowa',
            'consent' => false,
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function createContactMessageFailsWithEmptyFullName(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            'fullName' => '',
            'email' => 'jan@example.com',
            'message' => 'To jest testowa wiadomość kontaktowa',
            'consent' => true,
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function createContactMessageFailsWithShortMessage(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'Krótka',
            'consent' => true,
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function createContactMessageFailsWithMissingFullName(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            // fullName pominięte
            'email' => 'jan@example.com',
            'message' => 'To jest testowa wiadomość kontaktowa',
            'consent' => true,
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function createContactMessageFailsWithMissingEmail(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            'fullName' => 'Jan Kowalski',
            // email pominięty
            'message' => 'To jest testowa wiadomość kontaktowa',
            'consent' => true,
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function createContactMessageFailsWithMissingMessage(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            // message pominięte
            'consent' => true,
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function createContactMessageFailsWithMissingConsent(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'To jest testowa wiadomość kontaktowa',
            // consent pominięte
        ]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function createContactMessageFailsWithEmptyRequestBody(ApiTester $I): void
    {
        // Arrange
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Act
        $I->sendPost('/contact', []);

        // Assert
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
    }

    public function listContactMessagesEmpty(ApiTester $I): void
    {
        // Act
        $I->sendGet('/contact');

        // Assert
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'items' => [],
            'meta' => [
                'page' => 1,
                'limit' => 20,
                'total' => 0,
                'pages' => 1,
            ],
        ]);
    }

    public function listContactMessagesWithPagination(ApiTester $I): void
    {
        // Arrange
        for ($i = 1; $i <= 25; $i++) {
            $I->haveInRepository(ContactMessage::class, [
                'fullName' => "User $i",
                'email' => "user$i@example.com",
                'message' => "Message number $i for testing pagination",
                'consent' => true,
                'createdAt' => new \DateTimeImmutable("-$i minutes"),
            ]);
        }

        // Act
        $I->sendGet('/contact', ['page' => 1, 'limit' => 10]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'meta' => [
                'page' => 1,
                'limit' => 10,
                'total' => 25,
                'pages' => 3,
            ],
        ]);

        // Act
        $I->sendGet('/contact', ['page' => 2, 'limit' => 10]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'meta' => [
                'page' => 2,
                'limit' => 10,
                'total' => 25,
                'pages' => 3,
            ],
        ]);
    }

    public function listContactMessagesDefaultPagination(ApiTester $I): void
    {
        // Arrange
        $I->haveInRepository(ContactMessage::class, [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'Test message for list',
            'consent' => true,
            'createdAt' => new \DateTimeImmutable(),
        ]);

        // Act
        $I->sendGet('/contact');

        // Assert
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'meta' => [
                'page' => 1,
                'limit' => 20,
                'total' => 1,
                'pages' => 1,
            ],
        ]);
        $I->seeResponseJsonMatchesJsonPath('$.items[0].id');
        $I->seeResponseJsonMatchesJsonPath('$.items[0].fullName');
        $I->seeResponseJsonMatchesJsonPath('$.items[0].email');
        $I->seeResponseJsonMatchesJsonPath('$.items[0].message');
        $I->seeResponseJsonMatchesJsonPath('$.items[0].createdAt');
    }

    public function listContactMessagesLimitIsCapped(ApiTester $I): void
    {
        // Act
        $I->sendGet('/contact', ['limit' => 500]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'meta' => [
                'limit' => 100,
            ],
        ]);
    }

    public function listContactMessagesPageIsMinimumOne(ApiTester $I): void
    {
        // Act
        $I->sendGet('/contact', ['page' => 0]);

        // Assert
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'meta' => [
                'page' => 1,
            ],
        ]);
    }
}
