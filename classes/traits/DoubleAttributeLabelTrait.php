<?php

namespace app\classes\traits;

use yii\helpers\Inflector;
use app\classes\Language;

trait DoubleAttributeLabelTrait
{
    public $formLang;

    /**
     * Формирует перевод значения поля формы.
     * Основной перевод - язык пользователя
     * Дополнительный перевод - язык контрагента. Через " / ", если он отличается от языка пользователя
     *
     * @param string $name
     * @return string
     */
    public function generateAttributeLabel($name)
    {
        $category = $this->getLangCategory();
        $userLang = Language::getCurrentLanguage();
        $formLang = $this->formLang;
        $userLangLabel = \Yii::t($category, $name, [], $userLang);
        $contragentLangLabel = ($formLang == $userLang || !$formLang) ? null : \Yii::t($category, $name, [], $formLang);

        if ($userLangLabel == $name) { // нет перевода
            return Inflector::camel2words($name, true);
        } elseif ($contragentLangLabel === null) {
            return $userLangLabel;
        } elseif ($contragentLangLabel == $name) { // нет перевода
            return $userLangLabel;
        } elseif ($userLangLabel == $contragentLangLabel) { // перевод одинаковый
            return $userLangLabel;
        } else {
            return $userLangLabel . ' / ' . $contragentLangLabel;
        }
    }

    /**
     * @return string
     */
    abstract protected function getLangCategory();

}