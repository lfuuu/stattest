<?php

namespace app\helpers\usages;

use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\models\UsageTechCpe;

class UsageTechCpeHelper extends Object implements UsageHelperInterface
{

    private $usage;

    public function __construct($usage)
    {
        $this->usage = $usage;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Клиентские устройства';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->usage->model->vendor . ' ' . $this->usage->model->model, '', ''];
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
        return Url::toRoute(['/', 'module' => 'routers', 'action' => 'd_edit', 'id' => $this->usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageTechCpe::findOne($this->usage->prev_usage_id);
    }

}