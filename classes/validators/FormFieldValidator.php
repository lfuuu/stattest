<?php
namespace app\classes\validators;

use Yii;
use yii\validators\FilterValidator;

class FormFieldValidator extends FilterValidator
{
    public $filter = null;

    public function init()
    {
        if ($this->filter === null) {
            $this->filter = __CLASS__ . '::cleanField';
        }
        parent::init();
    }

    public function cleanField($value)
    {
        return htmlspecialchars(trim(strip_tags($value)));
    }
}
