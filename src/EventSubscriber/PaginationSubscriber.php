<?php

namespace App\EventSubscriber;

use Knp\Component\Pager\Event\BeforeEvent;
use Knp\Component\Pager\Event\ItemsEvent;
use Knp\Component\Pager\Event\PaginationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaginationSubscriber implements EventSubscriberInterface
{
    public function onKnpPagerBefore(BeforeEvent $event): void
    {
        // ...
        dump($event);
    }

    public function onKnpPagerPagination(PaginationEvent $event): void
    {
        // ...
        dump($event);
    }

    public function onKnpPagerItems(ItemsEvent $event): void
    {
        // ...
        dump($event->target);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // 'knp_pager.before' => 'onKnpPagerBefore',
            // 'knp_pager.pagination' => 'onKnpPagerPagination',
            'knp_pager.items' => 'onKnpPagerItems'
        ];
    }
}
