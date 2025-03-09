<?php

namespace App\Provider;

use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class TgStation extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const HOST = 'https://forums.tgstation13.org/app.php/tgapi';

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
        return ['user', 'user.linked_accounts'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new Exception("Invalid response");
        } elseif (isset($data['error'])) {
            var_dump($data);
            die();
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new TgStationResouceOwner($response);
    }
}
