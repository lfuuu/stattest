<?php

namespace app\classes\grid\account\partner\maintenance;

use yii\db\Query;
use yii\db\Expression;

trait PartherMaintanceTrait
{

    /**
     * @param Query $query
     */
    public function extendQuery(Query $query)
    {
        $query->innerJoin(
            new Expression('
                (
                    SELECT
                        cg.`partner_contract_id`,
                        SUM((SELECT COUNT(*) FROM `usage_voip` WHERE CAST(NOW() AS DATE) BETWEEN `actual_from` AND `actual_to` AND `client` = ccc.`client`)) AS usage_voip,
                        SUM((SELECT COUNT(*) FROM `usage_virtpbx` WHERE CAST(NOW() AS DATE) BETWEEN `actual_from` AND `actual_to` AND `client` = ccc.`client`)) AS usage_virtpbx
                    FROM
                        `clients` ccc
                            INNER JOIN `client_contract` `cr` ON ccc.`contract_id` = cr.`id`
                                INNER JOIN `client_contragent` `cg` ON cr.`contragent_id` = cg.`id`
                    WHERE
                        cg.`partner_contract_id`
                   GROUP BY cg.`partner_contract_id`
                   ' . ($this->partner_clients_service ? 'HAVING ' . $this->partner_clients_service . ' > 0' : '') . '
                )
            '),
            'c.`id` = expression.`partner_contract_id`'
        );
        $query->addSelect(['usage_voip', 'usage_virtpbx']);
    }

    /**
     * @param array $columns
     * @return array
     */
    public function appendServiceColumn(array $columns)
    {
        $columns['partner_clients_service']['filter'] = function () {
            return \yii\helpers\Html::dropDownList(
                'partner_clients_service',
                \Yii::$app->request->get('partner_clients_service'),
                [
                    'usage_virtpbx' => 'ВАТС',
                    'usage_voip' => 'Телефония',
                ],
                ['class' => 'form-control', 'prompt' => '- Не выбрано -', 'style' => 'width: 120px;',]
            );
        };
        return $columns;
    }

}