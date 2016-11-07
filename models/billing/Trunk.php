<?php
namespace app\models\billing;

use app\dao\billing\TrunkDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int id
 * @property string name
 * @property int server_id
 * @property int code
 */
class Trunk extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const TRUNK_DIRECTION_ORIG = 'orig_enabled';
    const TRUNK_DIRECTION_TERM = 'term_enabled';
    const TRUNK_DIRECTION_BOTH = 'both_enabled'; // Только для условий

    public static $trunkTypes = [
        self::TRUNK_DIRECTION_BOTH => 'Ориг. / Терм.',
        self::TRUNK_DIRECTION_ORIG => 'Ориг.',
        self::TRUNK_DIRECTION_TERM => 'Терм.',
    ];

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'server_id' => 'Сервер',
            'code' => 'Код',
        ];
    }

    public static function tableName()
    {
        return 'auth.trunk';
    }

    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    public static function dao()
    {
        return TrunkDao::me();
    }

    /**
     * Вернуть список всех доступных моделей
     * @param int $serverId
     * @param bool $isWithEmpty
     * @return self[]
     */
    public static function getList($serverId = null, $isWithEmpty = false)
    {
        $query = self::find();
        $serverId && $query->where(['server_id' => $serverId]);
        $list = $query->orderBy(self::getListOrderBy())
            ->indexBy('id')
            ->all();

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}