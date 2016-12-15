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

    /**
     * Получить транковые контракты с типом контракта в скобках
     *
     * @param string $trunkName - фильтр по транку
     * @param bool $isWithEmpty
     * @return $this|array
     */
    public static function getListWithType ($trunkName, $isWithEmpty = false)
    {
        $list = (new Query)
            ->select(["COALESCE(st.contract_number || ' (' || cct.name || ')', st.contract_number) AS name", 'st.contract_id AS id'])
            ->from('billing.service_trunk AS st')
            ->leftJoin('client_contract_type AS cct', 'cct.id = st.contract_type_id');

        $trunkName && $list->leftJoin('auth.trunk AS t', 't.id = st.trunk_id') && $list->andWhere(['t.name' => $trunkName]);


        $list = $list->indexBy('id')->column(Yii::$app->dbPgSlave);

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}
