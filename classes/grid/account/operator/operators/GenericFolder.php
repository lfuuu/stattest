<?php

namespace app\classes\grid\account\operator\operators;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use yii\db\Query;

/**
 * Class GenericFolder
 */
class GenericFolder extends AccountGridFolder
{
    /**
     * @var BusinessProcessStatus $status
     */
    private $status;

	/**
	 * @var array $columns
	 */
    private $columns = [];

    public $_isGenericFolder = true;

	/**
	 * @param BusinessProcessStatus $status
	 * @param array $columns
	 */
    public function initialize(BusinessProcessStatus $status, array $columns)
    {
        $this->status = $status;
        $this->columns = $columns;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->status->name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return md5($this->status->id);
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Query $query
     */
    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => $this->status->id]);
    }
}