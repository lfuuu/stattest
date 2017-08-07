<?php

namespace app\models\usages;

use app\classes\bill\Biller;
use app\classes\transfer\ServiceTransfer;
use app\helpers\usages\UsageHelperInterface;
use app\models\ClientAccount;
use app\models\LogTarif;
use app\models\tariffs\TariffInterface;
use DateTime;

/**
 * @property int $id
 * @property string $client
 * @property string $activation_dt
 * @property string $expire_dt
 * @property string $actual_from
 * @property string $actual_to
 * @property string $status
 * @property int $moved_from
 * @property int $prev_usage_id
 * @property int $next_usage_id
 *
 * @property-read LogTarif $logTariff см. LogTariffTrait
 * @property-read ClientAccount $clientAccount
 * @property-read Biller $biller
 * @property-read string $serviceType
 * @property-read TariffInterface $tariff
 * @property-read UsageHelperInterface $helper
 */
interface UsageInterface
{
    const MAX_POSSIBLE_DATE = '4000-01-01';
    const MIDDLE_DATE = '3000-01-01'; // "полпути в никуда" или "посередине ничего"
    const MIN_DATE = '1970-01-01';

    const STATUS_CONNECTING = 'connecting';
    const STATUS_WORKING = 'working';

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return Biller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount);

    /**
     * @return TariffInterface|null
     */
    public function getTariff();

    /**
     * @return string
     */
    public function getServiceType();

    /**
     * @return ClientAccount
     */
    public function getClientAccount();

    /**
     * @return UsageHelperInterface
     */
    public function getHelper();

}