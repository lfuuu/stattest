<?php

namespace app\classes\documents;

use app\models\Currency;

class DocSoglMCNTelekomToService extends DocumentReport
{
    const DOC_TYPE_BILL = 'sogl_mcn_telekom_to_service';

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
        return 'Соглашение о передачи прав (МСН Телеком => МСН Телеком Сервис)';
    }

}
