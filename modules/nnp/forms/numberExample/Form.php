<?php

namespace app\modules\nnp\forms\numberExample;

use app\modules\nnp\models\NumberExample;

abstract class Form extends \app\classes\Form
{
    /** @var NumberExample */
    public $numberExample;

    /**
     * @return NumberExample
     */
    abstract public function getNumberExampleModel();

    /**
     * Конструктор
     *
     */
    public function init()
    {
        $this->numberExample = $this->getNumberExampleModel();
    }
}
