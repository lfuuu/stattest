<?php

namespace app\controllers;

use app\models\ClientContract;
use app\models\ClientContragent;
use Yii;
use app\classes\Assert;
use app\classes\BaseController;
use app\forms\transfer\ServiceTransferForm;
use app\models\ClientAccount;
use yii\db\Query;
use yii\helpers\Json;

class TransferController extends BaseController
{

    /**
     * @param int $client
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionIndex($client)
    {
        /** @var ClientAccount $clientAccount */
        $clientAccount = ClientAccount::findOne($client);
        Assert::isObject($clientAccount);

        $model = new ServiceTransferForm($clientAccount);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->process()) {
            $this->redirect('/monitoring/transfered-usages');
        }

        return $this->render('index', [
            'model' => $model,
            'clientAccount' => $clientAccount,
        ]);
    }

    /**
     * @param int $clientAccountId
     * @param string $term
     * @return string
     */
    public function actionAccountSearch($clientAccountId, $term)
    {
        if (!Yii::$app->request->getIsAjax()) {
            $this->redirect('/');
        }

        $items = [];
        foreach (ClientAccount::dao()->clientAccountSearch($clientAccountId, $term)->each() as $row) {
            $items[] = [
                'label' => html_entity_decode(
                    '№ ' . $row['id'] . ' - ' .
                    (
                    mb_strlen($row['name'], 'UTF-8') > 27 ?
                        mb_substr($row['name'], 0, 27, 'UTF-8') . '...' :
                        $row['name']
                    )
                ),
                'full' => '№ ' . $row['id'] . ' - ' . $row['name'],
                'value' => $row['id']
            ];
        }

        return Json::encode($items);
    }

}