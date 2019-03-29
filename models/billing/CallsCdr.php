<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 *
 * @property int $server_id integer NOT NULL
 * @property int $id bigint NOT NULL
 * @property int $call_id bigint
 * @property string $nas_ip inet
 * @property string $src_number varchar
 * @property string $dst_number varchar
 * @property string $redirect_number varchar
 * @property string $setup_time timestamp without time zone
 * @property string $connect_time timestamp without time zone
 * @property string $disconnect_time timestamp without time zone
 * @property string $session_time timestamp without time zone
 * @property int $disconnect_cause smallint
 * @property string $src_route varchar
 * @property string $dst_route varchar
 *
 * @property int $hub_id
 * @property string $mcn_callid varchar
 *
 * @property-read Server $server
 */
class CallsCdr extends ActiveRecord
{

    /**
     * Вернуть имена полей
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'server_id' => 'Регион (точка подключения)',
            'id' => 'ID',
            'call_id' => 'Звонок',
            'nas_ip' => '',
            'src_number' => 'Исходящий №',
            'dst_number' => 'Входящий №',
            'redirect_number' => 'Переадресация на №',
            'setup_time' => '',
            'connect_time' => 'Время начала разговора (UTC)',
            'disconnect_time' => 'Время окончания разговора (UTC)',
            'session_time' => 'Длительность, сек.',
            'disconnect_cause' => 'Код завершения',
            'src_route' => 'Транк-оригинатор',
            'dst_route' => 'Транк-терминатор',
            // ...
            'hub_id' => 'Хаб',
            'mcn_callid' => 'Уникальный идентификатор звонка',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'calls_cdr.cdr';
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
     * @return ActiveQuery
     */
    public function getServer()
    {
        return $this->hasOne(Server::class, ['id' => 'server_id']);
    }

    /**
     * Получаем уникальный ключ отчета
     *
     * @param Query $query
     * @return string
     */
    public static function getCacheKey(Query $query)
    {
        return 'calls_cdrs_cache_' . md5($query->createCommand(self::getDb())->rawSql);
    }
}
