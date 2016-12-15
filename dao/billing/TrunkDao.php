<?php
namespace app\dao\billing;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\classes\Singleton;
use app\models\billing\Trunk;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\UsageTrunk;

/**
 * @method static TrunkDao me($args = null)
 * @property
 */
class TrunkDao extends Singleton
{

    /**
     * @param int|false $serverId
     * @return []
     */
    public function getList($serverId = false)
    {
        $query = Trunk::find();

        if ($serverId !== false) {
            $query->andWhere(['server_id' => $serverId]);
        }

        $query->andWhere('show_in_stat = true');

        return
            ArrayHelper::map(
                $query
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
    }

    /**
     * @return []
     */
    public function getListAll()
    {
        $query = Trunk::find();
        return
            ArrayHelper::map(
                $query
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
    }

    /**
     * @param int $trunkId
     * @param int $connectionPointId
     * @return ActiveRecord[]
     */
    public function getContragents($trunkId = 0, $connectionPointId = 0)
    {
        if (!$trunkId && !$connectionPointId) {
            return [];
        }

        $query =
            (new Query)
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

    /**
     * Получить список транков с идентификатором логического транка
     *
     * @param $serverId
     * @param $operatorId
     * @param $contractId
     * @param bool $isWithEmpty
     * @return $this|array
     */
    public static function getListWithName($serverId, $operatorId, $contractId, $isWithEmpty = false)
    {
        $list = (new Query)
            ->select(['t.name AS id', "COALESCE('(' || st.id || ') ' || t.name, t.name) AS name"])
            ->from('billing.service_trunk AS st')
            ->rightJoin('auth.trunk AS t', 't.id = st.trunk_id')
            ->orderBy('st.id ASC');

        $serverId && $list->andWhere(['t.server_id' => $serverId]);
        $operatorId && $list->andWhere(['st.operator_id' => $operatorId]);
        $contractId && $list->andWhere(['st.contract_id' => $contractId]);

        $list = $list->all(Yii::$app->dbPgSlave);

        $list = ArrayHelper::map(
            $list,
            'id',
            'name'
        );

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}
