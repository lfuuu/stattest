<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TariffVirtpbx extends ActiveRecord
{

    const TEST_TARIFF_ID = 42;

    public static function tableName()
    {
        return 'tarifs_virtpbx';
    }
}