<?php

namespace app\classes\documents;

use app\models\Language;

class CurrentStatementHuDocument extends CurrentStatementDocument
{
    public function getLanguage()
    {
        return Language::LANGUAGE_MAGYAR;
    }

}