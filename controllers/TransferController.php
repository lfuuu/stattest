<?php

namespace app\controllers;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use app\forms\transfer\ServiceTransferForm;
use app\models\ClientAccount;
use yii\helpers\Json;

class TransferController extends BaseController
{

    public function actionIndex($client)
    {
        $clientAccount = ClientAccount::findOne($client);
        Assert::isObject($clientAccount);

        $model = new ServiceTransferForm;
        if ($model->load(Yii::$app->request->post(), 'transfer') && $model->validate() && $model->process()) {

            Yii::$app->session->set(
                'transfer_results_' . $clientAccount->id . '_' . $model->targetAccount->id,
                Json::encode($model->servicesSuccess)
            );

            $this->redirect([
                'transfer/success',
                'client_account_id' => $clientAccount->id,
                'target_account_id' => $model->targetAccount->id
            ]);
        }

        $this->layout = 'minimal';
        return $this->render('index', [
            'model' => $model,
            'client' => $clientAccount
        ]);
    }

    public function actionSuccess($client_account_id, $target_account_id) {
        $clientAccount = ClientAccount::findOne($client_account_id);
        Assert::isObject($clientAccount);

        $targetAccount = ClientAccount::findOne($target_account_id);
        Assert::isObject($targetAccount);

        $session = Yii::$app->session;
        $session_key = 'transfer_results_' . $clientAccount->id . '_' . $targetAccount->id;

        $movedServices = Json::decode($session->get($session_key));
        unset($session[$session_key]);

        $this->layout = 'minimal';
        return $this->render('success', [
            'model'         => new ServiceTransferForm,
            'clientAccount' => $clientAccount,
            'targetAccount' => $targetAccount,
            'movedServices' => $movedServices
        ]);
    }

    public function actionAccountSearch($client_id, $term)
    {
        if (!Yii::$app->request->getIsAjax())
            $this->redirect('/');

        $result = ClientAccount::getDB()->createCommand("
            SELECT SQL_CALC_FOUND_ROWS c.`id`, c.`client`, cc.`name` AS 'contragent'
            FROM `clients` c
                    LEFT JOIN `client_contragent` cc ON cc.`id` = c.`contragent_id`
            WHERE
                c.`id` != " . (int) $client_id . " AND
                c.`client` LIKE '%" . $term . "%' OR
                c.`company` LIKE '%" . $term . "%' OR
                c.`id` = " . (int) $term . " OR
                cc.`name` LIKE '%" . $term . "%'
            ORDER BY cc.`name` DESC, c.`id` DESC
            LIMIT 10
        ")->queryAll();

        $items = [];
        foreach ($result as $row)
            $items[] = [
                'label' => html_entity_decode(
                        '№ ' . $row['id'] . ' - ' .
                        (
                            mb_strlen($row['contragent'], 'UTF-8') > 27
                                ? mb_substr($row['contragent'], 0, 27, 'UTF-8') . '...'
                                : $row['contragent']
                        )
                ),
                'full' => '№ ' . $row['id'] . ' - ' . $row['contragent'],
                'value' => $row['id']
            ];

        return Json::encode($items);
    }

}