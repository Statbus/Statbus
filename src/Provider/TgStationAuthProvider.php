<?php

namespace App\Provider;

use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class TgStationAuthProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const HOST = 'https://auth.tgstation13.org/application/o/';

    public function getBaseAuthorizationUrl(): string
    {
        return self::HOST . 'authorize/';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::HOST . 'token/';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return self::HOST . 'userinfo/';
    }

    protected function getDefaultScopes(): array
    {
        return ['openid email profile tgforum ckey'];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw TgStationProviderException::clientException($response, $data);
        }
    }

    protected function createResourceOwner(
        array $response,
        AccessToken $token
    ): ResourceOwnerInterface {
        return new TgStationResouceOwner($response);
    }
}
