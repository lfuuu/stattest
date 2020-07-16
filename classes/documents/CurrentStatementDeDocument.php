<?php

namespace app\classes\documents;

use app\models\Language;

class CurrentStatementDeDocument extends CurrentStatementDocument
{
    public function getLanguage()
    {
        return Language::LANGUAGE_GERMANY;
    }

}