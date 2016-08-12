<?php
namespace app\models\usages;

use DateTime;
use app\classes\transfer\ServiceTransfer;
use app\helpers\usages\UsageHelperInterface;
use app\classes\bill\Biller;
use app\models\ClientAccount;

/**
 * @property int $id
 * @property string client
 * @property string activation_dt
 * @property string expire_dt
 * @property string actual_from
 * @property string actual_to
 * @property string status
 * @property int moved_from
 * @property int prev_usage_id
 * @property int next_usage_id
 */
interface UsageInterface
{
    const MAX_POSSIBLE_DATE = '4000-01-01';
    const MIDDLE_DATE = '3000-01-01'; // "полпути в никуда" или "посередине ничего"
    const MIN_DATE = '1970-01-01';

    /**
     * @return Biller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount);

    public function getTariff();

    public function getServiceType();

    /**
     * @param UsageInterface $usage
     * @return ServiceTransfer
     */
    public static function getTransferHelper($usage);

    /**
     * @return ClientAccount
     */
    public function getClientAccount();

    /**
     * @return UsageHelperInterface
     */
    public function getHelper();
}