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
            $this->redirect(['/monitoring/transfered-usages', 'clientAccountId' => $clientAccount->id]);
        }

        return $this->render('index', [
            'model' => $model,
            'clientAccount' => $clientAccount,
        ]);
    }

    public function actionAccountSearch($client_id, $term)
    {
        if (!Yii::$app->request->getIsAjax()) {
            $this->redirect('/');
        }

        $result = ClientAccount::getDB()->createCommand("
            SELECT SQL_CALC_FOUND_ROWS c.`id`, c.`client`, cc.`name` AS 'contragent'
            FROM `clients` c
                    INNER JOIN `client_contract` cr ON cr.`id` = c.`contract_id`
                    INNER JOIN `client_contragent` cc ON cc.`id` = cr.`contragent_id`
            WHERE
                c.`id` != " . (int)$client_id . " AND
                c.`client` LIKE '%" . $term . "%' OR
                c.`id` = " . (int)$term . " OR
                cc.`name` LIKE '%" . $term . "%'
            ORDER BY cc.`name` DESC, c.`id` DESC
            LIMIT 10
        ")->queryAll();

        $items = [];
        foreach ($result as $row) {
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
        }

        return Json::encode($items);
    }

}