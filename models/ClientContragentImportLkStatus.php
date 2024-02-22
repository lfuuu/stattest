<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $contragent_id
 * @property string $updated_at
 * @property string $status_code
 * @property string $status_text
 */
class ClientContragentImportLkStatus extends ActiveRecord
{
    /**
     * Возвращает название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_contragent_import_lk_status';
    }

    /**
     * Правила
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            [['contragent_id', 'status_code'], 'required'],
            ['contragent_id', 'integer'],
            ['status_code', 'string'],
        ];

        return $rules;
    }

    /**
     * Поведение
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'updated_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('UTC_TIMESTAMP()'), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
        ];
    }

    public static function set($contragentId, $statusCode = 'ok', $statusText = ''): self
    {
        $status = self::findOne(['contragent_id' => $contragentId]);
        if (!$status) {
            $status = new self;
            $status->contragent_id = $contragentId;
        }
        $status->status_code = $statusCode;
        $status->status_text = $statusText;

        if(!$status->save()) {
            throw new ModelValidationException($status);
        }

        return $status;
    }

}
