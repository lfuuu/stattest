<?php

namespace app\helpers\usages;

use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\models\usages\UsageInterface;
use app\models\UsageExtra;

class UsageExtraHelper extends Object implements UsageHelperInterface
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
        return 'Доп. услуги';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [($this->usage->tariff ? $this->usage->tariff->description : 'Описание'), '', ''];
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
        return Url::toRoute(['/pop_services.php', 'table' => UsageExtra::tableName(), 'id' => $this->usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageExtra::findOne($this->usage->prev_usage_id);
    }

}