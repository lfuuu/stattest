<?php
namespace app\queries;

use yii\db\ActiveQuery;
use app\models\ClientDocument;

class ClientDocumentQuery extends ActiveQuery
{
    public function account($accountId)
    {
        return $this->andWhere(["client_id" => $accountId]);
    }

    public function active()
    {
        return $this->andWhere(["is_active" => 1]);
    }

    public function last()
    {
        return $this->orderBy("contract_date desc, contract_dop_date desc, id desc")->one();
    }

    public function contract()
    {
        return $this->andWhere(["type" => "contract"]);
    }

    public function agreement()
    {
        return $this->andWhere(["type" => "agreement"]);
    }

    public function blank()
    {
        return $this->andWhere(["type" => "blank"]);
    }

    public function fromContract(ClientDocument $contract)
    {
        return $this->andWhere(["contract_no" => $contract->contract_no, "contract_date" => $contract->contract_date]);
    }



}
