<?php

namespace app\classes\usages;

use yii\base\Object;
use app\models\Usage;
use yii\helpers\Url;

class UsageVoipTrunkHelper extends Object implements UsageHelperInterface
{

    private $usage;

    public function __construct(Usage $usage)
    {
        $this->usage = $usage;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Телефония транки';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->usage->description ?: 'Описание отсутствует', '', ''];
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getEditLink()
    {
        return Url::toRoute(['/usage/trunk/edit', 'id' => $this->usage->id]);
    }

}