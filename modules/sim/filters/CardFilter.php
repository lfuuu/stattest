<?php

namespace app\modules\sim\filters;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\Number;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use http\Client;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Card
 */
class CardFilter extends Card
{
    public $iccid = '';
    public $imei = '';
    public $client_account_id = '';
    public $is_active = '';
    public $status_id = '';

    public $imsi = '';
    public $msisdn = '';
    public $did = '';
    public $imsi_partner = '';
    public $profile_id = '';
    public $entry_point_id = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['iccid', 'imei', 'client_account_id', 'is_active', 'status_id', 'profile_id', 'entry_point_id'], 'integer'], // card
            [['imsi', 'msisdn', 'did', 'imsi_partner'], 'integer'], // imsi
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $cardTableName = Card::tableName();
        $imsiTableName = Imsi::tableName();

        $query = Card::find()
            ->with('imsies')
            ->joinWith('imsies');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if ($this->iccid) {
            if (strpos($this->iccid, '*') !== false) {
                $iccid = strtr($this->iccid, ['**' => '*', '*' => '%']);
                $query->andWhere($cardTableName . '.iccid::varchar like :like', [':like' => $iccid]);
            } else {
                $query->andWhere([$cardTableName . '.iccid' => $this->iccid]);
            }
        }

        $this->imei && $query->andWhere([$cardTableName . '.imei' => $this->imei]);
        $this->client_account_id && $query->andWhere([$cardTableName . '.client_account_id' => $this->client_account_id]);
        $this->is_active && $query->andWhere([$cardTableName . '.is_active' => $this->is_active]);
        $this->status_id && $query->andWhere([$cardTableName . '.status_id' => $this->status_id]);

        $this->imsi && $query->andWhere([$imsiTableName . '.imsi' => $this->imsi]);
        $this->msisdn && $query->andWhere([$imsiTableName . '.msisdn' => $this->msisdn]);
        $this->did && $query->andWhere([$imsiTableName . '.did' => $this->did]);
        $this->imsi_partner && $query->andWhere([$imsiTableName . '.partner_id' => $this->imsi_partner]);
        $this->profile_id && $query->andWhere([$imsiTableName . '.profile_id' => $this->profile_id]);

        if ($this->entry_point_id) {
            $queryAccount = clone $query;
            $accountIds = $queryAccount->select('client_account_id')->distinct()->column();

            $accountIds = ClientAccount::find()->alias('c')->joinWith('superClient')->where(['c.id' => $accountIds, 'entry_point_id' => $this->entry_point_id])->column();
            $query->andWhere([$cardTableName . '.client_account_id' => $accountIds]);
        }

        return $dataProvider;
    }

    public function actionSetStatus($iccids, $statusId)
    {
        $transaction = Card::getDb()->beginTransaction();
        try {

            $cardStatus = CardStatus::findOne(['id' => $statusId]);
            if (!$cardStatus) {
                throw new \LogicException('Неизвестный статус');
            }

            $counter = 0;
            foreach ($iccids as $iccid) {
                $card = Card::findOne(['iccid' => $iccid]);

                if (!$card) {
                    throw new \LogicException('не найдена карта: iccid: ' . $iccid);
                }

                if ($card->status_id == $statusId) {
                    continue;
                }

                $card->status_id = $statusId;

                if (!$card->save()) {
                    throw new ModelValidationException($card);
                }
                $counter++;
            }
            $transaction->commit();
            \Yii::$app->session->addFlash('success', 'Изменени статус на: ' . $cardStatus->name . ', кол-во карт: ' . $counter);
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }
    }

    public function actionSetUnLink($iccids)
    {
        $transaction = Card::getDb()->beginTransaction();
        try {

            $counter = 0;
            foreach ($iccids as $iccid) {
                $card = Card::findOne(['iccid' => $iccid]);

                if (!$card) {
                    throw new \LogicException('не найдена карта: iccid: ' . $iccid);
                }

                if ($card->client_account_id == null) {
                    continue;
                }

                $card->client_account_id = null;

                if (!$card->save()) {
                    throw new ModelValidationException($card);
                }
                $counter++;
            }
            $transaction->commit();
            \Yii::$app->session->addFlash('success', 'Отвязано карт: ' . $counter);
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }
    }

    public function actionSetLink($iccids, $accountId)
    {
        $transaction = Card::getDb()->beginTransaction();
        try {

            $counter = 0;
            foreach ($iccids as $iccid) {
                $card = Card::findOne(['iccid' => $iccid]);

                if (!$card) {
                    throw new \LogicException('не найдена карта: iccid: ' . $iccid);
                }

                if ($card->client_account_id) {
                    throw new \LogicException('Карта ' . $iccid . ' уже привязана');
                }

                if ($card->client_account_id == $accountId) {
                    continue;
                }

                $card->client_account_id = $accountId;

                if (!$card->save()) {
                    throw new ModelValidationException($card);
                }
                $counter++;
            }
            $transaction->commit();
            \Yii::$app->session->addFlash('success', 'Привязано к УЛС ' . $accountId . ' карт: ' . $counter);
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }
    }

}
