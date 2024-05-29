<?php

namespace app\classes\payments\makeInfo;

use app\classes\payments\makeInfo\informer\BankPaymentFromStatementInformer;
use app\classes\payments\makeInfo\informer\BankPaymentInformer;
use app\classes\payments\makeInfo\informer\CardAcquiringSberInformer;
use app\classes\payments\makeInfo\informer\CardAcquiringUMoneyInformer;
use app\classes\payments\makeInfo\informer\Informer;
use app\classes\payments\makeInfo\informer\UnawareInformer;
use app\classes\Singleton;
use app\models\PaymentApiInfo;

class PaymentMakeInfoFactory extends Singleton
{
    public function listInformators(): array
    {
        return [
            BankPaymentInformer::class,
            CardAcquiringSberInformer::class,
            CardAcquiringUMoneyInformer::class,
            BankPaymentFromStatementInformer::class,
            UnawareInformer::class,
        ];
    }

    public function getInformatorByApiAnfo(PaymentApiInfo $apiInfo): ?Informer
    {
        foreach ($this->listInformators() as $informerClass) {
            $informer = new $informerClass;
            if ($informer->detect($apiInfo)) {
                return $informer;
            }
        }
        return null;
    }
}
