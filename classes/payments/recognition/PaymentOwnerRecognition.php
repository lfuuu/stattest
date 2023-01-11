<?php

namespace app\classes\payments\recognition;

use app\classes\helpers\LoggerSimpleInternal;
use app\classes\Singleton;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\ClientContract;

class PaymentOwnerRecognition extends Singleton
{
    /** @var LoggerSimpleInternal */
    private $logger = null;

    public function who($model)
    {
        $this->logger = new LoggerSimpleInternal();

        if (!$model->info_json) {
            $this->logger->add('Пустой info_json');
            return;
        }

        $info = json_decode($model->info_json, true);

        if (!isset($info['paymentPurpose']) || !$info['paymentPurpose']) {
            $this->logger->add('Пустое описание');
        } else {

            $purpose = $info['paymentPurpose'];

            $data = $this->parseDescription($purpose);

            if (!$data) {
                $this->logger->add('В описании платежа ничего не распознано');
            } else {
                $accountId = $this->accountQualifierByDescription($data);
                if ($accountId) {
                    $this->logger->add('Результат поиска по описанию: Л/С: ' . $accountId);
                    return $accountId;
                }
            }
        }

        $accountId = $this->accountQualifierByInn($info['payerInn']);
        if ($accountId) {
            $this->logger->add('Результат по ИНН: Л/С: ' . $accountId);
            return $accountId;
        }

        $this->logger->add('Результат: Л/С не найден');
    }

    public function getLog()
    {
        return $this->logger->get();
    }


    public function parseDescription($description)
    {
        $matches = [];

        if (preg_match("/\D(20\d{4}\s*-\s*\d{6,7})\D?/", $description, $matches)) {
            $data['bill_no'] = preg_replace('/[^\d\-]+/', '', $matches[1]);
            $this->logger->add('найден счет: ' . $data['bill_no']);
        }

        if (preg_match("/id(\d{5,})/", $description, $matches)) {
            $data['account_id'] = $matches[1];
            $this->logger->add('найден Л/С: ' . $data['account_id']);
        } elseif (preg_match("/(л\/с|лицевому счету)\s*(#|№| )?(\d{5,6})[^\d-]/iu", $description, $matches)) {
            $data['account_id'] = $matches[3];
            $this->logger->add('найден Л/С: ' . $data['account_id']);
        }

        if (preg_match("/(по\s*)?дог(овор(у|а)?)?\.?\s*(оказанаия)?\s*(услуг связи)?\s*№?#?\s*([0-9A-Z_\-]{5,})/iu", $description, $matches)) {
            $data['contract_number'] = $matches[6];
            $this->logger->add('найден договор: ' . $data['contract_number']);
        }

        return $data;
    }

    public function accountQualifierByDescription($data)
    {
        if (isset($data['bill_no'])) {
            $accountId = Bill::find()->where(['bill_no' => $data['bill_no']])->select('client_id')->scalar();

            if ($accountId) {
                $this->logger->add('Найден Л/С ' . $accountId . ' по счету');
                return $accountId;
            } else {
                $this->logger->add('Л/С по счету не найден');
            }
        }

        if (isset($data['account_id'])) {
            $accountId = ClientAccount::find()->where(['id' => $data['account_id']])->select('id')->scalar();
            if ($accountId) {
                $this->logger->add('Проверка существования Л/С пройдена');
                return $accountId;
            } else {
                $this->logger->add('Л/С не найден');
            }
        }


        if (isset($data['contract_number'])) {
            $contractId = ClientContract::find()->where(['number' => $data['contract_number']])->select('id')->scalar();

            if ($contractId) {
                $accountIds = ClientAccount::find()->where([
                    'contract_id' => $contractId,
                    'is_active' => 1
                ])->select('id')->limit(10)->column();

                if (!$accountIds) {
                    $this->logger->add('Не найдены ЛС у договора');
                } elseif (count($accountIds) > 1) {
                    $this->logger->add('нет однозначного выбора ЛС: ' . implode(", ", $accountIds));
                } else {
                    $this->logger->add('найден активный ЛС (' . $accountIds[0] . ') по договору');
                    return reset($accountIds);
                }

            } else {
                $this->logger->add('Договор не найден');
            }
        }
    }

    public function accountQualifierByInn($inn)
    {
        if (!$inn) {
            $this->logger->add('ИНН не задан');
            return;
        }

        $accountIds = ClientAccount::find()
            ->alias('c')
            ->joinWith('clientContractModel.clientContragent cg')
            ->where([
                'c.is_active' => 1,
                'cg.inn' => $inn
            ])->select('c.id')
            ->limit(10)
            ->column();

        $this->logger->add('по ИНН ' . $inn . ' найдено Л/С: ' . count($accountIds) . ' шт.');
        if (!$accountIds) {
            return;
        }

        if (count($accountIds) > 1) {
            $this->logger->add('нет однозначного выбора ЛС: ' . implode(", ", $accountIds));
            return;
        }

        return reset($accountIds);
    }
}