<?php
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\Number;

/**
 * Class m170418_111434_del_did_group
 */
class m170418_111434_del_did_group extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->delete(DidGroup::tableName(), [
            'AND',
            ['country_code' => Country::RUSSIA],
            ['not', ['city_id' => City::MOSCOW]],
            ['IS NOT', 'city_id', null]
            ]
        );

        $this->delete(DidGroup::tableName(), [
            'and',
            ['country_code' => Country::HUNGARY],
            ['IS NOT', 'city_id', null]
            ]
        );

        $this->update(DidGroup::tableName(),
            ['city_id' => null],
            ['country_code' => Country::GERMANY]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // nothing
    }
}
