<?php

namespace app\classes\traits;

use Yii;
use yii\base\UnknownPropertyException;

/**
 * Определяет принцип поиска свойства у модели, для которой определен аналог EAV pattern
 */
trait I18NGetTrait
{

    /**
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        // Проверка существование свойства путем вызова оригинального getter
        try {
            return parent::__get($name);
        } catch(\Exception $e) {
            // Поиск свойства в языковом словаре свойств
            $i18n = $this->getI18N($this->langCode);
            if (array_key_exists($name, $i18n)) {
                return $i18n[$name];
            }

            // Поиск свойства в словаре свойств по-умолчанию (ru-RU)
            $i18n = $this->getI18N();
            if (array_key_exists($name, $i18n)) {
                return $i18n[$name];
            }

            // Проверка существования записи
            if (!$this->getPrimaryKey()) {
                return '';
            }
            // Запись существует, свойство не было найдено
            else {
                throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
            }
        }
    }

}