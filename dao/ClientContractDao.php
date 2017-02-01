<?php
namespace app\dao;

use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContract;
use app\models\ClientDocument;
use yii\db\Query;
use Yii;

class ClientContractDao extends Singleton
{

    /**
     * Получить транковые контракты с типом контракта в скобках
     *
     * @param array $params
     * @param bool $isWithEmpty
     *
     * @return string[]
     */
    public static function getListWithType(array $params = [], $isWithEmpty = false)
    {
        $query = (new Query)
            ->select(
                [
                    'name' => "COALESCE(st.contract_number || ' (' || cct.name || ')', st.contract_number)",
                    'id' => 'st.contract_id',
                ]
            )
            ->from('billing.service_trunk AS st')
            ->leftJoin('stat.client_contract_type AS cct', 'cct.id = st.contract_type_id')
            ->orderBy('name DESC');

        if (isset($params['serverIds']) && $params['serverIds']) {
            $query->andWhere(['st.server_id' => $params['serverIds']]);
        }

        if (isset($params['serviceTrunkIds']) && $params['serviceTrunkIds']) {
            $query->andWhere(['st.id' => $params['serviceTrunkIds']]);
        }

        if (isset($params['trunkIds']) && $params['trunkIds']) {
            $query->andWhere(['st.trunk_id' => $params['trunkIds']]);
        }

        $list = $query->indexBy('id')->column(Yii::$app->dbPgSlave);

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

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

        if (!$contractDoc) {
            $contractDoc = ClientDocument::find()
                ->contract()
                ->andWhere(['contract_id' => $contract->id])
                ->andWhere(['<=', 'contract_date', $date->format(DateTimeZoneHelper::DATETIME_FORMAT)])
                ->last();
        }

        return $contractDoc;
    }
}
