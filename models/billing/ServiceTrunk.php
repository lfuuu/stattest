<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $server_id
 * @property int $client_account_id
 * @property int $trunk_id
 * @property string $activation_dt
 * @property string $expire_dt
 * @property bool $orig_enabled
 * @property bool $term_enabled
 * @property float $orig_min_payment
 * @property float $term_min_payment
 * @property int $operator_id
 * @property int $contract_id
 * @property string $contract_number
 * @property int $contract_type_id
 *
 * @property Trunk $trunk
 *
 * Class ServiceTrunk
 * @package app\models\billing
 */
class ServiceTrunk extends ActiveRecord
{
    use \app\classes\traits\GetListTrait;
    
    public static function tableName()
    {
        return 'billing.service_trunk';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrunk()
    {
        return $this->hasOne(Trunk::className(), ['id' => 'trunk_id']);
    }

    /**
     * Получить список транков с идентификатором логического транка
     *
     * @param int $serverId
     * @param int $operatorId
     * @param int $contractId
     * @param bool $isWithEmpty
     * @return array
     */
    public static function getListWithName($serverId, $operatorId, $contractId, $isWithEmpty = false)
    {
        $query = self::find()
            ->select(["COALESCE('(' || st.id || ') ' || t.name, t.name) AS name", 't.name AS id'])
            ->from('billing.service_trunk st')
            ->joinWith('trunk t', true, 'RIGHT JOIN');

        $serverId && $query->andWhere(['t.server_id' => $serverId]);
        $operatorId && $query->andWhere(['st.operator_id' => $operatorId]);
        $contractId && $query->andWhere(['st.contract_id' => $contractId]);

        $list = $query
            ->indexBy('id')
            ->orderBy(['st.id' => SORT_ASC])
            ->column(Yii::$app->dbPgSlave);

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}