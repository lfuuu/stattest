<?php

namespace app\controllers\report\accounting;

use app\classes\BaseController;
use app\classes\excel\BalanceSellToExcel;
use app\classes\excel\BalanceSellToExcelEu;
use app\classes\excel\BalancesellToExcelRegister;
use app\helpers\DateTimeZoneHelper;
use app\models\BusinessProcessStatus;
use app\models\filter\SaleBookFilter;
use app\models\Organization;
use Exception;
use Yii;
use yii\filters\AccessControl;

class SaleBookController extends BaseController
{
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
            // Формирование документа

            try {
                if ($filter->is_euro_format) {
                    $excel = new BalanceSellToExcelEu(['filter' => $filter]);
                    $excel->download('Sale book');
                } elseif ($filter->is_register) {
                    $excel = new BalancesellToExcelRegister(['filter' => $filter]);
                    $excel->download('Реестр');
                } else {
                    $excel = new BalanceSellToExcel(['filter' => $filter]);
                    $excel->download('Книга продаж');
                }
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
                return $this->redirect(['index']);
            }
        }

        return $this->render('index', [
            'filter' => $filter,
        ]);
    }
}
