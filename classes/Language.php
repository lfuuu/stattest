<?php

namespace app\classes;

use app\models\Country;

class Language
{
    static private $languageList;

    public static function setCurrentLanguage($lang = null)
    {
        if (!self::languageExists($lang)) {
            if (\Yii::$app->session->has('language') && self::languageExists(\Yii::$app->session->get('language')))
                $lang = \Yii::$app->session->get('language');
            else
                $lang = self::getLanguageList()[0];
        }

        \Yii::$app->session->set('language', $lang);
        self::setAppLanguage($lang);
    }

    public static function getCurrentLanguage()
    {
        return \Yii::$app->language;
    }

    public static function getLanguageByCountryId($id)
    {
        $country = Country::findOne($id);
        return $country ? $country->lang : null;
    }

    public static function getLanguageExtension($lang = null)
    {
        if(!$lang)
            $lang = self::getCurrentLanguage();
        return (explode('-', $lang)[0]);
    }

    public static function getLanguageList()
    {
        if(!self::$languageList){
            $langs = [];
            foreach(\app\models\Language::find()->all() as $lang)
                $langs[] = $lang->code;
            self::$languageList = $langs;
        }
        return self::$languageList;
    }

    private static function languageExists($lang)
    {
        return in_array($lang, self::getLanguageList());
    }

    private static function setAppLanguage($lang)
    {
        \Yii::$app->language = $lang;
    }
}