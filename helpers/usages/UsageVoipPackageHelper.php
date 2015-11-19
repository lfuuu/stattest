<?php

namespace app\helpers\usages;

use yii\base\Object;
use app\models\usages\UsageInterface;

class UsageVoipPackageHelper extends Object implements UsageHelperInterface
{

    private $usage;

    public function __construct(UsageInterface $usage)
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