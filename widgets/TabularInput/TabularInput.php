<?php

namespace app\widgets\TabularInput;


use yii\db\ActiveRecord;

class TabularInput extends \unclead\multipleinput\TabularInput
{
    /** @var ActiveRecord $newModel Новая (которая добавляется по плюсику) модель */
    public $newModel = null;

    /**
     * Initialization.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->columnClass = TabularColumn::className();

        parent::init();
    }

}