<?php

namespace Klevio\KlevioApi\Tests;

use DateTimeImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Klevio\KlevioApi\Facades\KlevioApi;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class KlevioApiTest extends TestCase
{
    protected Configuration $jwtConfig;
    protected string $testPropertyId = 'test-property-123';
    protected string $testKeyId = 'test-key-123';
    protected string $testEmail = 'test@example.com';

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtConfig = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->getTestPrivateKey()),
            InMemory::plainText($this->getTestPublicKey())
        );

        // Prevent real HTTP requests
        Http::preventStrayRequests();
    }

    /** @test */
    public function it_can_grant_key_access_with_metadata()
    {
        $from = '2024-01-01T00:00:00Z';
        $to = '2024-01-07T23:59:59Z';
        $metadata = [
            'reservationId' => 'test-reservation-123',
            'guestName' => 'John Doe',
            'roomNumber' => '101'
        ];

        // Mock the API response
        Http::fake([
            'https://api.klevio.test/v2/rpc' => Http::response(
                $this->createJwtResponse([
                    'id' => '1',
                    'result' => ['keyId' => $this->testKeyId],
                ])
            ),
        ]);

        $response = KlevioApi::grantKey(
            $this->testPropertyId,
            $this->testEmail,
            $from,
            $to,
            $metadata
        );

        // Assert the response
        $this->assertEquals($this->testKeyId, $response['result']['keyId']);

        // Assert the request was made correctly
        Http::assertSent(function (Request $request) use ($from, $to, $metadata) {
            $token = $this->decodeJwtRequest($request);
            $rpc = $token->claims()->get('rpc');

            return $request->url() === 'https://api.klevio.test/v2/rpc'
                && $request->header('Content-Type')[0] === 'application/jwt'
                && $request->header('X-KeyID')[0] === 'test-api-key'
                && $rpc['method'] === 'grantKey'
                && $rpc['params']['source']['$type'] === 'property'
                && $rpc['params']['source']['id'] === $this->testPropertyId
                && $rpc['params']['user']['$type'] === 'user'
                && $rpc['params']['user']['email'] === $this->testEmail
                && $rpc['params']['user']['meta'] === $metadata
                && $rpc['params']['validity']['from'] === $from
                && $rpc['params']['validity']['to'] === $to;
        });
    }

    /** @test */
    public function it_can_grant_key_access_without_metadata()
    {
        $from = '2024-01-01T00:00:00Z';
        $to = '2024-01-07T23:59:59Z';

        Http::fake([
            'https://api.klevio.test/v2/rpc' => Http::response(
                $this->createJwtResponse([
                    'id' => '1',
                    'result' => ['keyId' => $this->testKeyId],
                ])
            ),
        ]);

        $response = KlevioApi::grantKey(
            $this->testPropertyId,
            $this->testEmail,
            $from,
            $to
        );

        $this->assertEquals($this->testKeyId, $response['result']['keyId']);

        Http::assertSent(function (Request $request) {
            $token = $this->decodeJwtRequest($request);
            $rpc = $token->claims()->get('rpc');

            return $rpc['method'] === 'grantKey'
                && $rpc['params']['user']['meta'] === [];
        });
    }

    /** @test */
    public function it_can_get_keys()
    {
        // Mock the API response
        Http::fake([
            'https://api.klevio.test/v2/rpc' => Http::response(
                $this->createJwtResponse([
                    'id' => '1',
                    'result' => [
                        'keys' => [
                            ['id' => $this->testKeyId],
                        ],
                    ],
                ])
            ),
        ]);

        $response = KlevioApi::getKeys($this->testPropertyId);

        // Assert the response
        $this->assertCount(1, $response['result']['keys']);
        $this->assertEquals($this->testKeyId, $response['result']['keys'][0]['id']);

        // Assert the request was made correctly
        Http::assertSent(function (Request $request) {
            $token = $this->decodeJwtRequest($request);
            $rpc = $token->claims()->get('rpc');

            return $request->url() === 'https://api.klevio.test/v2/rpc'
                && $rpc['method'] === 'getKeys'
                && $rpc['params']['source']['$type'] === 'property'
                && $rpc['params']['source']['id'] === $this->testPropertyId;
        });
    }

    /** @test */
    public function it_can_use_key()
    {
        // Mock the API response
        Http::fake([
            'https://api.klevio.test/v2/rpc' => Http::response(
                $this->createJwtResponse([
                    'id' => '1',
                    'result' => ['success' => true],
                ])
            ),
        ]);

        $response = KlevioApi::useKey($this->testKeyId);

        // Assert the response
        $this->assertTrue($response['result']['success']);

        // Assert the request was made correctly
        Http::assertSent(function (Request $request) {
            $token = $this->decodeJwtRequest($request);
            $rpc = $token->claims()->get('rpc');

            return $request->url() === 'https://api.klevio.test/v2/rpc'
                && $rpc['method'] === 'useKey'
                && $rpc['params']['key'] === $this->testKeyId;
        });
    }

    /** @test */
    public function it_throws_exception_on_api_error()
    {
        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        Http::fake([
            'https://api.klevio.test/v2/rpc' => Http::response([
                'error' => 'Invalid request',
            ], 400),
        ]);

        KlevioApi::useKey($this->testKeyId);
    }

    /** @test */
    public function it_includes_correct_jwt_claims()
    {
        Http::fake([
            'https://api.klevio.test/v2/rpc' => Http::response(
                $this->createJwtResponse(['result' => ['success' => true]])
            ),
        ]);

        KlevioApi::useKey($this->testKeyId);

        Http::assertSent(function (Request $request) {
            $token = $this->decodeJwtRequest($request);

            return $token->headers()->get('kid') === 'test-api-key'
                && $token->claims()->get('iss') === 'test-client-id'
                && $token->claims()->get('aud') === 'klevio-api/v2'
                && $token->claims()->get('iat') instanceof DateTimeImmutable
                && $token->claims()->get('exp') instanceof DateTimeImmutable;
        });
    }

    protected function createJwtResponse(array $rpcPayload): string
    {
        return $this->jwtConfig->builder()
            ->withClaim('rpc', $rpcPayload)
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
            ->toString();
    }

    protected function decodeJwtRequest(Request $request): \Lcobucci\JWT\UnencryptedToken
    {
        return $this->jwtConfig->parser()->parse($request->body());
    }
}
