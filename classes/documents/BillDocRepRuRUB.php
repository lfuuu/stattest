<?php

namespace app\classes\documents;

use app\models\Currency;

class BillDocRepRuRUB extends DocumentReport
{

    public function getLanguage()
    {
        return 'ru';
    }

    public function getCurrency()
    {
        return Currency::RUB;
    }

    public function getDocType()
    {
        return self::BILL_DOC_TYPE;
    }

    public function getName()
    {
        return 'Счет (предоплата)';
    }

    protected function postProcessingLines()
    {
        foreach($this->lines as &$line){
            if (
                $line['price'] > 0
                && preg_match('/^\s*Абонентская\s+плата|^\s*Поддержка\s+почтового\s+ящика|^\s*Виртуальная\s+АТС|^\s*Перенос|^\s*Выезд|^\s*Сервисное\s+обслуживание|^\s*Хостинг|^\s*Подключение|^\s*Внутренняя\s+линия|^\s*Абонентское\s+обслуживание|^\s*Услуга\s+доставки|^\s*Виртуальный\s+почтовый|^\s*Размещение\s+сервера|^\s*Настройка[0-9a-zA-Zа-яА-Я]+АТС|^Дополнительный\sIP[\s\-]адрес|^Поддержка\sпервичного\sDNS|^Поддержка\sвторичного\sDNS|^Аванс\sза\sподключение\sинтернет-канала|^Администрирование\sсервер|^Обслуживание\sрабочей\sстанции|^Оптимизация\sсайта|^Неснижаемый\sостаток/', $line['item'])
            ) {
                $line['item'] = str_replace('Абонентская', 'абонентскую', str_replace('плата', 'плату', $line['item']));
                $line['item'] = str_replace('Поддержка', 'поддержку', $line['item']);
                $line['item'] = str_replace('Виртуальная', 'виртуальную', $line['item']);
                $line['item'] = str_replace('Перенос', 'перенос', $line['item']);
                $line['item'] = str_replace('Выезд', 'выезд', $line['item']);
                $line['item'] = str_replace('Сервисное', 'сервисное', $line['item']);
                $line['item'] = str_replace('Хостинг', 'хостинг', $line['item']);
                $line['item'] = str_replace('Подключение', 'подключение', $line['item']);
                $line['item'] = str_replace('Внутренняя линия','внутреннюю линию', $line['item']);
                $line['item'] = str_replace('Услуга', 'услугу', $line['item']);
                $line['item'] = str_replace('Виртуальный', 'виртуальный', $line['item']);
                $line['item'] = str_replace('Размещение', 'размещение', $line['item']);
                $line['item'] = str_replace('Аванс за', '', $line['item']);
                $line['item'] = str_replace('Оптимизация', 'оптимизацию', $line['item']);
                $line['item'] = str_replace('Обслуживание', 'обслуживание', $line['item']);
                $line['item'] = str_replace('Администрирование', 'администрирование', $line['item']);

                $line['item'] = 'Авансовый платеж за ' . $line['item'];

            }
        }
        return parent::postProcessingLines();
    }

}