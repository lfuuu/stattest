<?php
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\Number;
use app\models\Region;
use app\models\TariffVoip;
use app\modules\nnp\models\NdcType;

/**
 * Class m170524_165155_ndc_type_for_all
 */
class m170524_165155_ndc_type_for_all extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(TariffVoip::tableName(), 'ndc_type_id', $this->integer()->notNull()->defaultValue(NdcType::ID_GEOGRAPHIC));
        $this->update(TariffVoip::tableName(), ['ndc_type_id' => NdcType::ID_FREEPHONE], ['status' => [TariffVoip::STATUS_7800, TariffVoip::STATUS_7800_TEST]]);
        $this->update(DidGroup::tableName(), ['city_id' => null], ['ndc_type_id' => NdcType::ID_FREEPHONE]);
        // $this->delete(City::tableName(), ['id' => ['7800', '3680']]); // @TODO согласовать с Борисом.
        $this->addColumn(Country::tableName(), 'default_connection_point_id', $this->integer()->notNull()->defaultValue(Region::MOSCOW));
        $this->update(Country::tableName(), ['default_connection_point_id' => Region::HUNGARY], ['code' => Country::HUNGARY]);
        $this->alterColumn(Number::tableName(), 'city_id', $this->integer()->defaultValue(null));
        $this->update(Number::tableName(), ['city_id' => null], ['ndc_type_id' => NdcType::ID_FREEPHONE]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(TariffVoip::tableName(), 'ndc_type_id');

        /*
        $this->insert(City::tableName(), [
            'id' => 7800,
            'name' => '800 номера',
            'country_id' => Country::RUSSIA,
            'connection_point_id' => Region::MOSCOW,
            'voip_number_format' => '+7-800-000-00-00',
            'in_use' => 1,
            'billing_method_id' => null,
            'order' => 8,
            'is_show_in_lk' => 1,
            'postfix_length' => 7,
        ]);

        $this->insert(City::tableName(), [
            'id' => 3680,
            'name' => '80 National Toll-free (zöld számok)',
            'country_id' => Country::HUNGARY,
            'connection_point_id' => Region::HUNGARY,
            'voip_number_format' => '36 80 000-000',
            'in_use' => 1,
            'billing_method_id' => 1,
            'order' => 2,
            'is_show_in_lk' => 1,
            'postfix_length' => 6,
        ]);
        */

        $this->update(DidGroup::tableName(), ['city_id' => 7800], ['country_code' => Country::RUSSIA, 'ndc_type_id' => NdcType::ID_FREEPHONE]);
        $this->update(DidGroup::tableName(), ['city_id' => 3680], ['country_code' => Country::HUNGARY, 'ndc_type_id' => NdcType::ID_FREEPHONE]);

        $this->dropColumn(Country::tableName(), 'default_connection_point_id');

        $this->dropForeignKey('fk_voip_number__city_id', Number::tableName());
        $this->alterColumn(Number::tableName(), 'city_id', $this->integer()->notNull()->defaultValue(City::MOSCOW));
        $this->update(Number::tableName(), ['city_id' => 7800], ['ndc_type_id' => NdcType::ID_FREEPHONE, 'country_code' => Country::RUSSIA]);
        $this->update(Number::tableName(), ['city_id' => 3680], ['ndc_type_id' => NdcType::ID_FREEPHONE, 'country_code' => Country::HUNGARY]);
        $this->addForeignKey('fk_voip_number__city_id', Number::tableName(),  'city_id', City::tableName(), 'id', null, 'CASCADE');

    }
}
