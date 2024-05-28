<?php

namespace app\classes\payments\recognition\processors;

use app\models\Bill;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\PaymentInfo;
use app\modules\uu\models\AccountTariff;

class PaymentTinkoffRecognitionProcessor extends RecognitionProcessor
{
    public static function detect($infoJson): bool
    {
        if (!$infoJson || !is_array($infoJson) || !isset($infoJson['paymentPurpose'])) {
            return false;
        }

        return true;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function yetWho(): int
    {
        $info = $this->infoJson;

        $purpose = $info['paymentPurpose'];

        $data = $this->parseDescription($purpose);

        $descriptionAccountId = null;
        if (!$data) {
            $this->logger->add('В описании платежа ничего не распознано');
        } else {
            $descriptionAccountId = $this->accountQualifierByDescription($data);
            if ($descriptionAccountId) {
                $this->logger->add('Результат поиска по описанию: Л/С: ' . $descriptionAccountId);
            }
        }

       $isPerson = false;
       if (isset($info['payerName'])) {
           $isPerson = $this->isPerson($info['payerName']);
       }

        if ($isPerson) {
            $this->logger->add('У Частного лица не проверяем Л/С по ИНН');

            if ($descriptionAccountId) {
                $this->logger->add('Результат: Л/С: ' . $descriptionAccountId);
                return $descriptionAccountId;
            }

            $accountIdByName = $this->getAccountIdByName($isPerson, 15);
            if ($accountIdByName) {
                $this->logger->add('Найдено по матчингу: Л/С ' . $accountIdByName);
                return $accountIdByName;
            }

            $this->logger->add('Не найдено по матчингу');
            $accountIdByName = $this->getAccountIdByName($isPerson, 5);
            if ($accountIdByName) {
                $this->logger->add('Возможно это Л/С: ' . $accountIdByName);
            }

            return 0;
        }


        $innAccountId = $this->accountQualifierByInn($info['payerInn'] ?? $data['inn'] ?? false, $descriptionAccountId);
        if (!$innAccountId) {
            $this->logger->add('Итого: Л/С не найден');
            return 0;
        }

        $this->logger->add('Результат: Л/С: ' . $innAccountId);

        return $innAccountId;
    }

    private function parseDescription($description)
    {
        $matches = [];

        if (preg_match("/\D(20\d{4}\s*-\s*\d{6,7})\D?/", $description, $matches)) {
            $data['bill_no'] = preg_replace('/[^\d\-]+/', '', $matches[1]);
            $this->logger->add('Скан: найден счет: ' . $data['bill_no']);
        }

        if (preg_match("/id(\d{5,})/", $description, $matches)) {
            $data['account_id'] = $matches[1];
            $this->logger->add('Скан: найден Л/С: ' . $data['account_id']);
        } elseif (preg_match("/(л[\/\.\s]*с[.]*|лицево(й|му) счету?)\s*(#|№| )?\s*(\d{5,6})\b/iu", $description, $matches)) { // https://regex101.com/r/ZcSTFF/1  Абонентская плата за доступ в интернет Лицевой счет: 54183 -за февраль
            $data['account_id'] = $matches[4];
            $this->logger->add('Скан: найден Л/С: ' . $data['account_id']);
        }

        if (preg_match("/(по\s*)?дог(овор(у|а)?)?\.?\s*(оказанаия)?\s*(услуг связи)?\s*№?#?\s*([0-9A-Z_\-]{5,})/iu", $description, $matches)) {
            $data['contract_number'] = $matches[6];
            $this->logger->add('Скан: найден договор: ' . $data['contract_number']);
        }

        if (preg_match("/ИНН\s*(\d{10})/", $description, $matches)) {
            $data['inn'] = $matches[1];
            $description = str_replace($data['inn'], '', $description);
            $this->logger->add('Скан: найден ИНН: ' . $data['inn']);
        }

        if (preg_match("/\+?((7|8)?(?'number'\d{10}))/", $description, $matches)) {
            $data['voip_number'] = '7' . $matches['number'];
            $this->logger->add('Скан: найден тел.номер: ' . $data['voip_number']);
        }


        return $data;
    }

    private function accountQualifierByDescription($data)
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

                $isActive = $accountIds;

                if (!$accountIds) {
                    $accountIds = ClientAccount::find()->where([
                        'contract_id' => $contractId,
                    ])->select('id')->limit(10)->column();
                }

                if (!$accountIds) {
                    $this->logger->add('Не найдены ЛС у договора');
                } elseif (count($accountIds) > 1) {
                    $this->logger->add('нет однозначного выбора ЛС: ' . implode(", ", $accountIds));
                } else {
                    $this->logger->add('найден ' . (!$isActive ? 'не ' : '') . 'активный ЛС (' . $accountIds[0] . ') по договору');
                    return reset($accountIds);
                }
            } else {
                $this->logger->add('Договор не найден');
            }
        }

        if (isset($data['voip_number']) && $data['voip_number']) {
            $logPrefix = "Номер {$data['voip_number']}:";
            /** @var AccountTariff $accountTariff */
            $accountTariff = AccountTariff::find()->where(['voip_number' => $data['voip_number']])->andWhere(['NOT', ['tariff_period_id' => null]])->one();
            if ($accountTariff) {
                $this->logger->add("{$logPrefix} Найден ЛС {$accountTariff->client_account_id} по включенному номеру");
                return $accountTariff->client_account_id;
            }

            $accountTariffs = AccountTariff::find()->where(['voip_number' => $data['voip_number']])->all();
            if (count($accountTariffs) == 1) {
                /** @var AccountTariff $firstAccountTariff */
                $firstAccountTariff = reset($accountTariffs);
                $this->logger->add("{$logPrefix} Найден ЛС {$firstAccountTariff->client_account_id} по единственному (отключенному) номеру");
                return $firstAccountTariff->client_account_id;
            } elseif ($accountTariffs) {
                $this->logger->add("{$logPrefix} Найдено ЛС: " . count($accountTariffs) . " шт. Выбор неоднозначен.");
            } else {
                $this->logger->add("{$logPrefix} Не найден в услугах");
            }
        }
    }

    private function accountQualifierByInn($inn, $descriptionAccountId)
    {
        if (!$inn) {
            $this->logger->add('ИНН не задан');
            return 0;
        }

        if (!$descriptionAccountId) {
            $accountIds = $this->getAccountIdsByInn($inn);
            if (!$accountIds) {
                $accountIds = $this->getAccountIdsByInn($inn, false);
            }

            if (count($accountIds) == 1) {
                $accountId = reset($accountIds);
                $this->logger->add("Найден единственный активный Л/С {$accountId} по ИНН: {$inn}");
                $this->isIdentificationPayment = true;
                return $accountId;
            } elseif ($accountIds) {
                $this->logger->add('нет однозначного выбора ЛС: ' . implode(", ", array_splice($accountIds, 0, 10)) . (count($accountIds) > 10 ? '...' : ''));
                return 0;
            } else {
                $this->logger->add("Нет Л/С по ИНН {$inn}");
                return 0;
            }
        }

        $accountIds = $this->getAccountIdsByInn($inn, !((bool)$descriptionAccountId));

        if ($accountIds) {
            if (in_array($descriptionAccountId, $accountIds)) {
                $this->logger->add("Л/С {$descriptionAccountId} ЕСТЬ в списке тех, у кого есть ИНН {$inn}");
                $this->isIdentificationPayment = true;
                return $descriptionAccountId;
            } elseif (count($accountIds) == 1) {
                $accountId = reset($accountIds);
                $this->logger->add("Найден единственный Л/С {$accountId} у ИНН: {$inn}");
                $this->isIdentificationPayment = true;
                return $accountId;
            } else {
                $this->logger->add("Л/С {$descriptionAccountId} НЕТ в списке тех, у кого есть ИНН {$inn} (" . $this->getAccountListToString($accountIds) . ")");
                $this->logger->add("Требуется проверка");
                return 0;
            }
        } else {
            $this->logger->add("Нет Л/С по ИНН {$inn}");
            $this->logger->add("Требуется проверка");
            return 0;
        }

        return 0;
    }

    private function getAccountIdsByInn($inn, $isActive = true): array
    {
        $query = ClientAccount::find()
            ->alias('c')
            ->joinWith('clientContractModel.clientContragent cg')
            ->where([
                'cg.inn' => $inn
            ])->select('c.id')
            ->limit(100);

        if ($isActive) {
            $query->andWhere([
                'c.is_active' => 1,
            ]);
        }

        return $query->column();
    }

    private function isPerson($payer)
    {
        if (strpos($payer, '//') !== false) {
            $payerData = explode('//', $payer . '////');

            // ПАО СБЕРБАНК//Голубчиков Павел Васильевич//1522001036883//195252,РОССИЯ,САНКТ-ПЕТЕРБУРГ Г,Г САНКТ-ПЕТЕРБУРГ,УЛ СОФЬИ КОВАЛЕВСКОЙ//
            if ($this->isOrganizatoin($payerData[0]) && $this->isFio($payerData[1])) {
                $this->logger->add('Банк-плательщик: ' . $payerData[0]);
                $this->logger->add('Частное лицо: ' . $payerData[1]);
                return $payerData[1];
            }

            // КОЗИК СЕРГЕЙ МИХАЙЛОВИЧ//Г. Москва Г МОСКВА УЛ Б-Р ОРЕХОВЫЙ д.18 кв.298//
            if ($this->isFio($payerData[0])) {
                $this->logger->add('Частное лицо: ' . $payerData[0]);
                return $payerData[0];
            }

            return false;
        }

        if ($this->isOrganizatoin($payer)) {
            $this->logger->add('Платеж от организации');
            return false;
        }

        if ($this->isFio($payer)) {
            $this->logger->add('Частное лицо: ' . $payer);
            return $payer;
        }

        return false;
    }

    private function isOrganizatoin($payer)
    {
        foreach (['ИП', 'ООО', 'АО', 'ПАО', 'НКО', 'ОП', 'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ'] as $legalForm) {
            if (preg_match("/\b{$legalForm}\b/u", $payer)) {
                return true;
            }
        }

        return false;
    }

    private function isFio($str)
    {
        $data = preg_split("/\s/", $str);

        if (!$data) {
            return false;
        }

        if (count($data) == 3) {
            return true;
        }

        return false;
    }

    public function getPaymentInfo(): ?PaymentInfo
    {
        $j = $this->infoJson;
        $info = new PaymentInfo();
        $info->payer = $j['payerName'] ?? '';
        $info->payer_inn = $j['payerInn'];
        $info->payer_bik = $j['payerBic'];
        $info->payer_bank = $j['payerBank'];
        $info->payer_account = $j['payerAccount'];
        $info->getter = $j['recipient'];
        $info->getter_inn = $j['recipientInn'];
        $info->getter_bik = $j['recipientBic'];
        $info->getter_bank = $j['recipientBank'];
        $info->getter_account = $j['recipientAccount'];
        $info->comment = $j['paymentPurpose'];

        return $info;
    }
}
