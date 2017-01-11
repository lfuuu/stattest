<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель таблицы billing.service_trunk
 *
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
     * @param int $serverId - фильтр по серверу
     * @param string $trunkName - фильтр по контракту
     * @param bool $isWithEmpty
     *
     * @return array
     */
    public static function getListWithName($serverId = null, $trunkName = null, $isWithEmpty = false)
    {
        $query = self::find()
            ->select(['t.trunk_name AS id', "COALESCE('(' || st.id || ') ' || t.name, t.name) AS name"])
            ->from('billing.service_trunk st')
            ->joinWith('trunk t', true, 'RIGHT JOIN');

        $serverId && $query->andWhere(['t.server_id' => $serverId]);
        $trunkName && $query->andWhere(['t.name' => $trunkName]);

        $list = $query
            ->indexBy('name')
            ->orderBy(['st.id' => SORT_ASC])
            ->column(Yii::$app->dbPgSlave);

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}
