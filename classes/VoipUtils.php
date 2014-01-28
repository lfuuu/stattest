<?php

class VoipUtils {

    public static function explodeNumber($def, $prefixFrom, $prefixTo, Closure $callback)
    {
        while (strlen($prefixFrom) > 0 && $prefixFrom[strlen($prefixFrom) - 1] == '0' && $prefixTo[strlen($prefixTo) - 1] == '9') {
            if (strlen($prefixFrom) > 1) {
                $prefixFrom = substr($prefixFrom, 0, strlen($prefixFrom) - 1);
                $prefixTo = substr($prefixTo, 0, strlen($prefixTo) - 1);
            } else {
                $prefixFrom = '';
                $prefixTo = '';
            }
        }

        if ($prefixFrom == '') {
            $callback($def);
            return;
        }

        if ($prefixFrom[0] == $prefixTo[0]) {
            self::explodeNumber($def . $prefixFrom[0], substr($prefixFrom, 1), substr($prefixTo, 1), $callback);
            return;
        }

        $len = strlen($prefixFrom);

        self::explodeNumber($def . $prefixFrom[0], substr($prefixFrom, 1), str_pad('', $len-1, '9'), $callback);


        for ($n = $prefixFrom[0] + 1; $n <= $prefixTo[0] -1; $n++) {
            $callback($def . $n);
        }

        self::explodeNumber($def . $prefixTo[0], str_pad('', $len-1, '0'), substr($prefixTo, 1), $callback);
    }

    public static function reducePrefixes(array $table, $fPrefix, array $fGroups)
    {
        usort($table, function($a, $b) use ($fPrefix) {
            return strcmp($a[$fPrefix], $b[$fPrefix]);
        });

        $table = self::reducePrefixesPart1($table, $fPrefix, $fGroups);

        $table = self::reducePrefixesPart2($table, $fPrefix, $fGroups);

        return $table;
    }

    private static function reducePrefixesPart1(array $table, $fPrefix, array $fGroups)
    {
        $newTable = array();
        $pre_prefix = '';
        $pre_filter = array();
        foreach($fGroups as $fGroup) {
            $pre_filter[$fGroup] = '';
        }

        $pre_l = 0;
        $m_prefix = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $m_filter = array();
        foreach($fGroups as $fGroup) {
            $m_filter[$fGroup] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        }

        foreach ($table as $v) {
            $prefix = $v[$fPrefix];

            $cur_l = strlen($prefix);
            if ($pre_l <> $cur_l || substr($prefix, 0, $cur_l - 1) <> substr($prefix, 0, $pre_l - 1)) {
                if ($pre_l > $cur_l) $n = $pre_l; else $n = $cur_l;
                while ($n > 0) {
                    if ($m_prefix[$n] == '' || $m_prefix[$n] <> substr($prefix, 0, strlen($m_prefix[$n]))) {
                        $m_prefix[$n] = '';
                        foreach($fGroups as $fGroup) {
                            $m_filter[$fGroup][$n] = '';
                        }
                    }
                    $n = $n - 1;
                }
                $pre_prefix = '';
                foreach($fGroups as $fGroup) {
                    $pre_filter[$fGroup] = '';
                }
                $n = $cur_l - 1;
                while ($n > 0) {
                    if ($pre_prefix === '' && $m_prefix[$n] !== '')
                        $pre_prefix = $m_prefix[$n];
                    foreach($fGroups as $fGroup) {
                        $pre_filter[$fGroup] = '';
                        if ($pre_filter[$fGroup] === '' && $m_filter[$fGroup][$n] !== '')
                            $pre_filter[$fGroup] = $m_filter[$fGroup][$n];
                    }

                    $n = $n - 1;
                }
            }
            $m_prefix[$cur_l] = $prefix;
            foreach($fGroups as $fGroup) {
                $m_filter[$fGroup][$cur_l] = $v[$fGroup];
            }

            $filterNotChanged = true;
            foreach($fGroups as $fGroup) {
                if ($pre_filter[$fGroup] != $v[$fGroup]) {
                    $filterNotChanged = false;
                    break;
                }
            }

            if ($pre_prefix != '' && strpos($prefix, $pre_prefix) === 0 &&
                $filterNotChanged
            ) {
                continue;
            }

            $newTable[] = $v;
        }
        return $newTable;
    }

    private static function reducePrefixesPart2(array $table, $fPrefix, array $fGroups)
    {
        while (true) {
            $needReduce = false;
            $newTable = array();
            $m = array();
            $pre_len = 0;
            $pre_subprefix = '';
            $pre_filter = array();
            foreach($fGroups as $fGroup) {
                $pre_filter[$fGroup] = '';
            }

            foreach ($table as $v) {
                $len = strlen($v[$fPrefix]);
                $subprefix = substr($v[$fPrefix], 0, $len - 1);

                $filterChanged = false;
                foreach($fGroups as $fGroup) {
                    if ($pre_filter[$fGroup] != $v[$fGroup]) {
                        $filterChanged = true;
                        break;
                    }
                }

                if ($len != $pre_len || $subprefix != $pre_subprefix || $filterChanged) {
                    if (count($m) < 10)
                        foreach ($m as $mm) {
                            $newTable[] = $mm;
                        }
                    else {
                        $mm = $m[0];
                        $mm[$fPrefix] = substr($mm[$fPrefix], 0, strlen($mm[$fPrefix]) - 1);
                        $newTable[] = $mm;
                        $needReduce = true;
                    }

                    $m = array($v);
                } else {
                    $m[] = $v;
                }
                $pre_len = $len;
                $pre_subprefix = $subprefix;

                foreach($fGroups as $fGroup) {
                    $pre_filter[$fGroup] = $v[$fGroup];
                }
            }
            if (count($m) < 10)
                foreach ($m as $mm) {
                    $newTable[] = $mm;
                }
            else {
                $mm = $m[0];
                $mm[$fPrefix] = substr($mm[$fPrefix], 0, strlen($mm[$fPrefix]) - 1);
                $newTable[] = $mm;
                $needReduce = true;
            }

            if (!$needReduce) {
                return $newTable;
            }
            $table = $newTable;
        }

    }

}