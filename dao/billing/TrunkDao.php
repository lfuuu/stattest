<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\Trunk;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\UsageTrunk;
use yii\db\Query;
use Yii;

/**
 * @method static TrunkDao me($args = null)
 */
class TrunkDao extends Singleton
{

    /**
     * Вернуть список всех доступных значений
     *
     * @param array $params
     * @param bool $isWithEmpty
     * @return Trunk[]
     */
    public function getList(array $params = [], $isWithEmpty = false)
    {
        $query = Trunk::find();

        if (isset($params['serverIds']) && $params['serverIds']) {
            $query->andWhere(['t.server_id' => $params['serverIds']]);
        }

        if (
            (isset($params['serviceTrunkIds']) && $params['serviceTrunkIds']) ||
            (isset($params['contractIds']) && $params['contractIds']) ||
            (isset($params['accountId']) && $params['accountId'])
        ) {
            $query->leftJoin(['st' => 'billing.service_trunk'], 'st.trunk_id = t.id');
        }

        if (isset($params['serviceTrunkIds']) && $params['serviceTrunkIds']) {
            $query->andWhere(['st.id' => $params['serviceTrunkIds']]);
        }

        if (isset($params['contractIds']) && $params['contractIds']) {
            $query->andWhere(['st.contract_id' => $params['contractIds']]);
        }

        if (isset($params['accountId']) && $params['accountId']) {
            $query->andWhere(['st.client_account_id' => $params['accountId']]);
        }

        if (!isset($params['showInStat']) || (isset($params['showInStat']) && $params['showInStat'])) {
            $query->andWhere(['show_in_stat' => true]);
        }

        $list = $query
            ->select('t.*')
            ->from('auth.trunk t')
            ->orderBy(['t.name' => SORT_ASC])
            ->indexBy('id')
            ->all();

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * @param int $trunkId
     * @param int $connectionPointId
     * @return array
     */
    public function getContragents($trunkId = 0, $connectionPointId = 0)
    {
        if (!$trunkId && !$connectionPointId) {
            return [];
        }

        $query = (new Query)
            ->select([
                'client_account_id' => 'clients.id',
                'id' => 'contragents.id',
                'name' => 'contragents.name',
            ])
            ->from([
                'trunks' => UsageTrunk::tableName()
            ])
            ->leftJoin(
                ['clients' => ClientAccount::tableName()],
                'clients.id = trunks.client_account_id'
            )
            ->leftJoin(
                ['contracts' => ClientContract::tableName()],
                'contracts.id = clients.contract_id'
            )
            ->leftJoin(
                ['contragents' => ClientContragent::tableName()],
                'contragents.id = contracts.contragent_id'
            )
            ->groupBy('trunks.client_account_id');

        $trunkId !== '' && $query->andWhere(['trunks.trunk_id' => $trunkId]);
        $connectionPointId !== '' && $query->andWhere(['trunks.connection_point_id' => $connectionPointId]);

        return $query->all();
    }
}
