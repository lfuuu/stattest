<?php

namespace app\commands\convert;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientContract;
use app\models\HistoryChanges;
use app\models\Invoice;
use app\models\Payment;
use app\models\PaymentOrder;
use yii\console\Controller;

class ContractController extends Controller
{
    /**
     * Установка offer_date для договоров типа Оферта
     *
     * @throws ModelValidationException
     */
    public function actionSetOfferDate()
    {
        $contractQuery = ClientContract::find()->where([
            'state' => ClientContract::STATE_OFFER,
            'offer_date' => null
        ]);

        /** @var ClientContract $contract */
        foreach ($contractQuery->each() as $contract) {
            echo '. ';

            $historyQuery = HistoryChanges::find()
                ->where([
                'model' => ClientContract::class,
                    'model_id' => $contract->id
                ])
                ->orderBy(['id' => SORT_ASC]);

            /** @var HistoryChanges $history */
            foreach ($historyQuery->each() as $history) {
                $data = json_decode($history->data_json, true);

                if (!isset($data['state']) || $data['state'] != ClientContract::STATE_OFFER) {
                    continue;
                }

                $dataPrev = json_decode($history->prev_data_json, true);

                if ($dataPrev['state'] == ClientContract::STATE_OFFER) {
                    continue;
                }

                $contract->offer_date = (new \DateTimeImmutable($history->created_at))->format(DateTimeZoneHelper::DATE_FORMAT);

                if (!$contract->save()) {
                    throw new ModelValidationException($contract);
                }

                echo "+";
                break;
            }

        }
    }
}
