<?php

namespace app\classes\documents;

use app\models\Language;

class CurrentStatementEnDocument extends CurrentStatementDocument
{
    public function getLanguage()
    {
        return Language::LANGUAGE_ENGLISH;
    }

}