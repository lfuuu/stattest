<?php
namespace app\models;

use app\classes\transfer\Transfer;
use DateTime;
use app\classes\bill\Biller;

/**
 * @property int $id
 * @property
 */
interface Usage
{
    /**
     * @return Biller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount);

    public function getTariff();

    public function getServiceType();

    /**
     * @return Transfer
     */
    public function getTransferHelper();

    /**
     * @return ClientAccount
     */
    public function getClientAccount();

}