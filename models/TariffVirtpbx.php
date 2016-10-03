<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\tariffs\TariffInterface;
use app\helpers\tariffs\TariffVirtpbxHelper;

/**
 * @property int $id
 * @property string status
 * @property string description
 * @property string period
 * @property string currency
 * @property float price
 * @property int num_ports
 * @property float overrun_per_port
 * @property int space
 * @property float overrun_per_gb
 * @property int ext_did_count
 * @property float ext_did_monthly_payment
 * @property int is_record
 * @property int is_web_call
 * @property int is_fax
 * @property int edit_user
 * @property string edit_time
 * @property int price_include_vat
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