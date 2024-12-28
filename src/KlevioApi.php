<?php

namespace Klevio\KlevioApi;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class KlevioApi
{
    private $client;
    private $apiUrl;

    public function __construct()
    {
        $this->client = new Client;
        $this->apiUrl = env('KLEVIO_API_URL').'/rpc';
    }

    /**
     * Make an RPC request to the Klevio API
     */
    public function post($method, $params = [])
    {
        $rpcPayload = [
            'id' => uniqid(),
            'method' => $method,
            'params' => $params,
        ];

        $jwt = $this->generateJwt($rpcPayload);

        $response = $this->client->post($this->apiUrl, [
            'headers' => [
                'X-KeyID' => env('KLEVIO_API_KEY'),
                'Content-Type' => 'application/jwt',
            ],
            'body' => $jwt,
        ]);

        $rawResponse = $this->decodeJwtResponse($response->getBody()->getContents());

        Log::info('RPC Response', ['response' => $rawResponse]);

        return $rawResponse;
    }

    /**
     * Generate a JWT token for API authentication
     */
    protected function generateJwt(array $rpcPayload): string
    {
        $privateKey = str_replace('\n', "\n", env('KLEVIO_PRIVATE_KEY'));
        $publicKey = str_replace('\n', "\n", env('KLEVIO_PUBLIC_KEY'));
        
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey),
            InMemory::plainText($publicKey)
        );

        $now = new \DateTimeImmutable;

        return $config->builder()
            ->issuedBy(env('KLEVIO_CLIENT_ID'))
            ->permittedFor('klevio-api/v2')
            ->issuedAt($now)
            ->expiresAt($now->modify('+30 seconds'))
            ->withHeader('kid', env('KLEVIO_API_KEY'))
            ->withClaim('rpc', $rpcPayload)
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }

    /**
     * Decode a JWT response from the API
     */
    protected function decodeJwtResponse($jwtResponse)
    {
        $publicKey = str_replace('\n', "\n", env('KLEVIO_PUBLIC_KEY'));
        $privateKey = str_replace('\n', "\n", env('KLEVIO_PRIVATE_KEY'));

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey),
            InMemory::plainText($publicKey)
        );

        $token = $config->parser()->parse($jwtResponse);
        return $token->claims()->get('rpc');
    }

    /**
     * Grant key access to a user
     * 
     * @param string $propertyId Property ID
     * @param string $email User email
     * @param string $from Start date (ISO 8601 format)
     * @param string $to End date (ISO 8601 format)
     * @param array $metadata Additional metadata for the key
     * @return array
     */
    public function grantKey($propertyId, $email, $from, $to, $metadata = [])
    {
        return $this->post('grantKey', [
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
     */
    public function getKeys($propertyId)
    {
        return $this->post('getKeys', [
            'source' => [
                '$type' => 'property',
                'id' => $propertyId,
            ],
        ]);
    }

    /**
     * Use a key (lock/unlock)
     */
    public function useKey($keyId)
    {
        return $this->post('useKey', [
            'key' => $keyId,
        ]);
    }
}
