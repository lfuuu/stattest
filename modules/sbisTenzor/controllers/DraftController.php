<?php

namespace app\modules\sbisTenzor\controllers;

use app\classes\BaseController;
use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\SBISExchangeStatus;
use app\modules\sbisTenzor\forms\draft\IndexForm;
use yii\base\InvalidArgumentException;
use yii\filters\AccessControl;
use Yii;

/**
 * DraftController controller for the `sbisTenzor` module
 */
class DraftController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'download-attachment'],
                        'roles' => ['newaccounts_bills.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['cancel', 'restore', 'save', 'process', 'process-all', 'repeat'],
                        'roles' => ['newaccounts_bills.edit'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Получить выбранного клиента
     *
     * @param int|null $clientId
     * @param bool $strict
     * @return ClientAccount|null
     */
    protected function getClient($clientId = null, $strict = true)
    {
        $client = null;
        if ($clientId) {
            $client = ClientAccount::findOne(['id' => $clientId]);
        } else {
            $client = $this->getFixClient();
        }

        if (!$client && $strict) {
            throw new InvalidArgumentException('Клиент не выбран');
        }

        return $client;
    }

    /**
     * Список сгенерированных черновиков
     *
     * @param int $clientId
     * @param int $state
     * @return string|\yii\web\Response
     */
    public function actionIndex($clientId = 0, $state =  0)
    {
        $client = $this->getClient($clientId, false);
        $indexForm = new IndexForm($client);

        $processCountAll = 0;
        $showAll = false;

        $processCount = $indexForm->getProcessCount();
        if (!$processCount) {
            $processCountAll = $indexForm->getProcessCount(true);
            $showAll = true;
        }

        return $this->render('index', [
            'dataProvider' => $indexForm->getDataProvider($state),
            'title' => $indexForm->getTitle(),
            'isAuto' => $client->exchange_group_id,
            'isVerified' => SBISExchangeStatus::isVerifiedById($client->exchange_status),
            'processConfirmText' => $indexForm->getProcessConfirmText(),
            'processAllConfirmText' => $indexForm->getProcessConfirmText(true),
            'processCount' => $processCount,
            'showAll' => $showAll,
            'processCountAll' => $processCountAll,
            'clientId' => $client->id,
            'state' => $state,
        ]);
    }

    /**
     * Cancel document
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCancel($id = 0)
    {
        try {
            IndexForm::cancel($id);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sbisTenzor/draft/');
    }

    /**
     * Restore document
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRestore($id = 0)
    {
        try {
            IndexForm::restore($id);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sbisTenzor/draft/');
    }

    /**
     * Repeat document
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRepeat($id = 0)
    {
        try {
            IndexForm::repeat($id);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sbisTenzor/draft/');
    }

    /**
     * Запуск документа в работу
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionSave($id = 0)
    {
        try {
            IndexForm::save($id);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sbisTenzor/draft/');
    }

    /**
     * Download attachment
     *
     * @param int $id
     * @param int $number
     * @return \yii\web\Response
     * @throws \yii\base\ExitException
     */
    public function actionDownloadAttachment($id, $number)
    {
        try {
            Yii::$app
                ->response
                ->sendContentAsFile(
                    IndexForm::getAttachmentContent($id, $number),
                    IndexForm::getAttachmentFileName($id, $number)
                );
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
            return $this->redirect('/sbisTenzor/draft/');
        }

        Yii::$app->end(200);
    }

    /**
     * Запуск черновиков в работу по клиенту
     *
     * @param int $clientId
     * @return \yii\web\Response
     */
    public function actionProcess($clientId = 0)
    {
        try {
            $client = $this->getClient($clientId, false);
            $indexForm = new IndexForm($client);

            $indexForm->process();
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sbisTenzor/draft/');
    }

    /**
     * Запуск черновиков в работу всех подтвержденных клиентов
     *
     * @param int $clientId
     * @return \yii\web\Response
     */
    public function actionProcessAll($clientId = 0)
    {
        try {
            $client = $this->getClient($clientId, false);
            $indexForm = new IndexForm($client);

            $indexForm->process(true);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect('/sbisTenzor/draft/');
    }
}
