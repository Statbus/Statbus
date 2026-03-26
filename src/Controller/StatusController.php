<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/status', name: '')]
final class StatusController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        #[Autowire(env: 'string:ISP_STATUS_API_KEY')]
        private readonly string $unifiKey
    ) {}

    #[Route('', name: '')]
    public function index(): Response
    {
        return $this->render('status/index.html.twig');
    }

    #[Route('/isp', name: '')]
    public function isp(): Response
    {
        $data = $this->cache->get('isp_metrics_5m', function (ItemInterface $item) {
            $item->expiresAfter(300);

            $res = $this->client->request(
                'GET',
                'https://api.ui.com/v1/isp-metrics/5m',
                [
                    'headers' => [
                        'accept' => 'application/json',
                        'X-API-Key' => $this->unifiKey
                    ]
                ]
            );

            return $res->toArray()['data'][0]['periods'];
        });

        return $this->json([
            $data
        ]);
    }
}
