<?php

use app\classes\Migration;
use app\models\Region;
use app\models\dictionary\PublicSiteCountry;
use app\models\dictionary\PublicSiteNdcType;

/**
 * Class m200714_104911_public_site_ndc
 */
class m200714_104911_public_site_ndc extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(PublicSiteNdcType::tableName(),
            [
                'public_site_country_id' => $this->integer()->notNull()->defaultValue(0),
                'ndc_type_id' => $this->integer()->notNull(),
            ],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('fk-' . PublicSiteNdcType::tableName() . '-public_site_country_id',
            PublicSiteNdcType::tableName(), 'public_site_country_id',
            PublicSiteCountry::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createIndex('idx-' . Region::tableName() . '-country_id', Region::tableName(), 'country_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PublicSiteNdcType::tableName());
        $this->dropIndex('idx-' . Region::tableName() . '-country_id', Region::tableName());
    }
}
