<?php


function addressIndicator($state)
{
    switch ($state) {
        case 'added':
            return \app\classes\Html::tag('span', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . ' ';
        case 'ok':
            return \app\classes\Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok text-success']) . ' ';
        default:
            return ' (' . $state . ') ';
    }
}

function fioInd($f, $strLen = null, $showLen = null)
{
    $f = trim($f);
    if (!$f) {
        return \app\classes\Html::tag('span', '&nbsp;?&nbsp;', ['style' => ['color' => 'white', 'background-color' => 'red']]);
    }

    if ($strLen && mb_strlen($f) != $strLen) {
        if (mb_strlen($f) > $strLen) {
            $len = 1;
        } else {
            $len = $strLen - mb_strlen($f);
        }
        return \app\classes\Html::tag('span', showLen($f . str_repeat('?', $len), $showLen), ['style' => ['color' => 'black', 'background-color' => 'yellow']]);
    }

    return showLen($f, $showLen);
}

function showLen($str, $showLen)
{
    if (!$showLen) {
        return $str;
    }

    $m = [];
    $p = '/^(.{'.$showLen.'})(.*)([^?]{'.$showLen.'}\?*)$/';

    if (preg_match($p, $str, $m)) {
        return $m[1] . str_repeat('.', mb_strlen($m[2])) . $m[3];
    }

    return $str;
}