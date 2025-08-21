<?php

namespace App\Service\Round;

use App\Enum\Round\TimelineKeys;
use DateTimeInterface;

class RoundTimelineService
{
    public static function sortStatsIntoTimeline(array $stats): array
    {
        $timeline = [];
        //Add server init to timeline
        // $timeline[] = new TimelineEntry(
        //     timestamp: $stats['round']->getInit(),
        //     key: TimelineKeys::SERVER_INIT,
        //     string: sprintf(
        //         'Round %s initialized at %s',
        //         $stats['round']->getId(),
        //         $stats['round']->getInit()->format('Y-m-d H:i:s')
        //     )
        // );

        // if ($stats['round']->getStart()) {
        //     $timeline[] = new TimelineEntry(
        //         timestamp: $stats['round']->getStart(),
        //         key: TimelineKeys::ROUND_START,
        //         string: sprintf(
        //             'Round %s starts at %s',
        //             $stats['round']->getId(),
        //             $stats['round']->getStart()->format('Y-m-d H:i:s')
        //         )
        //     );
        // }

        // if ($stats['round']->getEnd()) {
        //     $timeline[] = new TimelineEntry(
        //         timestamp: $stats['round']->getEnd(),
        //         key: TimelineKeys::ROUND_END,
        //         string: sprintf(
        //             'Round %s ends at %s',
        //             $stats['round']->getId(),
        //             $stats['round']->getEnd()->format('Y-m-d H:i:s')
        //         )
        //     );
        // }
        // if ($stats['round']->getShutdown()) {
        //     $timeline[] = new TimelineEntry(
        //         timestamp: $stats['round']->getShutdown(),
        //         key: TimelineKeys::SERVER_SHUTDOWN,
        //         string: sprintf(
        //             'Round %s finished shutting down at %s',
        //             $stats['round']->getId(),
        //             $stats['round']->getShutdown()->format('Y-m-d H:i:s')
        //         )
        //     );
        // }

        if (isset($stats['explosion'])) {
            foreach ($stats['explosion']->getData() as $e) {
                $timeline[] = new TimelineEntry(
                    timestamp: $e->time,
                    key: TimelineKeys::EXPLOSION,
                    string: sprintf(
                        'Explosion with size (%s, %s, %s, %s, %s) at %s (%s, %s, %s)',
                        $e->dev,
                        $e->heavy,
                        $e->light,
                        $e->flame,
                        $e->flash,
                        $e->area,
                        $e->x,
                        $e->y,
                        $e->z
                    )
                );
            }
        }
        if (isset($stats['manifest'])) {
            foreach ($stats['manifest'] as $m) {
                $timeline[] = new TimelineEntry(
                    timestamp: $m->timestamp,
                    key: TimelineKeys::MANIFEST,
                    string: sprintf('%s joins as %s', $m->ckey, $m->character),
                    metadata: $m
                );
            }
        }
        usort($timeline, function ($a, $b) {
            $ad = $a->timestamp;
            $bd = $b->timestamp;

            if ($ad == $bd) {
                return 0;
            }

            return $ad < $bd ? -1 : 1;
        });
        return $timeline;
    }
}

class TimelineEntry
{
    public function __construct(
        public DateTimeInterface $timestamp,
        public TimelineKeys $key,
        public string $string,
        public ?object $metadata = null
    ) {}
}
