<?php

namespace app\classes\documents;

use Yii;
use yii\helpers\ArrayHelper;
use app\classes\Singleton;
use app\classes\Company;
use app\classes\Utils;
use app\classes\BillQRCode;
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

    public function getCompanyResidents()
    {
        return Company::setResidents($this->bill->clientAccount->firma, $this->bill->bill_date);
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

        $this->doPrintPrepare();
        $this->calculateSummary();

        if (strtotime($this->bill->bill_date) >= strtotime('2013-05-01'))
            $this->qr_code = BillQRCode::getNo($this->bill->bill_no);

        return $this;
    }

    public function prepareFilter($lines)
    {
        return $lines;
    }

    public function doPrintPrepare()
    {
        $source  = $this->getDocSource();

        $isOneTimeService = (
            sizeof($this->bill_lines) != 1 &&
            (
                $this->bill_lines[0]['type'] == 'service' &&
                $this->bill_lines[0]['id_service'] == 0 &&
                $this->bill_lines[0]['service'] == ''
            )
        ) ? true : false;

        // if ($bill->isOneTimeService())// или разовая услуга
        if ($isOneTimeService)
        {
            if(strtotime($this->bill->doc_date)) {
                $period_date = Utils::get_inv_period(strtotime($this->bill->bill_date));
            }else{
                list($inv_date, $period_date) = Utils::get_inv_date(strtotime($this->bill->bill_date),($this->bill->inv2to1 && $source == 2) ? 1 : $source);
            }
        }
        else { // статовские переодические счета
            list($inv_date, $period_date) = Utils::get_inv_date(strtotime($this->bill->bill_date),($this->bill->inv2to1 && $source == 2) ? 1 : $source);
        }

        $this->bill_lines = static::doPrintPrepareFilter($this->getDocType(), $source, $this->bill_lines, $period_date);
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