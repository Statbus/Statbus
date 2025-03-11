<?php

namespace App\Service\Round;

use App\Enum\Round\TimelineKeys;

class RoundTimelineService
{

    public static function sortStatsIntoTimeline(array $stats): array
    {
        $timeline = [];
        if (isset($stats['explosion'])) {
            foreach ($stats['explosion']->getData() as $e) {
                $timeline[] = [
                    'timestamp' => $e->time,
                    'key' => TimelineKeys::EXPLOSION,
                    'string' => sprintf(
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
                ];
            }
        }
        usort($timeline, function ($a, $b) {
            $ad = $a['timestamp'];
            $bd = $b['timestamp'];

            if ($ad == $bd) {
                return 0;
            }

            return $ad < $bd ? -1 : 1;
        });
        return $timeline;
    }
}
