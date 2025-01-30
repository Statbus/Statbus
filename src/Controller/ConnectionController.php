<?php

namespace App\Controller;

use App\Repository\ConnectionRepository;
use App\Service\ServerInformationService;
use IPTools\IP;
use IPTools\Network;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_BAN')]
#[Route('/connections')]
class ConnectionController extends AbstractController
{

    public function __construct(
        private ConnectionRepository $connectionRepository,
        private ServerInformationService $serverInformationService
    ) {}

    #[Route('', name: 'connections')]
    public function index(Request $request): Response
    {
        $ip = $request->get('ip', null);
        if ($ip === "") {
            $ip = null;
        }
        if ($ip) {
            if (str_contains($ip, '/')) {
                $ip = Network::parse($ip);
            } else {
                $ip = IP::parse($ip);
            }
        }
        $cid = $request->get('cid');
        $ckey = $request->get('ckey');
        if ($ckey === "") {
            $ckey = null;
        }

        if ($cid === "") {
            $cid = null;
        }
        $connections = null;
        if ($ckey || $cid || $ip) {
            $connections = $this->connectionRepository->findConnections(
                ckey: $ckey,
                ip: $ip,
                cid: $cid
            );
        }
        $ckeys = [];
        $cids = [];
        $ips = [];
        if ($connections) {
            foreach ($connections as &$c) {
                $c['ip'] = IP::parseLong($c['ip'])->__toString();
                $ckeys[] = $c['ckey'];
                $cids[] = $c['computerid'];
                $ips[] = $c['ip'];
                $c['server'] = $this->serverInformationService->getServerFromPort($c['server_port']);
            }
            $ckeys = array_flip($ckeys);
            $cids = array_flip($cids);
            $ips = array_flip($ips);
            foreach ($connections as &$c) {
                $ckeys[$c['ckey']] += $c['count'];
                $cids[$c['computerid']] += $c['count'];
                $ips[$c['ip']] += $c['count'];
            }
        }
        return $this->render('connection/index.html.twig', [
            'connections' => $connections,
            'ckeys' => array_filter(array_unique($ckeys)),
            'cids' => array_filter(array_unique($cids)),
            'ips' => array_filter(array_unique($ips)),
            'ckey' => $ckey,
            'cid' => $cid,
            'ip' => $ip?->__toString(),
            'query' => $this->connectionRepository->getQuery(),
        ]);
    }
}
