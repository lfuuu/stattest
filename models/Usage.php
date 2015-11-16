<?php
namespace app\models;

use DateTime;
use app\classes\transfer\ServiceTransfer;
use app\classes\usages\UsageHelperInterface;
use app\classes\bill\Biller;

/**
 * @property int $id
 * @property
 */
interface Usage
{
    const MAX_POSSIBLE_DATE = '4000-01-01';

    /**
     * @return Biller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount);

    public function getTariff();

    public function getServiceType();

    /**
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