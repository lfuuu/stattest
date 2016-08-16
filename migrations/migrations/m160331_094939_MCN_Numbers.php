<?php

use app\helpers\DateTimeZoneHelper;
use \app\models\Region;
use \app\models\City;

class m160331_094939_MCN_Numbers extends \app\classes\Migration
{
    public function up()
    {

        $this->addColumn('voip_numbers', 'number_tech', $this->string(15));
        $this->addColumn('voip_numbers', 'operator_account_id', $this->integer(11));
        $this->addColumn('voip_numbers', 'country_code', $this->integer(4));
        $this->addColumn('voip_numbers', 'ndc', $this->integer(10), ['comment' => 'National Destination Code']);
        $this->addColumn('voip_numbers', 'number_subscriber', $this->string(15));
        $this->addColumn('voip_numbers', 'number_type', $this->integer());
        $this->addColumn('voip_numbers', 'date_start', $this->dateTime());
        $this->addColumn('voip_numbers', 'date_end', $this->dateTime());
        $this->createIndex('voip_numbers__number_tech', 'voip_numbers', 'number_tech', true);

        $data = $this->getData();
        $dateStart = (new \DateTime('now',
            new \DateTimeZone(\app\helpers\DateTimeZoneHelper::TIMEZONE_DEFAULT)))->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $this->getDb()->transaction(function () use ($data, $dateStart) {
            $this->batchInsert(
                'voip_numbers',
                [
                    'number',
                    'region',
                    'status',
                    'city_id',
                    'number_tech',
                    'operator_account_id',
                    'country_code',
                    'ndc',
                    'number_subscriber',
                    'number_type',
                    'date_start'
                ],
                array_map(function ($k, $v) use ($dateStart) {
                    return [
                        $k,        //number
                        Region::MOSCOW,             //region
                        'notsell', //status
                        City::DEFAULT_USER_CITY_ID, //city_id
                        $v,        //number_tech
                        38319,     //operator_account_id
                        '7',       //country_code
                        '800',     //ndc
                        substr($k, 4), //number_subscriber
                        5,         //number_type
                        $dateStart //date_start
                    ];

                }, array_keys($data), array_values($data)));
        });
    }

    public function down()
    {
        $this->dropIndex('voip_numbers__number_tech', 'voip_numbers');
        $this->dropColumn('voip_numbers', 'number_tech');
        $this->dropColumn('voip_numbers', 'operator_account_id');
        $this->dropColumn('voip_numbers', 'country_code');
        $this->dropColumn('voip_numbers', 'ndc');
        $this->dropColumn('voip_numbers', 'number_subscriber');
        $this->dropColumn('voip_numbers', 'number_type');
        $this->dropColumn('voip_numbers', 'date_start');
        $this->dropColumn('voip_numbers', 'date_end');
        $this->delete('voip_numbers', ['number' => array_keys($this->getData())]);
    }

    public function getData()
    {
        return [
            "78003503101" => "5555410546",
            "78003503102" => "5555410547",
            "78003503103" => "5555410548",
            "78003503104" => "5555410549",
            "78003503105" => "5555410550",
            "78003503106" => "5555410551",
            "78003503107" => "5555410552",
            "78003503108" => "5555410553",
            "78003503109" => "5555410554",
            "78003503190" => "5555410585",
            "78003503110" => "5555410555",
            "78003503112" => "5555410556",
            "78003503114" => "5555410557",
            "78003503115" => "5555410558",
            "78003503116" => "5555410559",
            "78003503117" => "5555410560",
            "78003503118" => "5555410561",
            "78003503119" => "5555410562",
            "78003503140" => "5555410579",
            "78003503141" => "5555410580",
            "78003503120" => "5555410563",
            "78003503121" => "5555410564",
            "78003503123" => "5555410565",
            "78003503124" => "5555410566",
            "78003503125" => "5555410567",
            "78003503126" => "5555410568",
            "78003503127" => "5555410569",
            "78003503128" => "5555410570",
            "78003503129" => "5555410571",
            "78003503160" => "5555410583",
            "78003503130" => "5555410572",
            "78003503132" => "5555410573",
            "78003503134" => "5555410574",
            "78003503136" => "5555410575",
            "78003503137" => "5555410576",
            "78003503138" => "5555410577",
            "78003503139" => "5555410578",
            "78003503151" => "5555410581",
            "78003503152" => "5555410582",
            "78003503170" => "5555410584",
            "78003503001" => "5555410586",
            "78003503004" => "5555410587",
            "78003503006" => "5555410588",
            "78003503007" => "5555410589",
            "78003503008" => "5555410590",
            "78003503009" => "5555410591",
            "78003503012" => "5555410592",
            "78003503013" => "5555410593",
            "78003503014" => "5555410594",
            "78003503015" => "5555410595",
            "78003503016" => "5555410596",
            "78003503017" => "5555410597",
            "78003503021" => "5555410598",
            "78003503023" => "5555410599",
            "78003503024" => "5555410600",
            "78003503025" => "5555410601",
            "78003503026" => "5555410602",
            "78003503027" => "5555410603",
            "78003503031" => "5555410604",
            "78003503032" => "5555410605",
            "78003503034" => "5555410606",
            "78003503036" => "5555410607",
            "78003503037" => "5555410608",
            "78003503038" => "5555410609",
            "78003503041" => "5555410610",
            "78003503042" => "5555410611",
            "78003503043" => "5555410612",
            "78003503045" => "5555410613",
            "78003503046" => "5555410614",
            "78003503047" => "5555410615",
            "78003503051" => "5555410616",
            "78003503052" => "5555410617",
            "78003503054" => "5555410618",
            "78003503056" => "5555410619",
            "78003503057" => "5555410620",
            "78003503058" => "5555410621",
            "78003503061" => "5555410622",
            "78003503062" => "5555410623",
            "78003503063" => "5555410624",
            "78003503064" => "5555410625",
            "78003503065" => "5555410626",
            "78003503067" => "5555410627",
            "78003503071" => "5555410628",
            "78003503072" => "5555410629",
            "78003503073" => "5555410630",
            "78003503074" => "5555410631",
            "78003503075" => "5555410632",
            "78003503076" => "5555410633",
            "78003503081" => "5555410634",
            "78003503082" => "5555410635",
            "78003503083" => "5555410636",
            "78003503084" => "5555410637",
            "78003503085" => "5555410638",
            "78003503086" => "5555410639",
            "78003503091" => "5555410640",
            "78003503092" => "5555410641",
            "78003503093" => "5555410642",
            "78003503094" => "5555410643",
            "78003503095" => "5555410644",
            "78003503096" => "5555410645",
            "78002003012" => "5555410544"
        ];
    }
}
