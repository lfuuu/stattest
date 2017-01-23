<?php

use app\models\dictionary\PublicSite;
use app\models\dictionary\PublicSiteCountry;
use app\models\dictionary\PublicSiteCity;
use app\models\Country;
use app\models\City;

class m170118_113801_public_sites extends \app\classes\Migration
{

    /**
     * Up
     */
    public function safeUp()
    {
        $publicSiteTableName = PublicSite::tableName();
        $publicSiteCountryTableName = PublicSiteCountry::tableName();
        $publicSiteCityTableName = PublicSiteCity::tableName();

        $countryTableName = Country::tableName();
        $cityTableName = City::tableName();

        $this->createTable($publicSiteTableName, [
            'id' => $this->primaryKey(),
            'title' => $this->string(255),
            'domain' => $this->string(255),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createTable($publicSiteCountryTableName, [
            'id' => $this->primaryKey(),
            'site_id' => $this->integer(),
            'country_code' => $this->integer(4),
            'order' => $this->integer(1),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createTable($publicSiteCityTableName, [
            'public_site_country_id' => $this->integer(),
            'city_id' => $this->integer(10),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createIndex('domain', $publicSiteTableName, ['domain']);

        $this->addForeignKey(
            'fk-' . $publicSiteCountryTableName . '-country_code',
            $publicSiteCountryTableName,
            'country_code',
            $countryTableName,
            'code',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . $publicSiteCountryTableName . '-site_id',
            $publicSiteCountryTableName,
            'site_id',
            $publicSiteTableName,
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addPrimaryKey('public_site_country_id-city_id', $publicSiteCityTableName, [
            'public_site_country_id',
            'city_id',
        ]);

        $this->addForeignKey(
            'fk-' . $publicSiteCityTableName . '-public_site_country_id',
            $publicSiteCityTableName,
            'public_site_country_id',
            $publicSiteCountryTableName,
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . $publicSiteCityTableName . '-city_id',
            $publicSiteCityTableName,
            'city_id',
            $cityTableName,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PublicSiteCity::tableName());
        $this->dropTable(PublicSiteCountry::tableName());
        $this->dropTable(PublicSite::tableName());
    }

}
