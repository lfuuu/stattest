<?php

namespace app\controllers;

use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\forms\client\ClientCreateExternalForm;
use app\models\ClientAccount;
use app\models\Lead;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Response;


class LeadController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actionIndex()
    {
        throw new \BadMethodCallException('Нет действия');
    }

    /**
     * Создать клиента
     *
     * @param string $messageId
     * @return Response
     */
    public function actionMakeClient($messageId)
    {
        $lead = $this->_getLeadByMessageId($messageId);

        return $this->_makeClient($lead);
    }

    /**
     * Создать лид
     *
     * @param string $messageId
     * @param integer $clientAccountId
     * @return mixed
     */
    public function actionToLead($messageId, $clientAccountId)
    {
        $lead = $this->_getLeadByMessageId($messageId);

        return $this->_toLead($lead, $clientAccountId);
    }

    /**
     * Переместить лид в мусор
     *
     * @param string $messageId
     * @return Response
     */
    public function actionToTrash($messageId)
    {
        $lead = $this->_getLeadByMessageId($messageId);

        return $this->_toTrashClient($lead);
    }

    /**
     * Сменить стуатус лида
     *
     * @param string $messageId
     * @param integer $stateId
     * @param integer $clientAccountId
     * @return Response
     */
    public function actionSetState($messageId, $stateId, $clientAccountId)
    {
        $lead = $this->_getLeadByMessageId($messageId);

        return $this->_setState($lead, $stateId, $clientAccountId);
    }

    /**
     * Просмотр лида
     *
     * @param string $messageId
     * @return Response
     */
    public function actionView($messageId)
    {
        $lead = $this->_getLeadByMessageId($messageId);

        return $this->redirect($lead->trouble->getUrl());
    }

    /**
     * Получение лида по messageId
     *
     * @param string $messageId
     * @return Lead
     */
    private function _getLeadByMessageId($messageId)
    {
        if (!($lead = Lead::findOne(['message_id' => $messageId]))) {
            throw new InvalidParamException('Неверный messageId');
        }

        return $lead;
    }

    /**
     * Создание лида
     *
     * @param Lead $lead
     * @param integer $clientAccountId
     * @return Response
     */
    private function _toLead(Lead $lead, $clientAccountId = null)
    {
        if ($clientAccountId) {
            $this->_checkClientAccountId($clientAccountId);

            if ($lead->account_id != $clientAccountId) {
                $lead->moveToClientAccount($clientAccountId);
            }
        }

        return $this->redirect($lead->getUrl());
    }

    /**
     * Создание клиента
     *
     * @param Lead $lead
     * @return Response
     * @throws \Exception
     */
    private function _makeClient(Lead $lead)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $client = new ClientCreateExternalForm();

            $client->entry_point_id = Lead::DEFAULT_ENTRY_POINT;

            isset($lead->data['did']) && $client->contact_phone = $lead->data['did'];

            if (!$client->create()) {
                throw new ModelValidationException($client);
            }

            $lead->moveToClientAccount($client->account_id);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->redirect($lead->getUrl());
    }

    /**
     * Перемещение лида в мусорного клиента
     *
     * @param Lead $lead
     * @return string|Response
     * @throws \Exception
     */
    private function _toTrashClient(Lead $lead)
    {
        $lead->moveToClientAccount(Lead::TRASH_ACCOUNT_ID);

        if (Yii::$app->request->isAjax) {
            return 'Ok';
        } else {
            return $this->redirect('/');
        }
    }


    /**
     * Переводим лид-заявку на другой этап.
     *
     * @param Lead $lead
     * @param integer $stateId
     * @param integer $clientAccountId
     * @return string|Response
     */
    private function _setState(Lead $lead, $stateId, $clientAccountId)
    {
        $this->_checkClientAccountId($clientAccountId);

        if ($lead->account_id != $clientAccountId) {
            $lead->moveToClientAccount($clientAccountId);
        }

        $trouble = $lead->trouble;

        if (!$trouble->isTranferAllowed($stateId)) {
            throw new InvalidParamException('Невозможно перевести из стадии ' . $trouble->currentStage->state_id . ' в ' . $stateId);
        }

        $trouble->addStage($stateId, '');

        if (Yii::$app->request->isAjax) {
            return 'Ok';
        } else {
            return $this->redirect($lead->getUrl());
        }
    }

    /**
     * Проверка существования ЛС
     *
     * @param integer $clientAccountId
     */
    private function _checkClientAccountId($clientAccountId)
    {
        if (!ClientAccount::find()->where(['id' => $clientAccountId])->exists()) {
            throw new InvalidParamException('ЛС не найден');
        }
    }
}
