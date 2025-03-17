<?php

namespace App\Provider;

use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class TgStation extends AbstractProvider
{
    use BearerAuthorizationTrait;
    public const HOST = 'https://forums.tgstation13.org/app.php/tgapi';
    // public const HOST = 'https://tgstation-phpbb.ddev.site/app.php/tgapi';

    public function getBaseAuthorizationUrl(): string
    {
        return self::HOST . "/oauth/auth";
    }
    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::HOST . "/oauth/token";
    }
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return self::HOST . "/user/me";
    }
    protected function getDefaultScopes(): array
    {
        return ['user.email'];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw TgStationProviderException::clientException($response, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new TgStationResouceOwner($response);
    }
}
