<?php
namespace app\models\usages;

use DateTime;
use app\classes\transfer\ServiceTransfer;
use app\helpers\usages\UsageHelperInterface;
use app\classes\bill\Biller;
use app\models\ClientAccount;

/**
 * @property int $id
 * @property
 */
interface UsageInterface
{
    const MAX_POSSIBLE_DATE = '4000-01-01';

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