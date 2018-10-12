<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\TrunkDao;
use Yii;

/**
 * @property int $id
 * @property int $server_id
 * @property int $code
 * @property string $name
 * @property bool $source_rule_default_allowed
 * @property bool $destination_rule_default_allowed
 * @property int $default_priority
 * @property bool $auto_routing
 * @property int $route_table_id
 * @property bool $our_trunk
 * @property string $trunk_name
 * @property string $trunk_name_alias
 * @property bool $auth_by_number
 * @property bool $show_in_stat
 * @property bool $orig_redirect_number
 * @property bool $term_redirect_number
 * @property bool $source_trunk_rule_default_allowed
 * @property bool $orig_redirect_number_7800
 * @property int $capacity
 * @property bool $sw_minimalki
 * @property bool $sw_shared
 * @property int $load_warning
 * @property bool $tech_trunk
 * @property string $road_to_regions
 * @property bool $pstn_trunk
 * @property bool $mgmn_trunk
 * @property bool $orig_afilter_default_allowed
 * @property bool $orig_bfilter_default_allowed
 * @property bool $term_afilter_default_allowed
 * @property bool $term_bfilter_default_allowed
 * @property bool $mgmn_orig_trunk
 * @property bool $orig_cfilter_default_allowed
 * @property bool $term_cfilter_default_allowed
 * @property bool $le8accept
 * @property int $id_pbx
 * @property string $back_trunk
 * @property string $trace_to_regions
 * @property int $location_id
 * @property bool $roaming_orig
 * @property bool $roaming_term
 * @property bool $mgmn2_orig
 * @property bool $mgmn2_term
 *
 * @property-read TrunkAbfiltersRule $trunkAbfiltersRules
 */
class Trunk extends ActiveRecord
{
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
     *
     * @return array [полеВТаблице => Перевод]
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

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * @return TrunkDao
     */
    public static function dao()
    {
        return TrunkDao::me();
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrunkAbfiltersRules()
    {
        return $this->hasMany(TrunkAbfiltersRule::class, ['trunk_id' => 'id']);
    }

    /**
     * Отображение Номер A/B с терминацией/оригинацией в bool флагом - allow
     *
     * @param array $items
     * @param bool $orig
     * @param bool $outgoing
     * @return string
     */
    public static function graphicDistributionOfRules($items, $orig, $outgoing)
    {
        return array_reduce($items, function($carry, $item) use ($orig, $outgoing) {
            if ($item['orig'] === $orig && $item['outgoing'] === $outgoing) {
                $class = $item['allow'] ? 'allowed' : 'disallowed';
                $carry .= "<div class='{$class}'><b>{$item['name']}</b></div><br>";
            }
            return $carry;
        }, '');
    }

    /**
     * Получение массива связанных данных с транком: правила и префикс-листы
     *
     * @param array $ids
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getRulesAndPrefixlistRelations($ids)
    {
        return self::find()
            ->alias('t')
            ->select(['t.id', 'tar.orig', 'tar.outgoing', 'tar.allow', 'p.name'])
            ->leftJoin(['tar' => TrunkAbfiltersRule::tableName()], 't.id = tar.trunk_id')
            ->innerJoin(['p' => Prefixlist::tableName()], 'tar.prefixlist_id = p.id')
            ->where(['t.id' => $ids])
            ->asArray()
            ->all();
    }

    /**
     * Перестроение структуры, полученный из функции getRulesAndPrefixlistRelations
     *
     * @see Trunk::getRulesAndPrefixlistRelations
     * @param array $items
     * @return array
     */
    public static function restructRulesAndPrefixlistRelations($items)
    {
        return array_reduce($items, function($carry, $item) {
            $carry[$item['id']][] = $item;
            return $carry;
        }, []);
    }
}