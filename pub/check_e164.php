<?php
    define("PATH_TO_ROOT",'../');
    include PATH_TO_ROOT."conf.php";
    //аутентификация
    $action=get_param_raw('action','default');
    $user->DoAction($action);
    if(!$user->IsAuthorized()){
        echo "Authorize please!";
        exit();
    }

    if(preg_match('/^FREE(\d+)?:(\d{4,7}|short)?/',$_GET['e164'],$m)){
        if(isset($m[2]) && $m[2] != 'short'){
            $ann = "and substring(`vn`.`number` from 1 for ".strlen($m[2]).") = '".$m[2]."'";
            $ann_ = "substring(`e164` from 1 for ".strlen($m[2]).") = '".$m[2]."'";
        }elseif ($m[2] == 'short') {
            $query = " select max(CONVERT(E164,UNSIGNED INTEGER))+1 as number from usage_voip where LENGTH(E164)<6";
            $ret = $db->AllRecords($query);
            if (count($ret) > 0 && $ret[0]['number']) {
                echo $ret[0]['number'];
            } else echo "FAIL";
            exit();
        }else{
            $ann = '';
            $ann_ = '';
        }

        if(isset($m[1]) && is_numeric($m[1])){
            $limit_call = (int)$m[1];
        }else
            $limit_call = 4;

        $actual_from = $_GET['actual_from'];
        $actual_to = $_GET['actual_to'];

        $query = "
            select `vn`.`number`, (select max(actual_to) from usage_voip uv where uv.e164 = vn.number) as actual_to
            from `voip_numbers` `vn`
                where `vn`.`beauty_level` = '0'
                and vn.client_id is null
                and ifnull(`vn`.`nullcalls_last_2_days`,0) <= ".$limit_call."
                ".$ann."

            having date_add(ifnull(actual_to,'2000-01-01'), interval 6 month) <= now()
            order by
                ifnull(actual_to, '2000-01-01') asc , rand()
            limit 1
        ";

        //echo $query;

        $ret = $db->AllRecords($query);
//printdbg($ret);
        if(count($ret) == 1)
            echo $ret[0]['number'];
        else
            echo "FAIL";
        exit();
    }elseif(preg_match('/^isset:(\d+)?/',$_GET['e164'],$m)){
        $e164 = $m[1];
        if(!$e164){
            exit();
        }
        $query = "select number from voip_numbers where number='".($e164)."'";
        $ret = $db->AllRecords($query);
        if(count($ret)>0)
            echo "is";
        exit();
    }

    $number = preg_replace('/[^0-9]+/','',$_GET['e164']);
    if(strlen($number)<4){
        echo "false";
        exit();
    }

    if(strlen($number)>5)
    {
        if(!preg_match("/^7[0-9]+$/", $_GET["e164"]))
        {
            echo "false";
            exit();
        }
    }


    $actual_from = $_GET['actual_from'];
    $actual_to = $_GET['actual_to'];

    $query = "
        SELECT
            *
        FROM
            `usage_voip`
        WHERE
            `E164` = '".$number."'
        AND
            (
                (
                    `actual_to` BETWEEN '".$actual_from."' AND '".$actual_to."'
                and
                    (`actual_to` <> '2029-01-01' or `status` = 'connection')
                )
            or
                (
                    `actual_from` BETWEEN '".$actual_from."' AND '".$actual_to."'
                and
                    (`actual_from` <> '2029-01-01' or `status` = 'connection')
                )
            or
                (
                    '".$actual_from."' BETWEEN `actual_from` AND `actual_to`
                and
                    ('".$actual_from."' <> '2029-01-01' or `status` = 'connection')
                )
            )
    ";

    $res = $db->AllRecords($query);
    if(count($res)>0)
        echo "false";
    else{
        $query = "
            SELECT
                *
            FROM
                `usage_voip`
            WHERE
                `E164` = '".$number."'
            AND
                now() between `actual_from` and `actual_to`
        ";

        $res = $db->AllRecords($query);
        if(count($res)>0){
            echo "true_but";
            //echo "false4";
        }else
            echo "true";
    }
?>
