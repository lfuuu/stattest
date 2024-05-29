<?php

namespace app\classes\payments\makeInfo\informer;

use app\classes\payments\makeInfo\informer\CaseClassInformerShortInfo as case_class_InformerShortInfo;;

class UnawareInformer extends Informer
{
    function detectCb(): bool
    {
        return true;
    }

    protected function getShortText(): ?case_class_InformerShortInfo
    {
        return null;
    }
}