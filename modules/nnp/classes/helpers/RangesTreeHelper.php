<?php

namespace app\modules\nnp\classes\helpers;

/**
 * Minimal overlap scanner that works on fully collected, sorted ranges.
 *
 * Segments must be numeric arrays in the form: [0 => from, 1 => to, 2 => line].
 */
class RangesTreeHelper
{
    /**
     * Sort segments by start and trigger a callback for every overlap found.
     */
    public static function scanOverlaps(array $segments, callable $onOverlap): void
    {
        $count = count($segments);
        if ($count < 2) {
            return;
        }

        usort($segments, static function (array $a, array $b): int {
            return $a[0] <=> $b[0] ?: $a[1] <=> $b[1];
        });

        $prev = $segments[0];
        for ($i = 1; $i < $count; $i++) {
            $curr = $segments[$i];

            if ($curr[0] <= $prev[1]) {
                $onOverlap($curr, $prev);

                if ($curr[1] > $prev[1]) {
                    $prev[1] = $curr[1];
                }
            } else {
                $prev = $curr;
            }
        }
    }
}