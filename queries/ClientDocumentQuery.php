<?php
namespace app\queries;

use yii\db\ActiveQuery;
use app\models\ClientDocument;

/**
 * Class ClientDocumentQuery
 *
 * @method static ClientDocument find()
 * @package app\queries
 */
class ClientDocumentQuery extends ActiveQuery
{

    /**
     * @param $id
     * @return $this
     */
    public function accountId($id)
    {
        return $this->andWhere(['account_id' => $id]);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function contractId($id)
    {
        return $this->andWhere(['contract_id' => $id]);
    }

    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(["is_active" => 1]);
    }

    /**
     * @return ClientDocument
     */
    public function last()
    {
        return $this->orderBy("contract_date desc, contract_dop_date desc, id desc")->one();
    }

    /**
     * @return $this
     */
    public function contract()
    {
        return $this->andWhere(["type" => ClientDocument::DOCUMENT_CONTRACT_TYPE]);
    }

    /**
     * @return $this
     */
    public function agreement()
    {
        return $this->andWhere(["type" => ClientDocument::DOCUMENT_AGREEMENT_TYPE]);
    }

    /**
     * @return $this
     */
    public function blank()
    {
        return $this->andWhere(["type" => ClientDocument::DOCUMENT_BLANK_TYPE]);
    }

    /**
     * @param ClientDocument $contract
     * @return $this
     */
    public function fromContract(ClientDocument $contract)
    {
        return $this->andWhere([
            "contract_no" => $contract->contract_no,
            "contract_date" => $contract->contract_date
        ]);
    }



}
