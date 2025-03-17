<?php

namespace App\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class TgStationProviderException extends IdentityProviderException
{
    public static function clientException(ResponseInterface $response, $data)
    {
        return static::fromResponse(
            $response,
            $data['message'] ?? json_encode($data)
        );
    }
    protected static function fromResponse(ResponseInterface $response, $message = null)
    {
        return new static($message, $response->getStatusCode(), (string) $response->getBody());
    }
}
