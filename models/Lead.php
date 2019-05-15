<?php

namespace app\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use yii\base\InvalidParamException;
use yii\helpers\Url;

/**
 * Class Lead
 *
 * @property integer $id
 * @property string $message_id
 * @property integer $trouble_id
 * @property integer $account_id
 * @property string $created_at
 * @property string $data_json
 * @property integer $state_id
 * @property integer $sale_channel_id
 * @property string $did
 * @property string $did_mcn
 *
 * @property-read ClientAccount $account
 * @property-read Trouble $trouble
 * @property-read array data
 */
class Lead extends ActiveRecord
{
    const DEFAULT_ENTRY_POINT = EntryPoint::RF_CRM;
    const DEFAULT_ACCOUNT_ID = 50124;
    const TRASH_ACCOUNT_ID = 50125;

    const TRASH_TYPE_SPAM = 'Спам';
    const TRASH_TYPE_HOOLIGANS = 'Хулиганы';
    const TRASH_TYPE_NUMBER_MISTAKEN = 'Ошиблись номером';
    const TRASH_TYPE_OTHER = 'Другое';

    public static function getTrashTypes()
    {
        return [
            static::TRASH_TYPE_SPAM,
            static::TRASH_TYPE_HOOLIGANS,
            static::TRASH_TYPE_NUMBER_MISTAKEN,
            static::TRASH_TYPE_OTHER,
        ];
    }

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'lead';
    }

    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::class,
        ];
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return json_decode($this->data_json, true);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrouble()
    {
        return $this->hasOne(Trouble::class, ['id' => 'trouble_id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to([
            '/lead/view',
            'messageId' => $this->message_id
        ]);
    }

    /**
     * Перемещение лида на другогй ЛС
     *
     * @param integer $clientAccountId
     * @throws \Exception
     */
    public function moveToClientAccount($clientAccountId)
    {
        if ($this->account_id == $clientAccountId) {
            return;
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $this->account_id = $clientAccountId;

            if (!$this->save()) {
                throw new ModelValidationException($this);
            }

            $trouble = $this->trouble;

            if (!$trouble) {
                throw new InvalidParamException('Лид-заявка не найдена');
            }

            $trouble->client = $this->account->client;

            if (!$trouble->save()) {
                throw new ModelValidationException($trouble);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

}
