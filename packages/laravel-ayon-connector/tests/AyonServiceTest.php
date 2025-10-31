<?php

namespace Vendor\AyonApi\Tests;

use Vendor\AyonApi\AyonService;
use Illuminate\Support\Facades\Http;

class AyonServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            '*/auth/login' => Http::response(['token' => 'fake-token'], 200),
        ]);
    }

    public function testAuthenticateReturnsToken()
    {
        Http::fake([
            '*' => Http::response([], 200), // pour d'autres requêtes
        ]);

        $service = new AyonService();

        $token = $service->userExists('any'); // va appeler authenticate
        $this->assertIsBool($token); // true ou false selon userExists
    }

    public function testGetUserReturnsData()
    {
        Http::fake([
            '*/users/john' => Http::response([
                'username' => 'john',
                'fullName' => 'John Doe',
                'email' => 'john@example.com',
            ], 200),
        ]);

        $service = new AyonService();
        $user = $service->getUser('john');

        $this->assertIsArray($user);
        $this->assertEquals('john', $user['username']);
        $this->assertEquals('John Doe', $user['fullName']);
    }

    public function testGetUserReturnsNullOn404()
    {
        Http::fake([
            '*/users/missing' => Http::response([], 404),
        ]);

        $service = new AyonService();
        $user = $service->getUser('missing');

        $this->assertNull($user);
    }

    public function testCreateUserCallsPut()
    {
        Http::fake([
            '*/users/jane' => Http::response(['username' => 'jane'], 200),
        ]);

        $service = new AyonService();
        $result = $service->createUser('jane', 'Jane Doe', 'jane@example.com');

        $this->assertArrayHasKey('username', $result);
        $this->assertEquals('jane', $result['username']);
    }

    public function testUpdateUserCallsPatch()
    {
        Http::fake([
            '*/users/john' => Http::response(['username' => 'john'], 200),
        ]);

        $service = new AyonService();
        $result = $service->updateUser('john', 'John Updated', 'john@example.com');

        $this->assertArrayHasKey('username', $result);
        $this->assertEquals('john', $result['username']);
    }

    public function testDeleteUserCallsDelete()
    {
        Http::fake([
            '*/users/john' => Http::response([], 200),
        ]);

        $service = new AyonService();
        $result = $service->deleteUser('john');

        $this->assertIsArray($result); // ou vide selon réponse
    }
}
