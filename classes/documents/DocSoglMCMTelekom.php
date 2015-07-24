<?php

namespace app\classes\documents;

use DateTime;
use app\models\Currency;

class DocSoglMCMTelekom extends DocumentReport
{
    const BILL_DOC_TYPE = 'sogl_mcm_telekom';

    public function getLanguage()
    {
        return 'ru';
    }

    public function getCurrency()
    {
        return Currency::RUB;
    }

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Соглашение о передачи прав';
    }

}
