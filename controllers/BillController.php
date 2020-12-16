<?php

namespace app\controllers;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\BillCorrection;
use app\models\BillLineCorrection;
use Yii;
use app\classes\BaseController;
use app\classes\Assert;
use app\classes\documents\DocumentReportFactory;
use app\models\Bill;
use yii\base\Model;
use yii\web\Response;

class BillController extends BaseController
{
    /**
     * @param string $billNo
     * @param string $docType
     * @param int $isPdf
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionPrint($billNo, $docType = 'bill', $isPdf = 0)
    {
        /** @var Bill $bill */
        $bill = Bill::findOne(['bill_no' => $billNo]);

        Assert::isObject($bill);

        $sendEmail = Yii::$app->request->get('emailed') == 1;
        $report = DocumentReportFactory::me()->getReport($bill, $docType, $sendEmail);

        if ($isPdf) {
            $response = Yii::$app->response;
            $response->headers->set('Content-Type', 'application/pdf; charset=utf-8');
            $response->content = $report->renderAsPDF();
            $response->format = Response::FORMAT_RAW;
            Yii::$app->end();
        }

        return $report->render();
    }

    /**
     * Создание корректирующей с/ф
     *
     * @param string $bill_no
     * @param integer $type_id
     * @return string
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionCorrectionInvoice($bill_no, $type_id)
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);

        Assert::isObject($bill);

        /** @var BillCorrection $billCorrection */
        $billCorrection = BillCorrection::find()
            ->where(['bill_no' => $bill_no, 'type_id' => $type_id])
            ->orderBy(['number' => SORT_DESC])
            ->one();

        if ($billCorrection && \Yii::$app->request->post('dropButton')) {
            if (!$billCorrection->delete()) {
                throw new ModelValidationException($billCorrection);
            }
            $billCorrection->recalcSumCorrection();

            return $this->redirect($bill->getUrl());
        }

        if (!$billCorrection) {
            $billCorrection = new BillCorrection;
            $billCorrection->bill_no = $bill_no;
            $billCorrection->type_id = $type_id;
            $billCorrection->number = 1;
            $billCorrection->date = date(DateTimeZoneHelper::DATE_FORMAT);
        }

        $lineAdd = new BillLineCorrection();

        // сохранение
        if (\Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                // дата изменения
                $billCorrection->load(\Yii::$app->request->post());

                if (!$billCorrection->save()) {
                    throw new ModelValidationException($billCorrection);
                }

                // позиции счета
                $models = $billCorrection->lines;

                $delete = \Yii::$app->request->post('delete');

                Model::loadMultiple($models, \Yii::$app->request->post());

                foreach ($models as $idx => $model) {
                    if ($delete && in_array($idx, $delete)) {
                        if (!$model->delete()) {
                            throw new ModelValidationException($model);
                        }
                        continue;
                    }

                    if (!$model->save()) {
                        throw new ModelValidationException($model);
                    }
                }

                $lineAdd->setAttributes([
                    'bill_no' => $bill_no,
                    'bill_correction_id' => $billCorrection->id
                ]);

                // Сохранение новой строки
                $lineAddData = \Yii::$app->request->post('BillLineCorrectionAdd');
                if ($lineAddData['item'] && $lineAdd->load($lineAddData, '')) {
                    if (!$lineAdd->validate()) {
                        \Yii::$app->session->addFlash('error', implode("<br>", $lineAdd->getFirstErrors()));
                    } elseif (!$lineAdd->save()) {
                        throw new ModelValidationException($lineAdd);
                    } else {
                        // сохраненно. Сбрасываем модель.
                        $lineAdd = new BillLineCorrection();
                    }
                }

                $billCorrection->refresh();
                $billCorrection->recalcSumCorrection();

                if ($billCorrection->bill->invoices) {
                    $billCorrection->bill->generateInvoices();
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::$app->session->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('correction', [
            'bill' => $bill,
            'billCorrection' => $billCorrection,
            'lineAdd' => $lineAdd
        ]);
    }

}