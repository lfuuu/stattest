<?php
namespace app\dao\billing;

use app\classes\Singleton;
use app\models\billing\Trunk;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\UsageTrunk;
use yii\db\Query;

/**
 * @method static TrunkDao me($args = null)
 * @property
 */
class TrunkDao extends Singleton
{

    /**
     * Вернуть список всех доступных моделей
     *
     * @param int $serverId
     * @param bool $isWithEmpty
     * @return Trunk[]
     */
    public function getList($serverId = null, $isWithEmpty = false)
    {
        $query = Trunk::find();
        $serverId && $query->where(['server_id' => $serverId]);
        $list = $query
            ->andWhere(['show_in_stat' => true])
            ->orderBy(['name' => SORT_ASC])
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
