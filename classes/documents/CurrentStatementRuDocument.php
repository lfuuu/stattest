<?php

namespace app\classes\documents;

use app\models\Language;

class CurrentStatementRuDocument extends CurrentStatementDocument
{
    public function getLanguage()
    {
        return Language::LANGUAGE_RUSSIAN;
    }

}