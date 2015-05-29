<?php

namespace app\controllers;

use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use app\forms\transfer\ServiceTransferForm;
use app\models\ClientAccount;


class TransferController extends BaseController
{

    public function actionIndex($client)
    {
        $clientAccount = ClientAccount::findOne($client);
        Assert::isObject($clientAccount);

        $model = new ServiceTransferForm;

        if ($model->load(Yii::$app->request->post(), 'transfer') && $model->validate() && $model->process()) {
            $this->redirect(array(
                'transfer/success',
                'target_account_id' => (
                    $model->target_account_id == 'custom' ? $model->target_account_custom : $model->target_account_id
                )
            ));
        }

        $this->layout = 'minimal';
        return $this->render('index', [
            'model' => $model,
            'client' => $clientAccount
        ]);
    }

    public function actionAccountSearch($term)
    {
        if (!Yii::$app->request->getIsAjax())
            $this->redirect('/');

        $result = ClientAccount::getDB()->createCommand("
            SELECT SQL_CALC_FOUND_ROWS `id`, `client`, `company`, `firma`
            FROM `clients`
            WHERE (`client` LIKE ('%" . $term . "%')) OR (`company` LIKE ('%" . $term . "%')) OR (`id` = " . (int) $term . ")
            LIMIT 15
        ")->queryAll();

        $items = [];
        foreach ($result as $row)
            $items[] = [
                'id' => $row['id'],
                'label' => html_entity_decode(
                        $row['client'] . ': ' .
                        (
                            !mb_strlen($row['firma'])
                                ? (
                                    mb_strlen($row['company']) > 30 ? mb_substr($row['company'], 0, 30) . '...'  : $row['company']
                                )
                                : $row['firma']
                        )
                ),
                'value' => $row['id']
            ];

        return \yii\helpers\Json::encode($items);
    }

    public function actionSuccess($target_account_id) {
        $this->layout = 'minimal';
        return $this->render('success', [
            'target_account_id' => $target_account_id
        ]);
    }

}