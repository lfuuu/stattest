<?php

namespace app\classes;

use yii\helpers\Inflector;

trait DoubleAttributeLabelTraut
{
    public $formLang;

    public function generateAttributeLabel($name)
    {
        $category = $this->getLangCategory();
        $appLang = Language::getCurrentLanguage();
        $formLang = $this->formLang;
        $t1 = \Yii::t($category, $name, [], $appLang);
        $t2 = ($formLang == $appLang || !$formLang) ? null : \Yii::t($category, $name, [], $formLang);

        if($t1 == $name)
            return Inflector::camel2words($name, true);
        elseif($t2 === null)
            return $t1;
        else
            return $t1.' ('.$t2.')';
    }

    abstract protected function getLangCategory();
}