<?php
use app\models\City;
use app\models\Number;
use app\models\voip\Registry;
use app\modules\nnp\models\NumberRange;

/**
 * Class m170405_140627_registry_ndc
 */
class m170405_140627_registry_ndc extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Registry::tableName(), 'ndc', $this->integer());
        $this->addColumn(Registry::tableName(), 'number_full_from', $this->string()->notNull()->defaultValue(''));
        $this->addColumn(Registry::tableName(), 'number_full_to', $this->string()->notNull()->defaultValue(''));
        $this->update(Registry::tableName(), ['city_id' => 7861], ['like', 'number_from', '7861%', $isEscape = false]);
        $this->update(Registry::tableName(), ['city_id' => 3680], ['like', 'number_from', '3680%', $isEscape = false]);
        $this->insert(City::tableName(), [
            'id' => 100594,
            'name' => 'Frankfurt am Main',
            'country_id' => 276,
            'connection_point_id' => 82,
            'voip_number_format' => '49 69 0000-0000',
            'in_use' => 1,
            'billing_method_id' => null,
            'order' => 0,
            'is_show_in_lk' => 0
        ]);
        $this->update(Number::tableName(), ['city_id' => 100594], ['city_id' => 49]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Registry::tableName(), 'ndc');
        $this->dropColumn(Registry::tableName(), 'number_full_from');
        $this->dropColumn(Registry::tableName(), 'number_full_to');
        $this->update(Number::tableName(), ['city_id' => 49], ['city_id' => 100594]);
        $this->delete(City::tableName(), ['id' => 100594]);
    }
}
