<?php
namespace app\controllers\usage;

use app\classes\api\ApiVpbx;
use app\classes\Assert;
use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\models\ActualVirtpbx;
use app\models\ClientAccount;
use app\models\UsageVirtpbx;
use app\modules\uu\models\AccountTariff;
use Yii;
use yii\filters\AccessControl;

class VpbxController extends BaseController
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Деархивация ВАТС
     *
     * @param integer $accountId
     * @param integer $usageId
     * @return \yii\web\Response
     */
    public function actionDearchive($accountId, $usageId)
    {
        /** @var ClientAccount $account */
        $account = ClientAccount::findOne($accountId);
        Assert::isObject($account);

        $isUniversalUsage = $usageId >= AccountTariff::DELTA;

        /** @var AccountTariff|UsageVirtpbx $usage */
        $usage = $isUniversalUsage ?
            AccountTariff::findOne(['id' => $usageId, 'client_account_id' => $account->id]) :
            UsageVirtpbx::findOne(['id' => $usageId, 'client' => $account->client]);

        Assert::isObject($usage);

        if ($isUniversalUsage ? !$usage->isClosed() : strtotime($usage->expire_dt) > time()) {
            throw new \LogicException('Услуга не отключена');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $result = ApiVpbx::me()->dearchiveVpbx($account->id, $usage->id);

            if (!$result || !isset($result['success'])) {
                throw new \LogicException('ВАТС не разархивирована');
            }

            /** @var AccountTariff|UsageVirtpbx $newUsage */
            $newUsage = $usage->reopen();

            $usage->is_unzipped = 1;
            if (!$usage->save()) {
                throw new ModelValidationException($usage);
            }

            $actualVirtpbx = ActualVirtpbx::findOne(['usage_id' => $usage->id, 'client_id' => $account->id]);

            if ($actualVirtpbx) {
                $actualVirtpbx->usage_id = $newUsage->id;
            } else {
                $actualVirtpbx = new ActualVirtpbx();
                $actualVirtpbx->setAttributes([
                    'usage_id' => $newUsage->id,
                    'client_id' => $account->id,
                    'tarif_id' => ($isUniversalUsage ? $newUsage->tariffPeriod->tariff_id : $newUsage->LogTariff->id_tarif),
                    'region_id' => $account->region,
                    'biller_version' => $isUniversalUsage ? ClientAccount::VERSION_BILLER_UNIVERSAL : ClientAccount::VERSION_BILLER_USAGE,
                ], false);
            }

            if (!$actualVirtpbx->save()) {
                throw new ModelValidationException($actualVirtpbx);
            }

            $result = ApiVpbx::me()->transfer($account->id, $usage->id, $account->id, $newUsage->id);

            if (!$result || !isset($result['success'])) {
                throw new \LogicException('ВАТС неразархивированна');
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            Yii::$app->session->addFlash('error', $e->getMessage());

            return $this->redirect($account->url);
        }

        Yii::$app->session->addFlash('success', 'Создана новая услуга и ВАТС перенесена на неё');

        return $this->redirect($account->url);
    }
}

