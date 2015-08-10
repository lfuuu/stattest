<?php

class BalanceSimple
{
    /* 
       $params = array(
           "client_id" => $fixclient_data["id"],
           "client_currency" => $fixclient_data["currency"],
           "is_multy" => $isMulty,
           "is_view_canceled" => $isViewCanceled,
           "get_sum" => $get_sum
       );
     */
    public static function get($params)
    {
        global $db;

        $sum = array(
            'USD'=>array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            ),
            'RUB'=>array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            )
        );

        $r=$db->GetRow('
            select
                *
            from
                newsaldo
            where
                client_id='.$params['client_id'].'
            and
                currency="'.$params['client_currency'].'"
            and
                is_history=0
            order by
                id desc
            limit 1
        ');
        if($r){
            $sum[$params['client_currency']]
                =
            array(
                'delta'=>0,
                'bill'=>$r['saldo'],
                'ts'=>$r['ts'],
                'saldo'=>$r['saldo'],
                'last_saldo'=>$r['saldo'],
                'last_saldo_ts'=>$r['ts'],
            );
        }else{
            $sum[$params['client_currency']]
                =
            array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            );
        }

        if(!isset($params["is_multy"])) $params["is_multy"] = false;
        if(!isset($params["is_view_canceled"])) $params["is_view_canceled"] = true;

        $sqlLimit = $params["is_multy"] ? " limit 1000" : "";

        $R1 = $db->AllRecords($q='
            select
                *,
                '.(
                    $sum[$params['client_currency']]['ts']
                        ?    'IF(bill_date >= "'.$sum[$params['client_currency']]['ts'].'",1,0)'
                        :    '1'
                ).' as in_sum
            from
                newbills
            '.($params["is_multy"] && !$params["is_view_canceled"] ? "
                left join tt_troubles t using (bill_no)
                left join tt_stages ts on (ts.stage_id = t. cur_stage_id)
                " : "").'
            where
                client_id='.$params['client_id'].'
                '.($params["is_multy"] && !$params["is_view_canceled"] ? " and (state_id is null or (state_id is not null and state_id !=21)) " : "").'
            order by
                bill_date desc,
                bill_no desc
            '.$sqlLimit.'
        ','',MYSQL_ASSOC);


        $R2 = $db->AllRecords('
            select
                P.*,
                U.user as user_name,
                '.(
                    $sum[$params['client_currency']]['ts']
                        ?    'IF(P.payment_date>="'.$sum[$params['client_currency']]['ts'].'",1,0)'
                        :    '1'
                ).' as in_sum
            from
                newpayments as P
            LEFT JOIN
                user_users as U
            on
                U.id=P.add_user
            where
                P.client_id='.$params['client_id'].'
            order by
                P.payment_date
            desc
                '.$sqlLimit.'
            ',
        '',MYSQL_ASSOC);

        $R=array();
        foreach($R1 as &$r){
            $v=array(
                'bill'=>$r,
                'date'=>$r['bill_date'],
                'pays'=>array(),
                'delta'=>-$r['sum']
            );
            foreach($R2 as $k2=>$r2){
                $r2['bill_vis_no'] = $r2['bill_no'];
                $R2[$k2]['bill_vis_no'] = $r2['bill_no'];
                if(
                    $r['bill_no'] == $r2['bill_no']
                &&
                    (
                        $r2['bill_no'] == $r2['bill_vis_no']
                    )
                ){
                    $r2['divide']=0;
                    $v['pays'][]=$r2;
                    $v['delta']+=$r2['sum'];
                    unset($R2[$k2]);
                }
            }

            foreach($R2 as $k2=>$r2)
                if(
                    $r['bill_no'] == $r2['bill_no']
                &&
                    $r2['bill_no'] != $r2['bill_vis_no']
                ){
                    $d = round(-$v['delta'],2);
                    $R2[$k2]['sum'] = $r2['sum']-$d;
                    $r2['sum'] = $d;
                    $r2['divide'] = 1;
                    $v['pays'][] = $r2;
                    $v['delta'] -= $d;
                }
            $r['v'] = $v;
        }
        unset($r);
        foreach($R1 as $r){
            $v=$r['v'];
            foreach($R2 as $k2=>$r2)
                if(
                    $r['bill_no'] == $r2['bill_vis_no']
                &&
                    $r2['bill_no'] != $r['bill_no']
                ){
                    $r2['divide']=2;
                    $v['pays'][]=$r2;
                    $v['delta']+=round($r2['sum'],2);
                    unset($R2[$k2]);
                }
            if($r['in_sum']){
                $sum[$r['currency']]['bill'] += $r['sum'];
                $sum[$r['currency']]['delta'] -= $v['delta'];
            }
            $R[$r['bill_no']] = $v;
        }
        foreach($R2 as $r2){
            $v = array(
                'date'=>$r2['payment_date'],
                'pays'=>array($r2),
                'delta'=>$r2['sum']
            );
            if($r2['in_sum'])
                $sum[$params['client_currency']]['delta']-=$v['delta'];
            $R[]=$v;
        }

        if(isset($params["get_sum"]) && $params['get_sum']){
            return $sum;
        }

        ## sorting
        $sk = array();
        foreach($R as $bn=>$b){
            if(!isset($sk[$b['date']]))
                $sk[$b['date']] = array();
            $sk[$b['date']][$bn] = 1;
        }
        $buf = array();

        $sw = array();

        krsort($sk);

        foreach($sk as $bn){
            krsort($bn);
            foreach($bn as $billno=>$v)
            {
                $buf[$billno] = $R[$billno];

                $bDate = $R[$billno]["bill"]["bill_date"];

                if($bDate)
                {
                    $sw[$bDate] = $billno;
                }

            }
        }

        $R = $buf;
        
        return array($R, $sum, $sw);
    }
}
