<?php

namespace app\classes\documents;

use Yii;
use yii\helpers\ArrayHelper;
use app\classes\Singleton;
use app\classes\Company;
use app\models\Bill;

abstract class DocumentReport extends Singleton
{

    const TEMPLATE_PATH = '@app/views/documents/';

    const BILL_DOC_TYPE = 'bill';

    const CURRENCY_RUB = 'RUB';
    const CURRENCY_USD = 'USD';
    const CURRENCY_FT = 'FT';

    public $bill;
    public $bill_lines = [];
    public $summary;
    public $qr_code = false;

    public function setBill(Bill $bill)
    {
        $this->bill = $bill;

        return $this;
    }

    public function getCompany()
    {
        return Company::getProperty($this->bill->clientAccount->firma, $this->bill->bill_date);
    }

    public function getCompanyDetails()
    {
        return Company::getDetail($this->bill->clientAccount->firma, $this->bill->bill_date);
    }

    public function getClassName() {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getTemplateFile()
    {
        return self::TEMPLATE_PATH . $this->getCountryLang() . '/' . $this->getDocType() . '_' . mb_strtolower($this->getCurrency(), 'UTF-8');
    }

    public function getHeaderTemplate()
    {
        return self::TEMPLATE_PATH . $this->getCountryLang() . '/header_base';
    }

    public function prepare()
    {
        $this->bill_lines = array_map(function($line) {
            $result = ArrayHelper::toArray($line);

            $result['ts_from'] = strtotime($line['date_from']);
            $result['ts_to'] = strtotime($line['date_to']);

            return $result;
        }, $this->bill->lines);

        $this->bill_lines = static::doPrintPrepareFilter($this->getDocType(), $this->getDocSource(), $this->bill_lines, '');
        $this->calculateSummary();

        //$design->assign("bill_no_qr", ($bill->GetTs() >= strtotime("2013-05-01") ? QRCode::getNo($bill->GetNo()) : false));

        //return $this->prepareFilter($bill->GetLines());
        return $this;
    }

    public function prepareFilter($lines)
    {
        return $lines;
    }

    public function doPrintPrepare()
    {
        $obj = $this->getDocType();
        $source  = $this->getDocSource();
        $origObj = $obj;

        if ($obj == 'gds') {
            $obj = 'bill';
        }
        if ($source == 4) {
            $source = 1;
            $is_four_order = true;
        }
        else
            $is_four_order = false;

        if (is_null($source))
            $source = 3;

        if($bill->isOneTimeService())// или разовая услуга
        {
            if($bdata["doc_ts"])
            {
                $inv_date = $bill->GetTs();
                $period_date = get_inv_period($inv_date);
            }else{
                list($inv_date, $period_date)=get_inv_date($bill->GetTs(),($bill->Get('inv2to1')&&($source==2))?1:$source);
            }
        }else{ // статовские переодичекские счета
            list($inv_date, $period_date)=get_inv_date($bill->GetTs(),($bill->Get('inv2to1')&&($source==2))?1:$source);
        }

        if(in_array($obj, array('invoice','akt','upd')))
        {
            if(date("Ymd", $inv_date) != date("Ymd", $bill->GetTs()))
            {
                $bill->SetClientDate(date("Y-m-d", $inv_date));
            }
        }

        if($is_four_order){
            $row = $db->QuerySelectRow('newpayments',array('bill_no'=>$bill->GetNo()));
            if($row['payment_date']){
                $da = explode('-',$row['payment_date']);
                $inv_date = mktime(0,0,0,$da[1],$da[2],$da[0]);
            }else{
                $inv_date= time();
            }
            unset($da,$row);
        }




        if(in_array($obj, array('invoice','upd')) && (in_array($source, array(1,3)) || ($source==2 && $bill->Get('inv2to1'))) && $do_assign) {//привязанный к фактуре счет
            //не отображать если оплата позже счета-фактуры
            $query = "
                SELECT
                    *,
                    UNIX_TIMESTAMP(payment_date) as payment_date_ts
                FROM
                    newpayments
                WHERE
                    payment_no<>''
                AND
                    `sum`>=0
                AND
                    (
                        bill_no='".$bdata['bill_no']."'
                    OR
                        bill_vis_no='".$bdata['bill_no']."'
                    )
                AND
                    1  IN (
                        SELECT
                            newpayments.payment_date
                                BETWEEN
                                    adddate(
                                        date_format(newbills.bill_date,'%Y-%m-01'),
                                        interval -1 month
                                    )
                                AND
                                    adddate(
                                        adddate(
                                            date_format(newbills.bill_date,'%Y-%m-01'),
                                            interval 1 month
                                        ),
                                        interval -1 day
                                    )
                        FROM
                            newbills
                        WHERE
                            newbills.bill_no = IFNULL(
                                (
                                    SELECT np1.bill_no
                                    FROM newpayments np1
                                    WHERE np1.bill_no = '".$bdata['bill_no']."'
                                    GROUP BY np1.bill_no
                                ),
                                (
                                    SELECT np2.bill_vis_no
                                    FROM newpayments np2
                                    WHERE np2.bill_vis_no = '".$bdata['bill_no']."'
                                    GROUP BY np2.bill_vis_no
                                )
                            )
                    ) /*or bill_no = '201109/0574'*/
            ";

            $inv_pays = $db->AllRecords($query,null,MYSQL_ASSOC);
            if($inv_pays)
                $design->assign('inv_pays',$inv_pays);
        }

        $bdata['ts']=$bill->GetTs();


        $L_prev=$bill->GetLines((preg_match('/bill-\d/',self::$object))?'order':false);//2 для фактур значит за прошлый период
        //print_r($L_prev);

        if(in_array($obj, array("invoice","upd")))
        {
            $this->checkSF_discount($L_prev);
        }


        $design->assign_by_ref('negative_balance', $bill->negative_balance); // если баланс отрицательный - говорим, что недостаточно средств для проведения авансовых платежей

        foreach($L_prev as $k => $li){
            if (!($obj=='bill' || ($li['type']!='zadatok' || $is_four_order))) {
                unset($L_prev[$k]);
            }
        }
        unset($li);

        $L = self::do_print_prepare_filter($obj,$source,$L_prev,$period_date,(($obj == "invoice" || $obj == "upd") && $source == 3), $isSellBook ? true : false, $origObj);

        if($is_four_order){
            $L =& $L_prev;
            $bill->refactLinesWithFourOrderFacure($L);
        }

        //подсчёт итоговых сумм, получить данные по оборудованию для акта-3

        $cpe = array();
        $bdata["sum"] = 0;
        $bdata['sum_without_tax'] = 0;
        $bdata['sum_tax'] = 0;


        $r = $bill->Client();

        foreach ($L as &$li) {

            $bdata['sum']               += $li['sum'];
            $bdata['sum_without_tax']   += $li['sum_without_tax'];
            $bdata['sum_tax']           += $li['sum_tax'];

            if ($obj=='akt' && $source==3 && $do_assign) {			//связь строчка>устройство или строчка>подключение>устройство
                $id = null;
                if ($li['service']=='tech_cpe') {
                    $id = $li['id_service'];
                } elseif ($li['service']=='usage_ip_ports') {
                    $r = $db->GetRow('select id_service from tech_cpe where id_service='.$li['id_service'].' AND actual_from<"'.$inv_date.'" AND actual_to>"'.$inv_date.'" order by id desc limit 1');
                    if ($r) $id = $r['id_service'];
                }
                if ($id) {
                    $r=$db->GetRow('select tech_cpe.*,model,vendor,type from tech_cpe INNER JOIN tech_cpe_models ON tech_cpe_models.id=tech_cpe.id_model WHERE tech_cpe.id='.$id);
                    $r['amount'] = floatval($li['amount']);
                    $cpe[]=$r;
                } else {
                    $cpe[]=array('type'=>'','vendor'=>'','model'=>$li['item'],'serial'=>'','amount'=>floatval($li['amount']), "actual_from" => $li["date_from"]);
                }
            }
        }
        unset($li);

        $b = $bill->GetBill();

        if ($do_assign){
            $design->assign('cpe',$cpe);
            $design->assign('curr',$curr);
            if (in_array($obj, array('invoice','akt','upd'))) {
                $design->assign('inv_no','-'.$source);
                $design->assign('inv_date',$inv_date);
                $design->assign('inv_is_new',($inv_date>=mktime(0,0,0,5,1,2006)));
                $design->assign('inv_is_new2',($inv_date>=mktime(0,0,0,6,1,2009)));
                $design->assign('inv_is_new3', ($inv_date>=mktime(0,0,0,1,24,2012)));
                $design->assign('inv_is_new4', ($inv_date>=mktime(0,0,0,2,13,2012)));
                $design->assign('inv_is_new5', ($inv_date>=mktime(0,0,0,10,1,2012))); // доработки в акте и сф, собственные (акциз, шт => -) + увеличен шрифт в шапке
                $design->assign('inv_is_new6', ($inv_date>=mktime(0,0,0,1,1,2013))); // 3 (объем), 5 всего, 6 сумма, 8 предъявлен покупателю, 8 всего
            }
            $design->assign('opener','interface');
            $design->assign('bill',$bdata);
            $design->assign('bill_lines',$L);
            $total_amount = 0;
            foreach($L as $line){
                $total_amount += round($line['amount'],2);
            }
            $design->assign('total_amount',$total_amount);

            $docDate = $obj == "bill" ? $b["bill_date"] : $inv_date;

            Company::setResidents($r["firma"], $docDate);
            $design->assign("firm", Company::getProperty($r["firma"], $docDate));

            ClientCS::Fetch($r);
            $r["manager_name"] = ClientCS::getManagerName($r["manager"]);
            $design->assign('bill_client',$r);
            return true;
        } else {
            if (in_array($obj, array('invoice','akt','upd'))) {
                return array('bill'=>$bdata,'bill_lines'=>$L,'inv_no'=>$bdata['bill_no'].'-'.$source,'inv_date'=>$inv_date);
            } else return array('bill'=>$bdata,'bill_lines'=>$L);
        }
    }

    public static function doPrintPrepareFilter($obj, $source, &$lines, $period_date, $inv3Full = true, $isViewOnly = false, $origObj = false)
    {
        $M = array();

        if ($origObj === false)
            $origObj = $obj;

        if ($obj == "gds") {
            $M = [
                'all4net'   => 0,
                'service'   => 0,
                'zalog'     => 0,
                'zadatok'   => 0,
                'good'      => 1,
                '_'         => 0
            ];
        }
        else {
            if ($obj == 'bill') {
                $M = [
                    'all4net' => 1,
                    'service' => 1,
                    'zalog' => 1,
                    'zadatok' => ($source == 2 ? 1 : 0),
                    'good' => 1,
                    '_' => 0
                ];
            } else if ($obj == 'lading') {
                $M = [
                    'all4net'   => 1,
                    'service'   => 0,
                    'zalog'     => 0,
                    'zadatok'   => 0,
                    'good'      => 1,
                    '_'         => 0
                ];
            } elseif ($obj == 'akt') {
                if ($source == 3) {
                    $M = [
                        'all4net'   => 0,
                        'service'   => 0,
                        'zalog'     => 1,
                        'zadatok'   => 0,
                        'good'      => 0,
                        '_'         => 0
                    ];
                } elseif (in_array($source, array(1, 2))) {
                    $M = [
                        'all4net'   => 1,
                        'service'   => 1,
                        'zalog'     => 0,
                        'zadatok'   => 0,
                        'good'      => 0,
                        '_'         => $source
                    ];
                }
            }
            else { //invoice
                if (in_array($source, array(1, 2))) {
                    $M = [
                        'all4net'   => 1,
                        'service'   => 1,
                        'zalog'     => 0,
                        'zadatok'   => 0,
                        'good'      => 0, //($obj=='invoice'?1:0);
                        '_'         => $source
                    ];
                }
                elseif ($source == 3) {
                    $M = [
                        'all4net'   => 1,
                        'service'   => 0,
                        'zalog'     => ($isViewOnly) ? 0 : 1,
                        'zadatok'   => 0,
                        'good'      => $inv3Full ? 1 : 0,
                        '_'         => 0
                    ];
                }
                elseif ($source == 4) {
                    if (!count($lines))
                        return [];
                    foreach ($lines as $line) {
                        $bill = $line;
                        break;
                    }

                    $ret = Yii::$app->db->createCommand("
                      SELECT
                          bill_date,
                          nal
                      FROM
                          newbills
                      WHERE
                          bill_no = '" . $bill['bill_no'] . "'
                    ")->queryOne();

                    if (in_array($ret['nal'], array('nal', 'prov'))) {
                        $ret = Yii::$app->db->createCommand("
                            SELECT
                                *
                            FROM
                                newpayments
                            WHERE
                                bill_no = '" . $bill['bill_no'] . "'
                        ")->queryOne();
                        if ($ret == 0)
                            return -1;
                    }

                    $query = "
                        SELECT
                            *
                        FROM
                            newbills nb
                        INNER JOIN
                            newpayments np
                        ON
                            (
                                np.bill_vis_no = nb.bill_no
                            OR
                                np.bill_no = nb.bill_no
                            )
                        AND
                            (
                                (
                                    YEAR(np.payment_date)=YEAR(nb.bill_date)
                                AND
                                    (
                                        MONTH(np.payment_date)=MONTH(nb.bill_date)
                                    OR
                                        MONTH(nb.bill_date)-1=MONTH(np.payment_date)
                                    )
                                )
                            OR
                                (
                                    YEAR(nb.bill_date)-1=YEAR(np.payment_date)
                                AND
                                    MONTH(np.payment_date)=1
                                AND
                                    MONTH(nb.bill_date)=12
                                )
                            )
                        WHERE
                            nb.bill_no = '" . $bill['bill_no'] . "'
                    ";

                    //echo $query;
                    $ret = Yii::$app->db->createCommand($query)->queryOne();

                    if ($ret == 0)
                        return 0;

                    $R = [];
                    foreach ($lines as $line) {
                        if (preg_match("/^\s*Абонентская\s+плата|^\s*Поддержка\s+почтового\s+ящика|^\s*Виртуальная\s+АТС|^\s*Перенос|^\s*Выезд|^\s*Сервисное\s+обслуживание|^\s*Хостинг|^\s*Подключение|^\s*Внутренняя\s+линия|^\s*Абонентское\s+обслуживание|^\s*Услуга\s+доставки|^\s*Виртуальный\s+почтовый|^\s*Размещение\s+сервера|^\s*Настройка[0-9a-zA-Zа-яА-Я]+АТС|^Дополнительный\sIP[\s\-]адрес|^Поддержка\sпервичного\sDNS|^Поддержка\sвторичного\sDNS|^Аванс\sза\sподключение\sинтернет-канала|^Администрирование\sсервер|^Обслуживание\sрабочей\sстанции|^Оптимизация\sсайта/", $line['item']))
                            $R[] = $line;
                    }
                    return $R;
                } else {
                    return [];
                }
            }
        }

        $R = array();
        foreach ($lines as &$li) {
            if ($M[ $li['type'] ] == 1) {
                if(
                    $M['_']==0
                    || ( $M['_'] == 1 && $li['ts_from'] >= $period_date)
                    || ( $M['_'] == 2 && $li['ts_from'] < $period_date)
                ){
                    if(
                        $li['sum'] != 0 ||
                        $li['item'] == 'S' ||
                        ($origObj == "gds" && $source == 2) ||
                        preg_match("/^Аренд/i", $li['item']) ||
                        ($li['sum'] == 0 && preg_match("|^МГТС/МТС|i", $li['item']))
                    )
                    {
                        if ($li['sum'] == 0) {
                            $li['outprice'] = 0;
                            $li['price'] = 0;
                        }
                        $R[] = &$li;
                    }
                }
            }
        }

        return $R;
    }

    protected function calculateSummary()
    {
        foreach ($this->bill_lines as $line) {
            $this->summary->value       += $line['sum'];
            $this->summary->without_tax += $line['sum_without_tax'];
            $this->summary->with_tax    += $line['sum_tax'];
        }
    }

    abstract public function getCountryLang();

    abstract public function getCurrency();

    abstract public function getDocType();

    public function getDocSource()
    {
        return '';
    }

    abstract public function getName();

}