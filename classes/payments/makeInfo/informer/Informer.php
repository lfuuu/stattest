<?php

namespace app\classes\payments\makeInfo\informer;


use app\exceptions\ModelValidationException;
use app\models\PaymentApiInfo;
use app\models\PaymentInfo;
use app\models\PaymentInfoShort;
use app\classes\payments\makeInfo\informer\CaseClassInformerShortInfo as case_class_InformerShortInfo;

abstract class Informer
{
    protected ?array $json = null;
    protected ?PaymentApiInfo $apiInfo = null;

    protected ?PaymentInfo $info = null;

    public function detect(PaymentApiInfo $apiInfo): bool
    {
        $json = $apiInfo->getInfoJsonAsJsin();
        $this->json = $json;
        $this->apiInfo = $apiInfo;

        return $this->detectCb();
    }

    abstract function detectCb(): bool;

    public function getPaymentInfo($paymentId): ?PaymentInfo
    {
        return null;
    }

    public function savePaymentInfo(): self
    {
        $info = $this->getPaymentInfo($this->apiInfo->payment_id);
        if ($info && !$info->save()) {
            throw new ModelValidationException($info);
        }

        if ($info) {
            $this->info = $info;
        }

        return $this;
    }

    public function setPaymentInfo(?PaymentInfo $info): self
    {
        if ($info) {
            $this->info = $info;
        }

        return $this;
    }

    abstract protected function getShortText(): ?case_class_InformerShortInfo;

    private function getShortInfo(): ?PaymentInfoShort
    {
        $si = $this->getShortText();
        if (!$si) {
            return null;
        }
        $shortInfo = PaymentInfoShort::findOne(['payment_id' => $this->apiInfo->payment_id]) ?: (new PaymentInfoShort());
        $shortInfo->payment_id = $this->apiInfo->payment_id;
        list($shortInfo->type, $shortInfo->comment) = [$si->type, $si->comment];

        return $shortInfo;
    }

    public function saveShortInfo(): self
    {
        $shortInfo = $this->getShortInfo();

        if ($shortInfo && !$shortInfo->save()) {
            throw new ModelValidationException($shortInfo);
        }

        return $this;
    }

    public function saveInfo()
    {
        return $this->savePaymentInfo()->saveShortInfo();
    }
}
