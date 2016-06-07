<?php

namespace app\classes\documents;

use DateTime;
use app\models\Currency;

class DocSoglMCMTelekom extends DocumentReport
{
    const DOC_TYPE_BILL = 'sogl_mcm_telekom';

    public function getLanguage()
    {
        return 'ru-RU';
    }

    public function getCurrency()
    {
        return Currency::RUB;
    }

    public function getDocType()
    {
        return self::DOC_TYPE_BILL;
    }

    public function getName()
    {
        return 'Соглашение о передачи прав';
    }

}
