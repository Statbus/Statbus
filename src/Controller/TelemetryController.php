<?php

namespace App\Controller;

use App\Attribute\FeatureEnabled;
use App\Repository\TelemetryRepository;
use IPTools\IP;
use IPTools\Network;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[FeatureEnabled('tgdb.telemetry')]
#[IsGranted('ROLE_BAN')]
#[Route('/telemetry', name: 'telemetry')]
class TelemetryController extends AbstractController
{
    public function __construct(
        private TelemetryRepository $telemetryRepository
    ) {}

    #[Route('', name: '')]
    public function index(Request $request): Response
    {
        $ip = $request->get('ip', null);
        if ($ip === '') {
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
        if ($ckey === '') {
            $ckey = null;
        }

        if ($cid === '') {
            $cid = null;
        }
        $telemetry = null;
        if ($ckey || $cid || $ip) {
            $telemetry = $this->telemetryRepository->findTelemetry(
                ckey: $ckey,
                ip: $ip,
                cid: $cid
            );
        }
        $ckeys = [];
        $cids = [];
        $ips = [];
        if ($telemetry) {
            foreach ($telemetry as &$t) {
                $t['ip'] = IP::parseLong($t['address'])->__toString();
                $ckeys[] = $t['ckey'];
                $cids[] = $t['computer_id'];
                $ips[] = $t['ip'];
            }
        }
        return $this->render('telemetry/index.html.twig', [
            'telemetry' => $telemetry,
            'ckeys' => array_count_values($ckeys),
            'cids' => array_count_values($cids),
            'ips' => array_count_values($ips),
            'ckey' => $ckey,
            'cid' => $cid,
            'ip' => $ip?->__toString()
        ]);
    }
}
