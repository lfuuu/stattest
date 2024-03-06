<?php
namespace app\classes\validators;

use yii\validators\Validator;

class InnValidator extends Validator
{

    /**
     * Init
     */
    public function init()
    {
        parent::init();
        $this->message = 'Неправильный ИНН';
    }

    /**
     * Проверка значения
     *
     * @param string $value
     * @return array|null
     */
    public function validateValue($value)
    {
        if (\Yii::$app->isEu()) {
            if ($value && !$this->_checkInnEuro($value) && !$this->_checkInnHU($value) && !$this->_checkInn($value)) {
                return ['Incorrect TIN'];
            }
        } elseif (!$this->_checkInn($value)) {
            return [$this->message];
        }

        return null;
    }

    /**
     * Проверка ИНН
     *
     * @param string $inn
     * @return bool
     */
    private function _checkInn($inn)
    {
        switch (strlen($inn)) {
            case 10: {
                return $this->_checkInn10($inn);
            }

            case 12: {
                return $this->_checkInn12($inn);
            }

            case 13: {
                return $this->_checkInnHU($inn);
            }

            default: {
                return false;
            }
        }
    }

    /**
     * Проверка 10-ти значного ИНН
     *
     * @param string $inn
     * @return bool
     */
    private function _checkInn10($inn)
    {
        $f1 = [2, 4, 10, 3, 5, 9, 4, 6, 8];
        $n10 = 0;

        foreach ($f1 as $k => $v) {
            $n10 += $inn[$k] * $v;
        }

        $n10 %= 11;
        $n10 %= 10;

        return $inn[9] == $n10;
    }

    /**
     * Проверка 12-ти значного ИНН
     *
     * @param string $inn
     * @return bool
     */
    private function _checkInn12($inn)
    {
        $f1 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
        $f2 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];

        $n11 = 0;
        foreach ($f1 as $k => $v) {
            $n11 += $inn[$k] * $v;
        }

        $n11 %= 11;
        $n11 %= 10;

        $n12 = 0;
        foreach ($f2 as $k => $v) {
            $n12 += $inn[$k] * $v;
        }

        $n12 %= 11;
        $n12 %= 10;

        return $inn[10] == $n11 && $inn[11] == $n12;
    }

    /**
     * Проверка венгерского ИНН
     *
     * @param string $inn
     * @return bool
     */
    private function _checkInnHU($inn): bool
    {
        return boolval(preg_match('/^\d{8}-\d{1}-\d{2}$/', $inn));
    }

    /**
     * Проверка Euro ИНН
     *
     * @param string $inn
     * @return bool
     */
    private function _checkInnEuro($inn): bool
    {
        return (bool)preg_match('/^[A-Z]{2}\d{2,13}$/', $inn);
    }
}