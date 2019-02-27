<?php

namespace app\classes\traits;

use app\models\Language;
use yii\base\UnknownPropertyException;

/**
 * Определяет принцип поиска свойства у модели, для которой определен аналог EAV pattern
 */
trait I18NGetTrait
{
    protected static $existed = [];
    protected static $dictionary = [];

    /**
     * @param string $name
     * @return string
     * @throws UnknownPropertyException
     */
    public function __get($name)
    {
        // Проверка существование свойства путем вызова оригинального getter
        $propertyKey = $this->getExistedKey($name);
        if (!array_key_exists($propertyKey, self::$existed)) {
            try {
                $result = parent::__get($name);
                self::$existed[$propertyKey] = 1;
                return $result;
            } catch(\Exception $e) {
                self::$existed[$propertyKey] = 0;
            }
        }

        if (self::$existed[$propertyKey]) {
            return parent::__get($name);
        }

        // Поиск свойства в языковом словаре свойств
        $dictKey = $this->getDictionaryKey($name, $this->langCode);
        if (array_key_exists($dictKey, self::$dictionary)) {
            return self::$dictionary[$dictKey];
        }

        // Заполняем языковой словарь
        $this->addToDictionary($this->langCode);
        if (array_key_exists($dictKey, self::$dictionary)) {
            return self::$dictionary[$dictKey];
        }

        // Поиск свойства в словаре свойств по умолчанию (ru-RU)
        $dictKey = $this->getDictionaryKey($name, Language::LANGUAGE_DEFAULT);
        if (array_key_exists($dictKey, self::$dictionary)) {
            return self::$dictionary[$dictKey];
        }

        // Заполняем словарь по умолчанию (ru-RU)
        $this->addToDictionary(Language::LANGUAGE_DEFAULT);
        if (array_key_exists($dictKey, self::$dictionary)) {
            return self::$dictionary[$dictKey];
        }

        // Проверка существования записи
        if (!$this->getPrimaryKey()) {
            return '';
        }

        // Запись существует, свойство не было найдено
        throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Generates key for existed properties
     *
     * @param $name string
     * @return string
     */
    protected function getExistedKey($name)
    {
        return get_class($this) . ':' . $name;
    }

    /**
     * Generates dictionary key
     *
     * @param $name string
     * @param $langCode string
     * @return string
     */
    protected function getDictionaryKey($name, $langCode)
    {
        return get_class($this) . ':' . $this->getPrimaryKey() . ':' . $langCode . ':' . $name;
    }

    /**
     * Adds i18n data to dictionary
     *
     * @param $langCode
     */
    public function addToDictionary($langCode)
    {
        $i18n = $this->getI18N($langCode);
        foreach ($i18n as $key => $value) {
            $dictKey = $this->getDictionaryKey($key, $langCode);
            self::$dictionary[$dictKey] = $value;
        }
    }
}