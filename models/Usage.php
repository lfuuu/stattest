<?php
namespace app\models;

use app\classes\transfer\ServiceTransfer;
use DateTime;
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
    public function getTransferHelper();

    /**
     * @return ClientAccount
     */
    public function getClientAccount();

    public static function getTypeTitle();
    public static function getTypeHelpBlock();
    public function getTypeDescription();

}