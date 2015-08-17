<?php
namespace app\classes\validators;

use yii\validators\Validator;

class InnValidator extends Validator
{

    public function init()
    {
        parent::init();
        $this->message = 'Incorrect INN';
    }

    public function validateValue($value)
    {
        if(!$this->checkInn($value)){
            return [$this->message];
        }
        return null;
    }

    private function checkInn($inn)
    {
        if (strlen($inn) == 10)
        {
            return $this->checkInn10($inn);
        } else if (strlen($inn) == 12)
        {
            return $this->checkInn12($inn);
        } else if (strlen($inn) == 13)
        {
            return $this->checkInnHU($inn);
        }
        return false;
    }

    private function checkInn10($inn)
    {
        $f1 = [2,4,10,3,5,9,4,6,8];
        $n10 = 0;

        foreach($f1 as $k => $v)
            $n10 += $inn[$k] * $v;


        $n10 %= 11;
        $n10 %= 10;

        return $inn[9] == $n10;
    }

    private function checkInn12($inn)
    {
        $f1 = [7,2,4,10,3,5,9,4,6,8];
        $f2 = [3,7,2,4,10,3,5,9,4,6,8];

        $n11 = 0;
        foreach($f1 as $k => $v)
            $n11 += $inn[$k] * $v;

        $n11 %= 11;
        $n11 %= 10;

        $n12 = 0;
        foreach($f2 as $k => $v)
            $n12 += $inn[$k] * $v;

        $n12 %= 11;
        $n12 %= 10;

        return $inn[10] == $n11 && $inn[11] == $n12;
    }

    private function checkInnHU($inn)
    {
        return boolval(preg_match('/^\d{8}-\d{1}-\d{2}$/', $inn));
    }
}