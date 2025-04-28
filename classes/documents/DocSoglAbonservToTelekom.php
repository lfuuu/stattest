<?php

namespace app\classes\documents;

use app\models\Currency;

class DocSoglAbonservToTelekom extends DocumentReport
{
    const DOC_TYPE_BILL = 'sogl_mcn_abonserv_to_telekom';

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

    public function getDocTypeFileName()
    {
        return DocSoglMCNServiceToAbonserv::DOC_TYPE_BILL;
    }

    public function getName()
    {
        return 'Соглашение о передачи прав (АбонентСервис => МСН Телеком Сервис)';
    }

}
