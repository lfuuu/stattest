<?php

namespace app\classes;

use app\models\Country;

class Language
{
    const DEFAULT_LANGUAGE = 'ru-RU'; //RUSSIAN

    public static function setCurrentLanguage($lang = null)
    {
        if (!self::languageExists($lang)) {
            if (\Yii::$app->session->has('language') && self::languageExists(\Yii::$app->session->get('language')))
                $lang = \Yii::$app->session->get('language');
            else
                $lang = static::DEFAULT_LANGUAGE;
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

    private static function languageExists($lang)
    {
        return array_key_exists($lang, \app\models\Language::getList());
    }

    private static function setAppLanguage($lang)
    {
        \Yii::$app->language = $lang;
    }
}