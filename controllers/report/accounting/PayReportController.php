<?php

namespace app\controllers\report\accounting;

use app\classes\ActOfReconciliation;
use app\classes\BaseController;
use app\classes\payments\recognition\PaymentRecognitionFactory;
use app\classes\payments\recognition\processors\RecognitionProcessor;
use app\classes\traits\AddClientAccountFilterTraits;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Country;
use app\models\EventQueue;
use app\models\media\ClientFiles;
use app\exceptions\ModelValidationException;
use app\models\Payment;
use app\modules\atol\behaviors\SendToOnlineCashRegister;
use app\models\filter\PayReportFilter;
use app\modules\uu\models\Bill as uuBill;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Response;

class PayReportController extends BaseController
{
    use AddClientAccountFilterTraits;

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'send-to-atol', 'revise'],
                        'roles' => ['newaccounts_payments.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['change-payment-type'],
                        'roles' => ['newaccounts_payments.edit'],
                    ],
                ],
            ],
        ]);
    }

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
        $get = Yii::$app->request->get();

        if (!isset($get['PayReportFilter'])) {
            $get['PayReportFilter'] = [
                'add_date_from' => (new \DateTimeImmutable)->modify('-2 days')->format(DateTimeZoneHelper::DATE_FORMAT),
            ] + ($this->_getCurrentClientAccountId() ? ['client_id' => $this->_getCurrentClientAccountId()] : []);
        }

        $filterModel->load($get);


        try {
            $result = $this->do($filterModel);
            if ($result) {
                \Yii::$app->session->addFlash('success', $result);
            }
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    private function do(PayReportFilter $filterModel)
    {
        if (!Yii::$app->user->can('newaccounts_payments.edit')) {
            throw new AccessDeniedException('Нет прав');
        }

        switch (\Yii::$app->request->get('do')) {
            case 'recognition':
                if (!$filterModel->id) {
                    throw new \InvalidArgumentException('Платеж не задан');
                }
                $payment = Payment::findOne(['id' => $filterModel->id]);
                if (!$payment) {
                    throw new \InvalidArgumentException('Платеж не найден');
                }

                if ($payment->client_id != RecognitionProcessor::UNRECOGNIZED_PAYMENTS_ACCOUNT_ID) {
                    throw new \InvalidArgumentException('Платеж не на кошельке по-умолчанию');
                }

                $infoJson = json_decode($payment->apiInfo->info_json ?? '{}', true);

                if (!$infoJson) {
                    throw new \InvalidArgumentException('Пустой json с данными');
                }

                $processor = PaymentRecognitionFactory::me()->getProcessor($infoJson);

                if (($recognizedAccountId = $processor->who()) != $payment->client_id) {
                    $fromAccountId = $payment->client_id;
                    $payment->client_id = $recognizedAccountId;
                    if (!$payment->save()) {
                        throw new ModelValidationException($payment);
                    }
                    EventQueue::go(EventQueue::UPDATE_BALANCE, $fromAccountId);
                    EventQueue::go(EventQueue::UPDATE_BALANCE, $payment->client_id);
                }

                $payment->apiInfo->log = $processor->getLog() . PHP_EOL . '----------------------------' . PHP_EOL . $payment->apiInfo->log;
                if (!$payment->apiInfo->save()) {
                    throw new ModelValidationException($payment->apiInfo);
                }
                return $processor->getLog();
                break;
        }
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

    /**
     * @return string
     * @throws Exception
     * @throws \yii\base\Exception
     */
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
        $depositBalance = 0;
        $deposit = 0;
        $sign = '';

        $format = isset($get['format']) ? $get['format'] : '';
        $isWithLink = ($format == 'w_links');
        $isSubmit = isset($get['submit']);

        if ($isSubmit) {
            $dateFrom = $get['dateFrom'];
            $dateTo = $get['dateTo'];
            $saldoView = preg_replace("/\s/", '', $get['saldo']);
            $saldo = (float)str_replace(',', '.', $saldoView);
            $sign = $get['sign'];

            if ($dateFrom && $dateTo && $accountId) {
                if (!$accountId || !($account = ClientAccount::findOne(['id' => $accountId]))) {
                    throw new Exception('Выберите клиента');
                }

                $this->view->title .= ',  ' . $account->getAccountTypeAndId();
                $result = ActOfReconciliation::me()->getRevise($account, $dateFrom, $dateTo, $saldo, false, $isWithLink);

                if ($isWithLink) {
                    $currentStatementSum = 0;
                    if ($account->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
                        $currentStatementSum = uuBill::getUnconvertedAccountEntries($account->id)->sum('price_with_vat') ?: 0;
                    }

                    $result['data'][] = [
                        'type' => 'current_statement',
                        'date' => date(DateTimeZoneHelper::DATE_FORMAT),
                        'income_sum' => (float)$currentStatementSum,
                        'description' => 'Текущая выписка',
                    ];

                    ActOfReconciliation::me()->addingLinks($account, $result['data'], $isRussia = $account->getUuCountryId() == Country::RUSSIA);
                }

                $allModels = $result['data'];
                $deposit = $result['deposit'];
                $depositBalance = $result['deposit_balance'];
                $contragent = $account->contract->getContragent($dateTo);
                $firm = $account->getOrganization($dateFrom);
            } elseif (isset($get['submit'])) {
                Yii::$app->session->setFlash('error', 'Выберите клиента и заполните дату');
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
            'pagination' => false
        ]);

        $viewParams = [
            'dataProvider' => $dataProvider,
            'isSubmit' => $isSubmit,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'saldo' => $saldoView,
            'contragent' => $contragent,
            'firm' => $firm,
            'deposit' => $deposit,
            'result' => $allModels,
            'deposit_balance' => $depositBalance,
            'accountId' => $accountId,
            'sign' => $sign,
            'format' => $format,
            'currency' => $account->currency,
            'isWithLink' => $isWithLink,
        ];


        switch ($format) {
            case 'pdf':

                $response = Yii::$app->response;
                $response->headers->set('Content-Type', 'application/pdf; charset=utf-8');
                $response->content = $this->renderAsPDF('revise', $viewParams);
                $response->format = Response::FORMAT_RAW;

                // Save file
                $clientFilesAttr = [
                    'name' => str_replace(['"'], "",
                            $contragent->name_full) . ' ' . $account->id . ' Акт сверки (на ' . $dateTo . ').pdf',
                    'ts' => $account->getDatetimeWithTimezone()->format(DateTimeZoneHelper::DATETIME_FORMAT),
                    'contract_id' => $account->contract_id,
                    'comment' => $contragent->name_full . ' ' . $account->id . ' Акт сверки (на ' . $dateTo . ')',
                    'user_id' => \Yii::$app->user->identity->id
                ];

                $clientFiles = new ClientFiles();
                $clientFiles->setAttributes($clientFilesAttr, false);
                if (!$clientFiles->save()) {
                    throw new ModelValidationException($clientFiles);
                }
                file_put_contents(Yii::$app->params['STORE_PATH'] . 'files/' . $clientFiles->id, $response->content);

                Yii::$app->end();

                break;

            case 'html':
                return $this->renderPartial('revise', $viewParams);
                break;

            default:
                return $this->render('revise', $viewParams);
        }
    }

    public function actionChangePaymentType($id)
    {
        $reportQuery = \Yii::$app->request->get('PayReportFilter');

        try {
            $report = new PayReportFilter();
            if (!$report->load($reportQuery, '')) {
                throw new \InvalidArgumentException('Ошибка в данных');
            }

            $report->id = $id;
            $payments = $report->search()->getModels();

            if (count($payments) != 1) {
                throw new \InvalidArgumentException('Платежей не 1. ');
            }

            /** @var Payment $payment */
            $payment = reset($payments);
            $payment->payment_type = $payment->payment_type == Payment::PAYMENT_TYPE_INCOME ? Payment::PAYMENT_TYPE_OUTCOME : Payment::PAYMENT_TYPE_INCOME;
            if (!$payment->save()) {
                throw new ModelValidationException($payment);
            }
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->redirect(['/report/accounting/pay-report/', 'PayReportFilter' => $reportQuery]);
    }
}