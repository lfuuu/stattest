<?php

namespace app\dao;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use yii\base\InvalidParamException;

/**
 * @method static CardDao me($args = null)
 */
class CardDao extends Singleton
{
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

            $iccids = array_unique($iccids);

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

    public function actionSetTransfer($iccids, $accountId, $newAccountId)
    {
        $transaction = Card::getDb()->beginTransaction();
        try {
            $counter = 0;
            foreach ($iccids as $iccid) {
                $card = Card::findOne(['iccid' => $iccid]);

                if (!$card) {
                    throw new InvalidParamException('не найдена карта: iccid: ' . $iccid);
                }

                $card->client_account_id = $newAccountId;
                if (!$card->save()) {
                    throw new ModelValidationException($card);
                }
                $counter++;
            }
            $transaction->commit();
            \Yii::$app->session->addFlash('success', 'Успешно перенесено с УЛС ' . $accountId . ' на УЛС' . $newAccountId . ' карт: ' . $counter);
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }
    }
}