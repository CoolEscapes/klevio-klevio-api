<?php

namespace Klevio\KlevioApi;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lcobucci\JWT\Configuration as JwtConfig;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;

class KlevioApi
{
    protected string $clientId;
    protected string $apiKey;
    protected string $privateKey;
    protected string $publicKey;
    protected string $baseUrl;
    protected int $timeout;
    protected JwtConfig $jwtConfig;

    public function __construct()
    {
        $this->clientId = Config::get('klevio-api.client_id');
        $this->apiKey = Config::get('klevio-api.api_key');
        $this->privateKey = str_replace('\n', "\n", Config::get('klevio-api.private_key'));
        $this->publicKey = str_replace('\n', "\n", Config::get('klevio-api.public_key'));
        $this->baseUrl = Config::get('klevio-api.base_url');
        $this->timeout = Config::get('klevio-api.timeout', 30);

        $this->jwtConfig = JwtConfig::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->privateKey),
            InMemory::plainText($this->publicKey)
        );
    }

    /**
     * Grant key access to a user
     *
     * @param string $propertyId
     * @param string $email
     * @param string $from
     * @param string $to
     * @param array $metadata Optional metadata for the key grant
     * @return array
     */
    public function grantKey(string $propertyId, string $email, string $from, string $to, array $metadata = []): array
    {
        return $this->makeRpcCall('grantKey', [
            'source' => [
                '$type' => 'property',
                'id' => $propertyId,
            ],
            'user' => [
                '$type' => 'user',
                'email' => $email,
                'meta' => $metadata,
            ],
            'validity' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    /**
     * Get all keys for a property
     *
     * @param string $propertyId
     * @return array
     */
    public function getKeys(string $propertyId): array
    {
        return $this->makeRpcCall('getKeys', [
            'source' => [
                '$type' => 'property',
                'id' => $propertyId,
            ],
        ]);
    }

    /**
     * Use a key (lock/unlock)
     *
     * @param string $keyId
     * @return array
     */
    public function useKey(string $keyId): array
    {
        return $this->makeRpcCall('useKey', [
            'key' => $keyId,
        ]);
    }

    /**
     * Make an RPC call to the Klevio API
     *
     * @param string $method
     * @param array $params
     * @return array
     */
    protected function makeRpcCall(string $method, array $params = []): array
    {
        $rpcPayload = [
            'id' => uniqid(),
            'method' => $method,
            'params' => $params,
        ];

        $jwt = $this->generateJwt($rpcPayload);

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'X-KeyID' => $this->apiKey,
                'Content-Type' => 'application/jwt',
            ])
            ->post($this->baseUrl . '/rpc', $jwt);

        $response->throw();

        return $this->decodeJwtResponse($response->body());
    }

    /**
     * Generate a JWT token for API authentication
     *
     * @param array $rpcPayload
     * @return string
     */
    protected function generateJwt(array $rpcPayload): string
    {
        $now = new DateTimeImmutable();

        return $this->jwtConfig->builder()
            ->issuedBy($this->clientId)
            ->permittedFor(Config::get('klevio-api.jwt_audience'))
            ->issuedAt($now)
            ->expiresAt($now->modify('+' . Config::get('klevio-api.jwt_lifetime') . ' seconds'))
            ->withHeader('kid', $this->apiKey)
            ->withClaim('rpc', $rpcPayload)
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
            ->toString();
    }

    /**
     * Decode a JWT response from the API
     *
     * @param string $jwtResponse
     * @return array
     */
    protected function decodeJwtResponse(string $jwtResponse): array
    {
        $token = $this->jwtConfig->parser()->parse($jwtResponse);
        return $token->claims()->get('rpc');
    }
}
