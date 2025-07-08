<?php
namespace App\Service\Round;

use App\Enum\Round\TimelineKeys;
use DateTimeInterface;

class RoundTimelineService
{

    public static function sortStatsIntoTimeline(array $stats): array
    {
        $timeline = [];
        if (isset($stats['explosion'])) {
            foreach ($stats['explosion']->getData() as $e) {
                $timeline[] = new TimelineEntry(
                    timestamp: $e->timestamp,
                    key: TimelineKeys::EXPLOSION,
                    string: sprintf(
                        "Explosion with size (%s, %s, %s, %s, %s) at %s (%s, %s, %s)",
                        $e->dev,
                        $e->heavy,
                        $e->light,
                        $e->flame,
                        $e->flash,
                        $e->area,
                        $e->x,
                        $e->y,
                        $e->z
                    ),
                );
            }
        }
        if (isset($stats['manifest'])) {
            foreach ($stats['manifest'] as $m) {
                $timeline[] = new TimelineEntry(
                    timestamp: $m->timestamp,
                    key: TimelineKeys::MANIFEST,
                    string: sprintf("%s joins as %s", $m->ckey, $m->character),
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
    ) {

    }
}
