<?php
namespace app\classes\grid\account\partner\maintenance;

use app\classes\grid\account\AccountGridFolder;
use app\models\BusinessProcessStatus;
use Yii;
use yii\db\Query;


class ActingFolder extends AccountGridFolder
{
    public function getName()
    {
        return 'Действующий';
    }

    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'contractNo',
            'contract_created',
            'manager',
            'account_manager',
            'region',
            'contract_type',
            'service'
        ];
    }

    public function queryParams(Query $query)
    {
        parent::queryParams($query);

        $query->andWhere(['cr.business_id' => $this->grid->getBusiness()]);
        $query->andWhere(['cr.business_process_status_id' => BusinessProcessStatus::PARTNER_MAINTENANCE_ACTING]);
    }

    protected function getDefaultColumns()
    {
        $columns = parent::getDefaultColumns();
        $columns['service']['filter'] = function () {
            return \yii\helpers\Html::dropDownList(
                'service',
                \Yii::$app->request->get('service'),
                [
                    'usage_virtpbx' => 'ВАТС',
                    'usage_voip' => 'Телефония',
                ],
                ['class' => 'form-control', 'prompt' => '-Не выбрано-', 'style' => 'max-width:50px;',]
            );
        };
        return $columns;
    }
}