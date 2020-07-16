<?php

namespace app\classes\documents;

use app\models\Language;

class CurrentStatementSkDocument extends CurrentStatementDocument
{
    public function getLanguage()
    {
        return Language::LANGUAGE_SLOVAK;
    }

}