<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\tariffs\TariffInterface;
use app\helpers\tariffs\TariffVirtpbxHelper;

/**
 * @property int $id
 */
class TariffVirtpbx extends ActiveRecord implements TariffInterface
{
    const TEST_TARIFF_ID = 42;

    public static function tableName()
    {
        return 'tarifs_virtpbx';
    }

    public function getHelper()
    {
        return new TariffVirtpbxHelper($this);
    }

}