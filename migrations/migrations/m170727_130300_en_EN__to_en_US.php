<?php
use app\models\ClientAccountOptions;
use app\models\ClientContragent;
use app\models\Country;
use app\models\HistoryChanges;
use app\models\HistoryVersion;
use app\models\Language;
use app\modules\notifier\models\templates\TemplateContent;

/**
 * Class m170727_130300_en_EN__to_en_US
 */
class m170727_130300_en_EN__to_en_US extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(Language::tableName(), ['code' => 'en-US'], ['code' => 'en-EN']);
        $this->update(ClientAccountOptions::tableName(), ['value' => 'en-US'], ['value' => 'en-EN']);
        $this->update(ClientContragent::tableName(), ['lang_code' => 'en-US'], ['lang_code' => 'en-EN']);
        $this->update(Country::tableName(), ['lang' => 'en-US'], ['lang' => 'en-EN']);
        $this->update(TemplateContent::tableName(), ['lang_code' => 'en-US'], ['lang_code' => 'en-EN']);

        $this->update(HistoryChanges::tableName(),
            ['data_json' => new \yii\db\Expression('replace(data_json, \'"en-EN"\', \'"en-US"\')')],
            ['like', 'data_json', 'en-EN']
        );

        $this->update(HistoryChanges::tableName(),
            ['prev_data_json' => new \yii\db\Expression('replace(prev_data_json, \'"en-EN"\', \'"en-US"\')')],
            ['like', 'prev_data_json', 'en-EN']
        );

        $this->update(HistoryVersion::tableName(),
            ['data_json' => new \yii\db\Expression('replace(data_json, \'"en-EN"\', \'"en-US"\')')],
            ['like', 'data_json', 'en-EN']
        );

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(Language::tableName(), ['code' => 'en-EN'], ['code' => 'en-US']);
        $this->update(ClientAccountOptions::tableName(), ['value' => 'en-EN'], ['value' => 'en-US']);
        $this->update(ClientContragent::tableName(), ['lang_code' => 'en-EN'], ['lang_code' => 'en-US']);
        $this->update(Country::tableName(), ['lang' => 'en-EN'], ['lang' => 'en-US']);
        $this->update(TemplateContent::tableName(), ['lang_code' => 'en-EN'], ['lang_code' => 'en-USN']);

        $this->update(HistoryChanges::tableName(),
            ['data_json' => new \yii\db\Expression('replace(data_json, \'"en-US"\', \'"en-EN"\')')],
            ['like', 'data_json', 'en-US']
        );

        $this->update(HistoryChanges::tableName(),
            ['prev_data_json' => new \yii\db\Expression('replace(prev_data_json, \'"en-US"\', \'"en-EN"\')')],
            ['like', 'prev_data_json', 'en-US']
        );

        $this->update(HistoryVersion::tableName(),
            ['data_json' => new \yii\db\Expression('replace(data_json, \'"en-US"\', \'"en-EN"\')')],
            ['like', 'data_json', 'en-US']
        );

    }
}
