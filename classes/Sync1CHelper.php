<?php

class Sync1CHelper
{
    public function getClientCardData($cardId)
    {
        $clientCard = ClientCard::find($cardId);
        if (!$clientCard)
            throw new Exception('Client card not found');

        $client = $clientCard->getClient();
        if (!$client)
            throw new Exception('Client not found');

        $clientCardData = array(
            'ИдКлиентаСтат' => $client->client,
            'ИдКарточкиКлиентаСтат' => $clientCard->client,
            'КодКлиентаСтат' => $client->id,
            'КодКарточкиКлиентаСтат' => $clientCard->id,
            'НаименованиеКомпании' => $clientCard->company,
            'ПолноеНаименованиеКомпании' => $clientCard->company_full,
            'ИНН' => $clientCard->inn,
            'КПП' => $clientCard->kpp,
            'ПравоваяФорма' => $clientCard->type == 'org' ? 'ЮрЛицо' : 'ФизЛицо',
            'Организация' => $clientCard->firma,
            'ВалютаРасчетов' => $clientCard->currency,
            'ВидЦен' => $clientCard->price_type ? $clientCard->price_type: '739a53ba-8389-11df-9af5-001517456eb1'
        );

        return $clientCardData;
    }



    public function translateToUtf8($data)
    {
        if (is_string($data)) {
            return iconv('koi8-r', 'utf-8', $data);
        } elseif (is_array($data)) {
            $translated = array();
            foreach ($data as $k => $v) {
                if (is_string($k)) {
                    $k = iconv('koi8-r', 'utf-8', $k);
                }
                $translated[$k] = $this->translateToUtf8($v);
            }
            return $translated;
        } elseif ($data instanceof stdClass) {
            $translated = array();
            foreach ((array)$data as $k => $v) {
                $k = iconv('koi8-r', 'utf-8', $k);
                $translated[$k] = $this->translateToUtf8($v);
            }
            return (object)$translated;
        } else {
            return $data;
        }
    }

    public function translateToKoi8r($data)
    {
        if (is_string($data)) {
            return iconv('utf-8', 'koi8-r//ignore', $data);
        } elseif (is_array($data)) {
            $translated = array();
            foreach ($data as $k => $v) {
                if (is_string($k)) {
                    $k = iconv('utf-8', 'koi8-r//ignore', $k);
                }
                $translated[$k] = $this->translateToKoi8r($v);
            }
            return $translated;
        } elseif ($data instanceof stdClass) {
            $translated = array();
            foreach ((array)$data as $k => $v) {
                $k = iconv('utf-8', 'koi8-r//ignore', $k);
                $translated[$k] = $this->translateToKoi8r($v);
            }
            return (object)$translated;
        } else {
            return $data;
        }
    }

    public function printSoapFault($e)
    {
        $a = explode("|||",$e->getMessage());
        trigger_error("<br><font style='color: black;'>1C: <font style='font-weight: normal;'>".iconv("utf-8", "koi8-r//ignore", $a[0])."</font></font>");
        trigger_error("<font style='color: black; font-weight: normal;font-size: 8pt;'>".iconv("utf-8", "koi8-r//ignore", $a[1])."</font>");
    }

}