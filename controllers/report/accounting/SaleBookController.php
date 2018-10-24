<?php

namespace app\controllers\report\accounting;

use app\classes\BaseController;
use app\helpers\DateTimeZoneHelper;
use app\models\BusinessProcessStatus;
use app\models\filter\SaleBookFilter;
use app\models\Organization;
use Exception;
use Yii;
use yii\filters\AccessControl;

class SaleBookController extends BaseController
{
    public static $skipping_bps = [
        BusinessProcessStatus::TELEKOM_MAINTENANCE_TRASH,
        BusinessProcessStatus::TELEKOM_MAINTENANCE_FAILURE,
        BusinessProcessStatus::WELLTIME_MAINTENANCE_FAILURE
    ];

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
                        'roles' => ['newaccounts_balance.read'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $filter = new SaleBookFilter;
        $request = \Yii::$app->request;
        $filter->load(\Yii::$app->request->get()) && $filter->validate();
        // Получение excel-файла
        if (($isExcel = $request->get('is_excel')) && (int)$isExcel === 1) {
            // Приведение данных к образцу, по которому работает первичный отчет
            $query = $filter->search();
            $data = $this->_dataConversionToStandard($query);
            // Формирование документа
            $saleBookFilter = $request->get('SaleBookFilter');
            $excel = new \app\classes\excel\BalanceSellToExcel;
            $excel->openFile(Yii::getAlias('@app/templates/balance_sell.xls'));
            $excel->organization = Organization::findOne(['id' => $saleBookFilter['organization_id']])
                ->name;
            $excel->dateFrom = $saleBookFilter['date_from'];
            $excel->dateTo = $saleBookFilter['date_to'];
            $excel->prepare($data);
            $excel->download('Книга продаж');
        }
        return $this->render('index', [
            'filter' => $filter,
            'skipping_bps' => self::$skipping_bps,
        ]);
    }

    /**
     * @param $query
     * @return array
     */
    private function _dataConversionToStandard($query)
    {
        $data = [];
        foreach ($query->each() as $invoice) {
            /** @var \app\models\filter\SaleBookFilter $invoice */
            try {
                $account = $invoice->bill->clientAccount;
                $contract = $account->contract;

                if ($contract->contract_type_id === 6 || in_array($contract->business_process_status_id, self::$skipping_bps)) {
                    continue;
                }

                $bill = $invoice->bill;
                $contragent = $contract->contragent;
                list($sum, $sum_without_tax, $sum_tax) = $account->convertSum($invoice->sum, $account->getTaxRate());

                $data[] = [
                    'sum' => $sum,
                    'sum_without_tax' => $sum_without_tax,
                    'sum_tax' => $sum_tax,
                    'inv_date' => time($bill->bill_date),
                    'company_full' => $contragent->name_full,
                    'inn' => $contragent->inn,
                    'kpp' => $contragent->kpp,
                    'inv_no' => $invoice->number . '; ' . $invoice->getDateImmutable()->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
                    'type' => $contragent->legal_type,
                ];
            } catch (Exception $e) {}
        }
        return $data;
    }
}
