<?php

use app\classes\Migration;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\RegionSettings;

/**
 * Class m220725_155531_imsi_sip_storage
 */
class m220725_155531_imsi_sip_storage extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(RegionSettings::tableName(), 'sip_warehouse_status_id', $this->integer());
        foreach ([
                     98 => 26, //Санкт-Петербург
                     99 => 27, //Москва
                     95 => 28, //Екатеринбург
                     94 => 29, //Новосибирск
                     50 => 30, //Барнаул
                     49 => 31, //Белгород
                     85 => 32, //Брянск
                     31 => 33, //Улан-Удэ
                     90 => 34, //Челябинск
                     62 => 35, //Чебоксары
                     51 => 36, //Иркутск
                     74 => 37, //Ижевск
                     47 => 38, //Калуга
                     52 => 39, //Кемерово
                     72 => 40, //Калининград
                     69 => 41, //Киров
                     55 => 42, //Красноярск
                     46 => 43, //Кострома
                     68 => 44, //Сургут
                     58 => 45, //Курск
                     93 => 46, //Казань
                     59 => 47, //Липецк
                     64 => 48, //Оренбург
                     53 => 49, //Омск
                     45 => 50, //Орёл
                     92 => 51, //Пермь
                     33 => 52, //Петрозаводск
                     87 => 53, //Ростов-на-Дону
                     77 => 54, //Рязань
                     44 => 55, //Смоленск
                     96 => 56, //Самара
                     66 => 57, //Саратов
                     43 => 58, //Тамбов
                     78 => 59, //Тюмень
                     54 => 60, //Томск
                     57 => 61, //Тверь
                     63 => 62, //Ульяновск
                     91 => 63, //Волгоград
                     86 => 64, //Воронеж
                     56 => 65, //Ярославль
                     97 => 66, //Краснодар
                 ] as $regionId => $sipWarehouseStatusId) {

            $region = \app\models\Region::findOne(['id' => $regionId]);
            $cardStatus = CardStatus::findOne(['id' => $sipWarehouseStatusId]);

            echo PHP_EOL . $regionId.': ' . ($region ? $region->name : '???') . ' => ' . ($cardStatus ? $cardStatus->name : '???');

            $this->update(RegionSettings::tableName(), ['sip_warehouse_status_id' => $sipWarehouseStatusId], ['region_id' => $regionId]);
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(RegionSettings::tableName(), 'sip_warehouse_status_id');
    }
}
