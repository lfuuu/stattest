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

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.service_trunk';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
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
     * @param array $params
     * @param bool $isWithEmpty
     *
     * @return array
     */
    public static function getListWithName(array $params = [], $isWithEmpty = false)
    {
        $query = self::find()
            ->select(["COALESCE('(' || st.id || ') ' || t.name, t.name) AS name", 'st.id'])
            ->from('billing.service_trunk st')
            ->joinWith('trunk t', true, 'JOIN');

        if (isset($params['serverIds']) && $params['serverIds']) {
            $query->andWhere(['st.server_id' => $params['serverIds']]);
        }

        if (isset($params['contractIds']) && $params['contractIds']) {
            $query->andWhere(['st.contract_id' => $params['contractIds']]);
        }
        
        if (isset($params['trunkIds']) && $params['trunkIds']) {
            $query->andWhere(['st.trunk_id' => $params['trunkIds']]);
        }

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
