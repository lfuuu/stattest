<?php

namespace app\classes\usages;

use yii\base\Object;
use app\models\Usage;

class UsageVoipPackageHelper extends Object implements UsageHelperInterface
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
        return 'Телефония пакет';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->usage->id, '', ''];
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
        return '';
    }

}