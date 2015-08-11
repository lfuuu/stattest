<?php
namespace app\models;

use app\dao\TransactionDao;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $client_account_id
 * @property string $source
 * @property string $billing_period
 * @property string $service_type
 * @property int    $service_id
 * @property int    $package_id
 * @property string $transaction_type
 * @property int    $code
 * @property string $transaction_date
 * @property string $period_from
 * @property string $period_to
 * @property string $name
 * @property float  $price
 * @property float  $amount
 * @property int    $tax_rate
 * @property float  $sum
 * @property float  $sum_tax
 * @property float  $sum_without_tax
 * @property int    $is_partial_write_off
 * @property float  $effective_amount
 * @property float  $effective_sum
 * @property int    $payment_id
 * @property int    $bill_id
 * @property int    $bill_line_id
 * @property int    $deleted
 * @property
 */
class Transaction extends ActiveRecord
{
    const SOURCE_STAT = 'stat';
    const SOURCE_BILL = 'bill';
    const SOURCE_PAYMENT = 'payment';

    const SERVICE_WELLTIME      = 'usage_welltime';
    const SERVICE_EXTRA         = 'usage_extra';
    const SERVICE_VIRTPBX       = 'usage_virtpbx';
    const SERVICE_SMS           = 'usage_sms';
    const SERVICE_EMAIL         = 'emails';
    const SERVICE_IPPORT        = 'usage_ip_ports';
    const SERVICE_VOIP          = 'usage_voip';
    const SERVICE_VOIP_PACKAGE  = 'usage_voip_package';
    const SERVICE_TRUNK         = 'usage_trunk';

    const TYPE_CONNECTING = 'connecting';
    const TYPE_PERIODICAL = 'periodical';
    const TYPE_RESOURCE   = 'resource';

    public static function tableName()
    {
        return 'transaction';
    }

    public static function dao()
    {
        return TransactionDao::me();
    }
}