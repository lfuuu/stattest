<?php

namespace app\controllers\report\accounting;

use app\classes\ActOfReconciliation;
use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\atol\behaviors\SendToOnlineCashRegister;
use app\models\filter\PayReportFilter;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;

class PayReportController extends BaseController
{
    use AddClientAccountFilterTraits;

    /**
     * Вывод списка
     *
     * @return string
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\Exception
     * @throws \app\exceptions\ModelValidationException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \HttpRequestException
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $this->view->title = 'Платежи';
        $filterModel = new PayReportFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionSendToAtol($id)
    {
        try {
            $log = SendToOnlineCashRegister::send($id);
            Yii::$app->session->setFlash('success', $log);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRefreshStatus($id)
    {
        try {
            $status = SendToOnlineCashRegister::refreshStatus($id);
            Yii::$app->session->setFlash('success', $status);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionRevise()
    {
        $this->view->title = 'Акт сверки (новый)';
        $get = Yii::$app->request->get();
        $accountId = $this->_getCurrentClientAccountId();

        $dateFrom = (new \DateTimeImmutable())->modify('first day of previous month')->format(DateTimeZoneHelper::DATE_FORMAT);
        $dateTo = (new \DateTimeImmutable())->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT);;
        $saldo = 0;
        $allModels = [];
        $contragent = null;
        $firm = null;

        $isSubmit = isset($get['submit']);

        if ($isSubmit) {
            $dateFrom = $get['dateFrom'];
            $dateTo = $get['dateTo'];
            $saldo = $get['saldo'];

            if ($dateFrom && $dateTo && $accountId) {
                if (!$accountId || !($account = ClientAccount::findOne(['id' => $accountId]))) {
                    throw new Exception('Выберите клиента');
                }

                $this->view->title .= ',  ' . $account->getAccountTypeAndId();
                $allModels = ActOfReconciliation::me()->getRevise($account, $dateFrom, $dateTo, $saldo);

                $contragent = $account->contract->getContragent($dateFrom);
                $firm = $account->getOrganization($dateFrom);
            } elseif (isset($get['submit'])) {
                Yii::$app->session->setFlash('error', 'Выберите клиента и заполните дату');
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
            'pagination' => false
        ]);

        return $this->render('revise', [
            'dataProvider' => $dataProvider,
            'isSubmit' => $isSubmit,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'saldo' => $saldo,
            'contragent' => $contragent,
            'firm' => $firm,
        ]);
    }
}