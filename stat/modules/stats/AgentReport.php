<?php
/**
 * Класс предназначен для формирования "Отчета по агентам"
 */
class AgentReport 
{
    /** 
     * Обработка параметров запроса и cоставление главного отчета
     */
    public static function getReport()
    {
        global $db;
        global $design,$fixclient_data;

        $agents = array();
        $agent = false;
        $agent_id = get_param_raw("agent", false);
        $export = get_param_raw("export", false);
        
        $agents = $db->AllRecords('SELECT id, name FROM sale_channels_old WHERE is_agent=1');
        if ($agent_id && $agent_id > 0) $agent = $db->GetRow('SELECT * FROM sale_channels_old WHERE id=' . $agent_id);
        
        $interests_types = array(
                'prebills' => 
                        array(
                                'all' => array(
                                        'name' => '% абон.',
                                        'field_name' => 'per_abon' 
                                ),
                        ), 
                'bills' => 
                        array(
                                'all' => array(
                                        'name' => '% счет.',
                                        'field_name' => 'per_bill_sum' 
                                ),
                        ),
        );
        $interest_type = false;
        $agent_interests = array();
        $interests = array();
        if ($agent)
        {
                $row_exists = AgentInterests::exists($agent['dealer_id']);
                if ($row_exists)
                {
                        $_agent_interests = AgentInterests::find($agent['dealer_id']);
                        $interest_type = $_agent_interests->interest;
                        foreach ($interests_types[$interest_type] as $k => $v)
                        {
                                $agent_interests[$k] = $_agent_interests->$v['field_name'];
                        }
                        $interests=array_keys($interests_types[$interest_type]);
                } else {
                        $interests = array('all');
                        $design->assign('default_interest', true);
                        $interest_type = 'prebills';
                        $agent_interests = array('all' => 20);
                }
                $interests_types = $interests_types[$interest_type];
                
                $design->assign('interests', $interests);
                $design->assign('interests_types', $interests_types);
                $design->assign('interest_type', $interest_type);
                $design->assign('agent_interests', $agent_interests);
        }
        $cur_m = get_param_raw("from_m", date('m'));
        $cur_y = get_param_raw("from_y", date('Y'));

        $mm = array();
        for($i=1;$i<=12;$i++) $mm[date('m', mktime(0,0,0,$i,1,date('Y')))] = mdate('Месяц', mktime(0,0,0,$i,1,date('Y')));
        $yy = array(date('Y'), date('Y')-1);

        $from = date("01.m.Y", mktime(0,0,0,$cur_m,1,$cur_y));
        $to = date("t.m.Y", mktime(0,0,0,$cur_m,1,$cur_y));

        list($R, $T) = AgentReport::getReportData($agent, $from, $to, $interest_type, $agent_interests, $interests);

        if ($export) {
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="agent_report_'.date("01mY", mktime(0,0,0,$cur_m,1,$cur_y)).'_'.date("tmY", mktime(0,0,0,$cur_m,1,$cur_y)).'.csv"');

            ob_start();
            
            echo 'Агент:;'.$agent['name'].';Расчетный период с;'.$from.'г.;по;'.$to.'г.;';
            echo "\n";
            echo ';;;;;;';
            echo "\n";
            $str = ($interest_type == 'bills')? 'Сумма полученных платежей' : 'Абон плата;Сумма оплаченных счетов';
            echo ('Компания;'.$str.';Тип вознаграждения;%;Сумма вознаграждения;');
            echo "\n";
            foreach ($R as $r) {
                foreach ($interests as $v) {
                        echo '"' . $r['company'] . '";';
                        if ($interest_type == 'prebills')
                        {
                            echo '"' . number_format($r['isum'], 2, ',', '') . '";';
                        }
                        echo '"' . number_format($r['psum'], 2, ',', '') . '";';
                        echo '"' . $interests_types[$v]['name'] . '";';
                        echo '"' . $agent_interests[$v] . ' %";';
                        $key = $interest_type . '_' . $v;
                        echo '"' . number_format($r['fsums'][$key], 2, ',', '') . '";';
                        echo "\n";
                }
            }
            echo '"Итого";';
            if ($interest_type == 'prebills')
            {
                echo ';';
            }
            echo '"' . number_format($T['psum'], 2, ',', '') . '";;;';
            echo '"' . number_format($T['fsum'], 2, ',', '') . '";';
            echo "\n";
            echo iconv('utf-8', 'windows-1251', ob_get_clean());
            exit;
        } else {
            $params = array(
                            'mm'=>$mm,
                            'yy'=>$yy,
                            'inns'=>$R,
                            'agent'=>$agent,
                            'agents'=>$agents,
                            'cur_m'=>$cur_m,
                            'cur_y'=>$cur_y,
                            'total'=>$T,
                            'from'=>$from,
                            'to'=>$to
            );
            $design->assign($params);
            $design->AddMain("stats/report_agent.tpl");
        }
    }
    
    
    /** 
     * Получение данных отчета
     * @param array $agent информация об агенте
     * @param string $from  начало периода
     * @param string $to  конец периода
     * @param string $interest_type тип начисления поощрений агента
     * @param array $agent_interests данные о возможных поощрениях агента
     * @param array $interests данные о подтипах поощрений агента
     */
    private static function getReportData($agent = false, $from = false, $to = false, $interest_type = false, $agent_interests = false, $interests = array())
    {
        if ($agent === false) return array(array(),array());
        global $db;
        
        $from = date("Y-m-d", strtotime($from));
        $to = date("Y-m-d", strtotime($to));

        $interests_types = array();
        foreach ($interests as $v)
        {
                $interests_types[$interest_type .'_' . $v] = 0;
                $agent_interests[$interest_type .'_' . $v] = $agent_interests[$v];
        }
        
        $interests_fields = array(
            'prebills' => 
                array(
                    'all' => "sum( 
                                IF(
                                    l.service = 'usage_voip' OR l.service = 'usage_virtpbx',
                                    if (l.item like '%Абонентск%', l.sum, 0),
                                    IF (
                                            l.type = 'service' 
                                        AND 
                                            l.sum > 0 
                                        AND 
                                            (l.item like '%номер%' OR l.item like '%ВАТС%') 
                                        AND 
                                            l.item like '%Абонентск%', 
                                        l.sum,
                                        0
                                    )
                                )
                            )",
                ), 
            'bills' => 
                array(
                    'all' => 'sum(l.sum)',
                )
        );
        $fields = '';
        foreach ($interests as $v) 
        {
            $fields .= $interests_fields[$interest_type][$v] . ' as ' . $interest_type . '_' . $v . ', ';
        }
        if ($interest_type == 'bills')
        {
            return AgentReport::getReportDataBills($agent, $fields, $from, $to, $interests_types, $agent_interests);
        } else {
            return AgentReport::getReportDataPrebills($agent, $fields, $from, $to, $interests_types, $agent_interests);
        }

        
    }
    
    /** 
     * Получение данных при вознагрождение "% от счета" при подсчете от "Счетов"
     * @param array $agent информация об агенте
     * @param string $fields  доп поля для запроса
     * @param string $from  начало периода
     * @param string $to  конец периода
     * @param string $interests_types данные о подтипах поощрений агента
     * @param array $agent_interests данные о возможных поощрениях агента
     */
    private static function getReportDataBills($agent, $fields, $from, $to, $interests_types, $agent_interests)
    {
        global $db;
        $ret = array(); 
        $total = array('psum'=>0, 'fsum'=>0, 'nds'=>0);
        $R = $db->AllRecords($q = "
                SELECT " . $fields . " 
                    c.id, c.client, cg.name AS company,
                    sum(l.sum) as bills
                FROM
                    clients c
                LEFT JOIN client_contract cr ON (cr.id = c.contract_id)
                LEFT JOIN client_contragent cg ON (cg.id = cr.contragent_id)
                LEFT JOIN newbills b ON (b.client_id = c.id)
                LEFT JOIN newbill_lines l ON (b.bill_no = l.bill_no)
                WHERE
                    c.sale_channel = ".$agent['id']."
                AND b.bill_date >= '".$from."'
                AND b.bill_date <= '".$to."' 
                AND l.sum > 0 
                GROUP BY c.id
             ");
             
        foreach ($R as $r) {
            $ret[$r['id']] = array(
                'id'=>$r['id'],
                'client'=>$r['client'],
                'company'=>$r['company'],
                'psum'=>0,
                'fsum'=>0, 
                'period'=>0,
                'fsums' => $interests_types);
        }

        $R2 = $db->AllRecords($q = "
            SELECT 
                c.id, 
                sum(if(l.item LIKE '%номер%' OR l.item LIKE '%ВАТС%', l.sum, 0)) as  bills_all 
            FROM
                clients c
            LEFT JOIN 
                newbills b  ON (b.client_id = c.id)
            LEFT JOIN 
                newbill_lines l ON (l.bill_no = b.bill_no)
            WHERE
                    c.sale_channel = ".$agent['id']."
                AND b.bill_date >= '".$from."' 
                AND b.bill_date <= '".$to."' 
                AND b.bill_no NOT LIKE '%/%'
                AND b.is_payed = 1  
                    
            GROUP BY c.id
        ");

        foreach ($R2 as $r) 
        {
            $r['bills'] = $r['bills_all'];
            $ret[$r['id']]['psum'] += $r['bills'];
            $total['psum'] += $ret[$r['id']]['psum'];
            foreach ($interests_types as $k => $v) {
                        $sum = round($r[$k]*$agent_interests[$k]/100, 2);
                        $ret[$r['id']]['fsums'][$k] += $sum;
                        $ret[$r['id']]['fsum'] += $sum;
                        $total['fsum'] += $ret[$r['id']]['fsum'];
            }
        }
        //$total = AgentReport::prepareTotals($total);
        return array($ret, $total);
    }
    
    /**
     * Получение данных при вознагрождение "% от абонентской платы"
     * @param array $agent информация об агенте
     * @param string $fields  доп поля для запроса
     * @param string $from  начало периода
     * @param string $to  конец периода
     * @param string $interests_types данные о подтипах поощрений агента
     * @param array $agent_interests данные о возможных поощрениях агента
     */
    private static function getReportDataPrebills($agent, $fields, $from, $to, $interests_types, $agent_interests)
    {
        global $db;
        $ret = array(); 
        $total = array('psum'=>0, 'fsum'=>0, 'nds'=>0);
        $R = $db->AllRecords($q = "
                SELECT " . $fields . " 
                    c.id, c.client, cg.name AS company,
                    sum(l.sum) as bills,
                    sum( IF(
                            l.service = 'usage_voip' OR l.service = 'usage_virtpbx',
                            if (l.item like '%Абонентск%', l.sum, 0),
                            IF (
                                    l.type = 'service' 
                                AND 
                                    l.sum > 0 
                                AND 
                                    (l.item like '%номер%' OR l.item like '%ВАТС%') 
                                AND 
                                    l.item like '%Абонентск%', 
                                l.sum,
                                0
                            )
                        )
                    ) as prebills 
                FROM
                    clients c
                LEFT JOIN client_contract cr ON (cr.id = c.contract_id)
                LEFT JOIN client_contragent cg ON (cg.id = cr.contragent_id)
                LEFT JOIN newbills b ON (b.client_id = c.id)
                LEFT JOIN newbill_lines l ON (b.bill_no = l.bill_no)
                WHERE
                    c.sale_channel = ".$agent['id']."
                AND b.bill_date >= '".$from."'
                AND b.bill_date <= '".$to."' 
                AND l.sum > 0 
                GROUP BY c.id
             ");
             
        foreach ($R as $r) {
            $ret[$r['id']] = array(
                'id'=>$r['id'],
                'client'=>$r['client'],
                'company'=>$r['company'],
                'isum'=>$r['prebills'],
                'psum'=>0,
                'fsum'=>0, 
                'period'=>0,
                'fsums' => $interests_types);
        }

        $R2 = $db->AllRecords($q = "
            SELECT " . $fields . " 
                c.id, 
                sum(
                    IF(
                        l.service = 'usage_voip' OR l.service = 'usage_virtpbx',
                        if (l.item like '%Абонентск%', l.sum, 0),
                        IF (
                                l.type = 'service' 
                            AND 
                                l.sum > 0 
                            AND 
                                (l.item like '%номер%' OR l.item like '%ВАТС%') 
                            AND 
                                l.item like '%Абонентск%', 
                            l.sum,
                            0
                        )
                    )
                ) as prebills,
                sum(l.sum) as bills 
            FROM
                clients c
            LEFT JOIN 
                newbills b ON (b.client_id = c.id)
            LEFT JOIN 
                newbill_lines l ON (b.bill_no = l.bill_no)
            WHERE
                    c.sale_channel = ".$agent['id']."
                AND
                    b.bill_date >= '".$from."' 
                AND
                    b.bill_date <= '".$to."' 
                AND 
                    b.is_payed = 1
                AND 
                    l.sum > 0 
            GROUP BY c.id
        ");
            
        foreach ($R2 as $r) {
            $ret[$r['id']]['isum'] = $r['prebills'];
            $ret[$r['id']]['psum'] = $r['bills'];
            $total['psum'] += $ret[$r['id']]['psum'];
            foreach ($interests_types as $k => $v) {
                        $sum = round($r[$k]*$agent_interests[$k]/100, 2);
                        $ret[$r['id']]['fsums'][$k] += $sum;
                        $ret[$r['id']]['fsum'] += $sum;
                        $total['fsum'] += $ret[$r['id']]['fsum'];
            }
        }
        //$total = AgentReport::prepareTotals($total);
        return array($ret, $total);
    }
    
    
    /** 
     * Подговка массива для вывода текстовых значений вознагрождений
     * @param array $total массив с данными
     */
    /*
    private static function prepareTotals($total)
    {
        $total['nds'] = round($total['fsum']*(18/118), 2);
        $total['fsum_str'] = floor($total['fsum']) . ' руб. ' . floor(round(100*($total['fsum'] - floor($total['fsum'])), 5)) . ' коп.';
        $total['nds_str'] = floor($total['nds']) . ' руб. ' . floor(round(100*($total['nds'] - floor($total['nds'])), 5)) . ' коп.';
        return $total;
    }
    */
    
    /** 
     * Обработка параметров запроса и вызов соответствующей функии для детализации
     */
    public static function getDetails()
    {
        $type = get_param_raw('type', '');
        $month = get_param_integer('month', 0);
        $year = get_param_integer('year', 0);
        $client_id = get_param_integer('client_id', 0);
        
        if (!$type || !$client_id || !$month || !$year)
        {
                return false;
        }
        
        switch ($type)
        {
                case 'bills':
                        return self::getBillsDetails($client_id, $month, $year);
                case 'prebills':
                        return self::getPrebillsDetails($client_id, $month, $year);
        }
        
        return false;
    }
    
    
    /** 
     * Получение детализации об оплаченных счетах
     * @param int $client_id ID клиента по которому идет детализация
     * @param int $month месяц по которому идет детализация
     * @param int $year год по которому идет детализация
     */
    private static function getPrebillsDetails($client_id, $month, $year)
    {
        global $db,$design;
        $from = mktime(0,0,0,$month,1,$year);
        $to = strtotime('last day of this month', $from);
        
        $title = array();
        $title['period'] = ' в период с 1 по ' . mdate('d месяца Y ',$to);
        $title['title'] = \app\models\ClientAccount::findOne($client_id)->contract->contragent->name;
        $title['client_id'] = $client_id;
        
        $from = date('Y-m-d', $from);
        $to = date('Y-m-d', $to);
        
        $data = $db->AllRecords($q = "
            SELECT 
                b.bill_no, 
                UNIX_TIMESTAMP(b.bill_date) as ts,
                b.sum as b_sum, 
                l.sum, 
                l.item, 
                IF(
                    l.service = 'usage_voip' OR l.service = 'usage_virtpbx',
                    if (l.item like '%Абонентск%', 1, 0),
                    IF (
                            l.type = 'service' 
                        AND l.sum > 0 
                        AND (l.item like '%номер%' OR l.item like '%ВАТС%') 
                        AND l.item like '%Абонентск%', 
                    1,
                    0
                    )
                ) as is_abon,
                b.is_payed
            FROM 
                newbills as b 
            LEFT JOIN 
                newbill_lines as l ON b.bill_no = l.bill_no 
            WHERE 
                    b.client_id = ".$client_id." 
                AND b.bill_date >= '".$from."' 
                AND b.bill_date <= '".$to."' 
                AND b.bill_no NOT LIKE '%/%' 
            ORDER BY 
                b.bill_no, l.sort
        ");
        
        $totals = $db->GetRow($q = "
            SELECT 
                sum(
                    IF(
                        l.service = 'usage_voip' OR l.service = 'usage_virtpbx',
                        if (l.item like '%Абонентск%', l.sum, 0),
                        IF (
                                l.type = 'service' 
                            AND l.sum > 0 
                            AND (l.item like '%номер%' OR l.item like '%ВАТС%') 
                            AND l.item like '%Абонентск%', 
                            l.sum,
                            0
                        )
                    )
                ) as prebills,
                sum(l.sum) as bills 
            FROM
                newbills b 
            LEFT JOIN 
                newbill_lines l ON (b.bill_no = l.bill_no)
            WHERE
                    b.client_id = ".$client_id."
                AND b.bill_date >= '".$from."' 
                AND b.bill_date <= '".$to."' 
                AND b.is_payed = 1
                AND l.sum > 0 
        ");

        $design->assign('totals', $totals);
        $design->assign('title', $title);
        $design->assign('data', $data);
        $design->ProcessEx('errors.tpl');
        $design->ProcessEx('stats/agent_details_prebills.tpl');
    }
    
    /** 
     * Получение детализации об оплаченных счетах
     * @param int $client_id ID клиента по которому идет детализация
     * @param int $month месяц по которому идет детализация
     * @param int $year год по которому идет детализация
     */
    private static function getBillsDetails($client_id, $month, $year)
    {
        global $db,$design;
        $from = mktime(0,0,0,$month,1,$year);
        $to = strtotime('last day of this month', $from);
        
        $title = array();
        $title['period'] = ' в период с 1 по ' . mdate('d месяца Y ',$to);
        $title['title'] = \app\models\ClientAccount::findOne($client_id)->contract->contragent->name;
        $title['client_id'] = $client_id;
        
        $from = date('Y-m-d', $from);
        $to = date('Y-m-d', $to);
        
        $data = $db->AllRecords($q = "
            SELECT 
                b.bill_no, 
                UNIX_TIMESTAMP(b.bill_date) as ts,
                b.sum as b_sum, 
                l.sum, 
                l.item, 
                IF(
                    l.item like '%номер%' OR l.item like '%ВАТС%',
                    1,
                    0
                ) as is_abon,
                b.is_payed
            FROM 
                newbills as b 
            LEFT JOIN 
                newbill_lines as l ON b.bill_no = l.bill_no 
            WHERE 
                    b.client_id = ".$client_id." 
                AND b.bill_date >= '".$from."' 
                AND b.bill_date <= '".$to."' 
                AND b.bill_no NOT LIKE '%/%' 
            ORDER BY 
                b.bill_no, l.sort
        ");
        
        $totals = $db->GetRow($q = "
            SELECT 
                sum(
                    IF(
                        l.item like '%номер%' OR l.item like '%ВАТС%', 
                        l.sum,
                        0
                    )
                ) as prebills,
                sum(l.sum) as bills 
            FROM
                newbills b 
            LEFT JOIN 
                newbill_lines l ON (b.bill_no = l.bill_no)
            WHERE
                    b.client_id = ".$client_id."
                AND b.bill_date >= '".$from."' 
                AND b.bill_date <= '".$to."' 
                AND b.is_payed = 1
                AND l.sum > 0 
        ");
        
        $design->assign('totals', $totals);
        $design->assign('title', $title);
        $design->assign('data', $data);
        $design->ProcessEx('errors.tpl');
        $design->ProcessEx('stats/agent_details_bills.tpl');
    }
}
    
?>
