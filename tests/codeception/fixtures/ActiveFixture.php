<?php
namespace app\tests\codeception\fixtures;

class ActiveFixture extends \yii\test\ActiveFixture
{
    /**
     * @inheritdoc
     */
    public function unload()
    {
        $this->resetTable();
        parent::unload();
    }
}