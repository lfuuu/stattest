<?php

namespace app\classes\documents;

class GetBillData extends DocumentReport
{

    public function getLanguage(){}

    public function getCurrency() {}

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Получение счета для последующей обработки / вывода';
    }

}