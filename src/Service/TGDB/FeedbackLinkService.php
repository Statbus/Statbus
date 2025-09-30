<?php

namespace App\Service\TGDB;

use App\Enum\ExternalAction\Type;
use App\Repository\ExternalActivityRepository;
use App\Repository\PlayerRepository;
use App\Security\User;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

class FeedbackLinkService
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private ExternalActivityRepository $externalActivityRepository,
        private RequestStack $requestStack,
        private readonly string $feedbackUri
    ) {}

    public function setFeedbackLink(string $uri, User $user): void
    {
        if ($this->validateUri($uri)) {
            throw new Exception('Invalid URL given', 401);
        }
        $parts = parse_url($uri);
        parse_str($parts['query'], $query);
        if (empty($query['t'])) {
            throw new Exception('Invalid URL given.');
        }
        $threadId = $query['t'];
        $queryString = http_build_query([
            't' => $threadId
        ]);
        $uri = sprintf('%s?%s', $this->feedbackUri, $queryString);
        $this->playerRepository->updateFeedbackLink($uri, $user);
        $this->externalActivityRepository->logExternalAction(
            user: $user,
            type: Type::FBL,
            text: "Updated their feedback link to $uri",
            ip: $this->requestStack->getCurrentRequest()->getClientIp()
        );
        return;
    }

    public function getValidUri(): string
    {
        return $this->feedbackUri;
    }

    private function validateUri(string $uri): bool
    {
        $validateParts = parse_url($this->feedbackUri);
        $givenParts = parse_url($uri);
        return (
            $givenParts['host'] !== $validateParts['host'] ||
            $givenParts['path'] !== $validateParts['path']
        );
    }
}
