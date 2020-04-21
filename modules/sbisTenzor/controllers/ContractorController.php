<?php

namespace app\modules\sbisTenzor\controllers;

use app\classes\BaseController;
use app\models\ClientAccount;
use app\modules\sbisTenzor\forms\contractor\IndexForm;
use app\modules\sbisTenzor\forms\contractor\AddForm;
use app\modules\sbisTenzor\forms\contractor\RoamingForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\AccessControl;

/**
 * ContractorController controller for the `sbisTenzor` module
 */
class ContractorController extends BaseController
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
                        'actions' => ['index', 'roaming'],
                        'roles' => ['newaccounts_bills.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add'],
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
     * Информация по интеграции клиентов
     *
     * @param int $clientId
     * @param int $state
     * @return string|\yii\web\Response
     */
    public function actionIndex($clientId = 0, $state = -1)
    {
        $indexForm = new IndexForm($this->getClient($clientId, false));

        return $this->render('index', [
            'dataProvider' => $indexForm->getDataProvider($state),
            'state' => $state,
            'title' => $indexForm->getTitle(),
        ]);
    }

    /**
     * Информация по контрагентам с роумингом
     *
     * @param int $clientId
     * @return string|\yii\web\Response
     */
    public function actionRoaming($clientId = 0)
    {
        $indexForm = new RoamingForm($this->getClient($clientId, false));

        return $this->render('roaming', [
            'dataProvider' => $indexForm->getDataProvider(),
            'title' => $indexForm->getTitle(),
        ]);
    }

    /**
     * Добвить роуминг
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        try {
            $addForm = new AddForm();

            if ($addForm->tryToSave()) {
                return $this->redirect(AddForm::getIndexUrl());
            }
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->render('add', [
            'form' => $addForm,
            'indexUrl' => AddForm::getIndexUrl(),
        ]);
    }
}
