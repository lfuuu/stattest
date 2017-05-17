<?php

use app\models\City;

/**
 * Class m170517_104746_city_postfix_length
 */
class m170517_104746_city_postfix_length extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(City::tableName(), 'postfix_length', $this->integer()->notNull()->defaultValue(6));

        City::updateAll(['postfix_length' => 7], [
            'id' => [
                City::MOSCOW,
                361, // Budapest
                3621, // Non-geografic number
                7343, // Екатеринбург
                7342, // Пермь
                7347, // Уфа
                7351, // Челябинск
                7383, // Новосибирск
                7473, // Воронеж
                7800, // 800 номера
                7812, // Санкт-Петербург
                7831, // Нижний Новгород
                7843, // Казань
                7846, // Самара
                7861, // Краснодар
                7862, // Сочи
                7863, // Ростов-на-Дону
            ]
        ]);

        City::updateAll(['postfix_length' => 8], [
                'id' => [
                    49, // Вся Германия
                    100594, // Frankfurt am Main 8
                ]
            ]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(City::tableName(), 'postfix_length');
    }
}
