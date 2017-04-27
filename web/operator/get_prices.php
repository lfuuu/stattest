<?php

/**
 * Заметки для будующих поколений...
 *
 * $p_dest - обозначение для региона
 *         - $p_dest = 1 => Россия (country == 7)
 *         - $p_dest = 2 => Остальные регионы
 *         - $p_dest = 4 => Россия не Мобильные
 *         - $p_dest = 5 => Остальные регионы Мобильные
 *
 * pricelist_id берутся из
 *  - Телефония / Прайс-листы Клиент Ориг
 *  - Телефония / Прайс-листы Клиент Терм
 *
 * первым берется Базовый МГМН (Междугородний/Международный)
 * вторым берется МГМН 1 (Междугородний/Международный)
 */

use app\helpers\DateTimeZoneHelper;
use app\models\Region;

define("PATH_TO_ROOT", '../../stat/');
include PATH_TO_ROOT . "conf_yii.php";

$conf = [
    '99' => [
        '1' => ['pricelists' => [17, 18, 19]],
        '2' => ['pricelists' => [17, 18, 19]],
        '4' => ['pricelists' => [61, 61, 61], 'mob' => false],
        '5' => ['pricelists' => [39, 42, 43, 45], 'mob' => true],
    ],
    '97' => [
        '1' => ['pricelists' => [27, 165]],
        '2' => ['pricelists' => [27, 165]],
        '4' => ['pricelists' => [38], 'mob' => false],
        '5' => ['pricelists' => [38], 'mob' => true],
    ],
    '98' => [
        '1' => ['pricelists' => [52, 167]],
        '2' => ['pricelists' => [52, 167]],
        '4' => ['pricelists' => [50], 'mob' => false, 'dest' => 1],
        '5' => ['pricelists' => [50], 'mob' => true, 'dest' => 1],
    ],
    '96' => [
        '1' => ['pricelists' => [56, 170]],
        '2' => ['pricelists' => [56, 170]],
        '4' => ['pricelists' => [55], 'mob' => false, 'dest' => 1],
        '5' => ['pricelists' => [55], 'mob' => true, 'dest' => 1],
    ],
    '95' => [
        '1' => ['pricelists' => [66, 168]],
        '2' => ['pricelists' => [66, 168]],
        '4' => ['pricelists' => [65], 'mob' => false],
        '5' => ['pricelists' => [65], 'mob' => true],
    ],
    '94' => [
        '1' => ['pricelists' => [70, 169]],
        '2' => ['pricelists' => [70, 169]],
        '4' => ['pricelists' => [68], 'mob' => false],
        '5' => ['pricelists' => [68], 'mob' => true],
    ],
    '93' => [
        '1' => ['pricelists' => [79, 166]],
        '2' => ['pricelists' => [79, 166]],
        '4' => ['pricelists' => [78], 'mob' => false],
        '5' => ['pricelists' => [78], 'mob' => true],
    ],
    '87' => [
        '1' => ['pricelists' => [73, 171]],
        '2' => ['pricelists' => [73, 171]],
        '4' => ['pricelists' => [72], 'mob' => false],
        '5' => ['pricelists' => [72], 'mob' => true],
    ],
    '89' => [
        '1' => ['pricelists' => [117, 173]],
        '2' => ['pricelists' => [117, 173]],
        '4' => ['pricelists' => [116], 'mob' => false],
        '5' => ['pricelists' => [116], 'mob' => true],
    ],
    '88' => [
        '1' => ['pricelists' => [82, 172]],
        '2' => ['pricelists' => [82, 172]],
        '4' => ['pricelists' => [81], 'mob' => false],
        '5' => ['pricelists' => [81], 'mob' => true],
    ],
    '83' => [// Хабаровск
        '1' => ['pricelists' => [349, 350]],
        '2' => ['pricelists' => [349, 350]],
        '4' => ['pricelists' => [351], 'mob' => false],
        '5' => ['pricelists' => [351], 'mob' => true],
    ],
    '78' => [// Тюмень
        '1' => ['pricelists' => [362, 363]],
        '2' => ['pricelists' => [362, 363]],
        '4' => ['pricelists' => [360], 'mob' => false],
        '5' => ['pricelists' => [360], 'mob' => true],
    ],
    '79' => [// Тула
        '1' => ['pricelists' => [368, 369]],
        '2' => ['pricelists' => [368, 369]],
        '4' => ['pricelists' => [373], 'mob' => false],
        '5' => ['pricelists' => [373], 'mob' => true],
    ],
    '90' => [// Челябинск
        '1' => ['pricelists' => [391, 392]],
        '2' => ['pricelists' => [391, 392]],
    ],
    '92' => [// Пермь
        '1' => ['pricelists' => [326, 327]],
        '2' => ['pricelists' => [326, 327]],
        '4' => ['pricelists' => [325], 'mob' => false],
        '5' => ['pricelists' => [325], 'mob' => true],
    ],
    Region::RYAZAN => [// Рязань
        '1' => ['pricelists' => [488, 489]],
        '2' => ['pricelists' => [488, 489]],
        '4' => ['pricelists' => [486], 'mob' => false],
        '5' => ['pricelists' => [486], 'mob' => true],
    ],

    Region::ASTRAKHAN => [// Астрахань
        '1' => ['pricelists' => [605, 606]],
        '2' => ['pricelists' => [605, 606]],
        '4' => ['pricelists' => [603], 'mob' => false],
        '5' => ['pricelists' => [603], 'mob' => true],
    ]
];

$filter = '';
$report_id = -1;
$params = array();

$p_region = intval($_GET['region']);
$p_dest = intval($_GET['dest']);

if (!isset($conf[$p_region]) || !isset($conf[$p_region][$p_dest])) {
    die('error: incorrect parameters');
}

$count = 1;
foreach ($conf[$p_region][$p_dest]['pricelists'] as $pricelistId) {
    $params[] = [
        'report_id' => $report_id,
        'position' => $count++,
        'pricelist_id' => $pricelistId,
        'param' => 'd1',
        'date' => DateTimeZoneHelper::DATE_FORMAT
    ];
}

if ($p_dest == 1) {
    $filter = " AND g.country=7 ";
} elseif ($p_dest == 2) {
    $filter = " AND g.country<>7 ";
}


if (isset($conf[$p_region][$p_dest]['mob'])) {
    $filter .= 'AND ' . ($conf[$p_region][$p_dest]['mob'] ? '' : 'not ') . 'd.mob';
}

if (isset($conf[$p_region][$p_dest]['dest'])) {
    $filter .= 'AND g.dest=' . $conf[$p_region][$p_dest]['dest'];
}


$pg_db->Query("DELETE FROM voip.analyze_pricelist_report WHERE id='{$report_id}'");
$pg_db->Query("INSERT INTO voip.analyze_pricelist_report(id, generated,ext_params)values('{$report_id}',null, null)");
foreach ($params as $param) {
    $pg_db->QueryInsert('voip.analyze_pricelist_report_params', $param, false);
}

$pg_db->Query("select * from voip.prepare_analyze_pricelist_report('{$report_id}')");
$data = $pg_db->AllRecords("          select d.defcode, r.*,
                                                    g.name as destination, g.zone, d.mob
                                            from voip.analyze_pricelist_report_data r
                                                    LEFT JOIN voip_destinations d ON r.ndef=d.ndef
                      					                    LEFT JOIN geo.geo g ON g.id=d.geo_id
                                                    where r.report_id={$report_id} {$filter}
                                                    order by r.report_id, r.position, r.param, g.name, d.defcode
                                             ");
foreach ($data as $r) {
    if (!isset($report[$r['defcode']])) {
        $report[$r['defcode']] = array(
            'defcode' => $r['defcode'],
            'zone' => $r['zone'],
            'mob' => $r['mob'],
            'destination' => $r['destination'],
            'parts' => array(),
        );
    }

    if (!isset($report[$r['defcode']]['parts'][$r['position']])) {
        $report[$r['defcode']]['parts'][$r['position']] = array('d1' => array(), 'd2' => array());
    }

    $m = explode('-', $r['date_from']);
    if (count($m) == 3) {
        $r['date_from'] = $m[2] . '.' . $m[1] . '.' . $m[0];
    }

    $report[$r['defcode']]['parts'][$r['position']][$r['param']] = $r;
}

$dest = '';
$ismob = '';
$price = '';

foreach ($report as $k => $r) {
    $price = '';
    foreach ($r['parts'] as $part) {
        if (isset($part['d1']['price'])) {
            $price .= $part['d1']['price'];
        }
        if (isset($part['d2']['price'])) {
            $price .= $part['d2']['price'];
        }
    }
    $report[$k]['price'] = $price;
}

function cmp($r1, $r2)
{
    $res = strcmp($r1['destination'], $r2['destination']);
    if ($res != 0) {
        return $res;
    }

    $res = strcmp($r1['price'], $r2['price']);
    if ($res != 0) {
        return $res;
    }

    $res = strcmp($r1['defcode'], $r2['defcode']);
    if ($res != 0) {
        return $res;
    }

    return 0;
}

usort($report, "cmp");

$resgroups = array();
$resgroup = array();
foreach ($report as $r) {

    if ($destination != $r['destination'] ||
        $ismob != $r['mob'] ||
        $price != $r['price']
    ) {
        $destination = $r['destination'];
        $ismob = $r['mob'];
        $price = $r['price'];

        if (count($resgroup) > 0) {
            $resgroups[] = $resgroup;
        }
        $resgroup = $r;
        $resgroup['defs'] = array();
        $resgroup['defcode'] = '';

    }


    $resgroup['defs'][] = $r['defcode'];
}
if (count($resgroup) > 0) {
    $resgroups[] = $resgroup;
}

foreach ($resgroups as $k => $resgroup) {
    while (true) {
        $can_trim = false;
        $first = true;
        $char = '';
        $defs = array();
        foreach ($resgroups[$k]['defs'] as $d) {
            if ($first == true) {
                $can_trim = true;
                $first = false;
                $char = substr($d, 0, 1);
            } else {
                if ($char != substr($d, 0, 1)) {
                    $can_trim = false;
                }
            }
        }

        if ($can_trim == true) {
            foreach ($resgroups[$k]['defs'] as $d) {
                $dd = substr($d, 1);
                if (strlen($dd) > 0) {
                    $defs[] = $dd;
                } else {
                    if (strlen($dd) == 0) {
                        $defs = array();
                        break;
                    }
                }
            }
            $resgroups[$k]['defcode'] = $resgroups[$k]['defcode'] . $char;
            $resgroups[$k]['defs'] = $defs;
        } else {
            break;
        }
    }
}


$data = array();
foreach ($resgroups as $resgroup) {
    $defs = '';
    foreach ($resgroup['defs'] as $d) {
        if ($defs == '') {
            $defs .= $d;
        } else {
            $defs .= ', ' . $d;
        }
    }
    $price1 = isset($resgroup['parts'][1]['d1']['price']) ? str_replace('.', ',',
        $resgroup['parts'][1]['d1']['price']) : '';
    $price2 = isset($resgroup['parts'][2]['d1']['price']) ? str_replace('.', ',',
        $resgroup['parts'][2]['d1']['price']) : '';
    $price3 = isset($resgroup['parts'][3]['d1']['price']) ? str_replace('.', ',',
        $resgroup['parts'][3]['d1']['price']) : '';
    $price4 = isset($resgroup['parts'][4]['d1']['price']) ? str_replace('.', ',',
        $resgroup['parts'][4]['d1']['price']) : '';
    $data[] = array(
        'code1' => $resgroup['defcode'],
        'code2' => $defs,
        'name' => str_replace('"', '""', $resgroup['destination']) . ($resgroup['mob'] == 't' ? ' (моб.)' : ''),
        'zone' => $resgroup['zone'],
        'price1' => $price1,
        'price2' => $price2,
        'price3' => $price3,
        'price4' => $price4
    );
}


header("Content-type: text/plain; charset=UTF-8");
header('Pragma: no-cache');
header('Expires: 0');

if ($p_region == 99) {
    echo '"code1";"code2";"name";"zone";"price1";"price2";"price3";"price4"' . "\n";
    foreach ($data as $r) {
        echo '"' . $r['code1'] . '";"' . $r['code2'] . '";"' . $r['name'] . '";"' . $r['zone'] . '";"' . $r['price1'] . '";"' . $r['price2'] . '";"' . $r['price3'] . '";"' . $r['price4'] . '"' . "\n";
    }
} else {
    echo '"code1";"code2";"name";"zone";"price1";"price2"' . "\n";
    foreach ($data as $r) {
        echo '"' . $r['code1'] . '";"' . $r['code2'] . '";"' . $r['name'] . '";"' . $r['zone'] . '";"' . $r['price1'] . '";"' . $r['price2'] . '"' . "\n";
    }
}
