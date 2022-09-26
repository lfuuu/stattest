<?php

namespace app\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\models\ClientAccount;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;

class AccountingController extends BaseController
{
    use AddClientAccountFilterTraits;

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
                        'roles' => ['clients.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws Exception
     */
    public function actionIndex($client_id = 0)
    {
        $account = ClientAccount::findOne(['id' => $client_id ? $client_id : $this->_getCurrentClientAccountId()]);

        if (!$account) {
            throw new Exception('Client not found');
        }

        // Для старого стата, для старых модулей
        Yii::$app->session->set('clients_client', $account->id);
        $this->applyFixClient($account->id);

        return
            $this->render(
                'index',
                [
                    'account' => $account,
                ]
            );
    }
}
