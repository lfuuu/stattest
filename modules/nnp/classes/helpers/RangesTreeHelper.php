<?php

namespace app\modules\nnp\classes\helpers;

class RangesTreeHelper
{
    public static function insert(
        ?array $node,
        int $from,
        int $to,
        int $line,
        string $ndc,
        string $fromSn,
        string $toSn
    ): array {
        if ($node === null) {
            return [
                'from'     => $from,
                'to'       => $to,
                'ndc'      => $ndc,
                'from_sn'  => $fromSn,
                'to_sn'    => $toSn,
                'line'     => $line,
                'max'      => $to,
                'left'     => null,
                'right'    => null,
            ];
        }

        if ($from < $node['from']) {
            $node['left'] = self::insert($node['left'], $from, $to, $line, $ndc, $fromSn, $toSn);
        } else {
            $node['right'] = self::insert($node['right'], $from, $to, $line, $ndc, $fromSn, $toSn);
        }

        $leftMax  = $node['left']['max']  ?? $node['to'];
        $rightMax = $node['right']['max'] ?? $node['to'];

        $node['max'] = max($node['to'], $leftMax, $rightMax);

        return $node;
    }

    public static function search(?array $node, int $from, int $to, array &$result): void
    {
        if ($node === null) {
            return;
        }

        if ($node['left'] !== null && $node['left']['max'] >= $from) {
            self::search($node['left'], $from, $to, $result);
        }

        if ($node['from'] <= $to && $node['to'] >= $from) {
            $result[] = $node;
        }

        if ($node['right'] !== null && $node['right']['max'] >= $from) {
            self::search($node['right'], $from, $to, $result);
        }
    }
}
