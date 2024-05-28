<?php

namespace app\classes\payments\makeInfo;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\models\PaymentApiInfo;
use app\models\PaymentInfo;
use app\models\PaymentInfoShort;

class PaymentMakeInfoFactory extends Singleton
{
    public function listInformators(): array
    {
        return [
            BankPaymentInformator::class,
            CardAcquiringInformator::class,
            UnawareInformator::class,
        ];
    }

    public function getInformatorByApiAnfo(PaymentApiInfo $apiInfo): ?Informator
    {
        foreach ($this->listInformators() as $informatorClass) {
            $informator = new $informatorClass;
            if ($informator->detect($apiInfo)) {
                return $informator;
            }
        }
        return null;
    }
}

abstract class Informator
{
    protected ?array $json = null;
    protected ?PaymentApiInfo $apiInfo = null;

    public function detect(PaymentApiInfo $apiInfo): bool
    {
        $json = $apiInfo->getInfoJsonAsJsin();
        $this->json = $json;
        $this->apiInfo = $apiInfo;

        return $this->detectCb();
    }

    abstract function detectCb(): bool;

    public function getPaymentInfo(): ?PaymentInfo
    {
        return null;
    }

    public function savePaymentInfo(): self
    {
        return $this;
    }

    public function saveShortInfo(): self
    {
        return $this;
    }
}

class UnawareInformator extends Informator
{
    function detectCb(): bool
    {
        return true;
    }
}

class BankPaymentInformator extends Informator
{

    public function detectCb(): bool
    {
        $json = $this->json;

        if (!$json || !is_array($json) || !isset($json['paymentPurpose'])) {
            return false;
        }

        return true;
    }

    public function getPaymentInfo($paymentId): ?PaymentInfo
    {
        $j = $this->json;

        $info = PaymentInfo::findOne(['payment_id' => $paymentId]) ?: (new PaymentInfo());
        $info->payment_id = $paymentId;
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

    public function savePaymentInfo(): self
    {
        $info = $this->getPaymentInfo($this->apiInfo->payment_id);
        if (!$info->save()) {
            throw new ModelValidationException($info);
        }

        return $this;
    }
}

class CardAcquiringInformator extends Informator
{
    public function detectCb(): bool
    {
        $json = $this->json;

        if ($json && is_array($json) && isset($json['cardAuthInfo']) && isset($json['cardAuthInfo']['maskedPan'])) {
            return true;
        }

        return false;
    }

    private function getShortText()
    {
        $bank = ($this->json['bankInfo']['bankName'] ? ' (' . $this->json['bankInfo']['bankName'] . ')' : '');
        $cardHolder = $this->json['cardAuthInfo']['cardholderName'] ? ' /'.$this->json['cardAuthInfo']['cardholderName'].'/' : '';
        $s = "Оплата банковской картой {$this->json['cardAuthInfo']['maskedPan']}{$bank}{$cardHolder}";

        return ['Эквайринг', $s];
    }

    private function getShortInfo(): PaymentInfoShort
    {
        $shortInfo = PaymentInfoShort::findOne(['payment_id' => $this->apiInfo->payment_id]) ?: (new PaymentInfoShort());
        $shortInfo->payment_id = $this->apiInfo->payment_id;
        list($shortInfo->type, $shortInfo->comment) = $this->getShortText();

        return $shortInfo;
    }

    public function saveShortInfo(): self
    {
        $shortInfo = $this->getShortInfo();

        if (!$shortInfo->save()) {
            throw new ModelValidationException($shortInfo);
        }

        return $this;
    }
}