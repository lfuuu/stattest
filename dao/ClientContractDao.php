<?php
namespace app\dao;

use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContract;
use app\models\ClientDocument;

class ClientContractDao extends Singleton
{

    /**
     * @param ClientContract $contract
     * @param \DateTime|null $date
     * @return null|\app\models\ClientDocument
     */
    public function getContractInfo(ClientContract $contract, \DateTime $date = null)
    {
        if (!$date) {
            $date = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));
        }

        $contractDoc = ClientDocument::find()
            ->active()
            ->contract()
            ->andWhere(['contract_id' => $contract->id])
            ->andWhere(['<=', 'contract_date', $date->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->last();

        return $contractDoc;
    }

}
