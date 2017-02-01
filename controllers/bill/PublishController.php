<?php
namespace app\controllers\bill;

use app\helpers\DateTimeZoneHelper;
use app\models\Organization;
use app\models\Param;
use app\models\Region;
use Yii;
use yii\filters\AccessControl;
use app\classes\BaseController;

class PublishController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['newaccounts_mass.access'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($organizationId = Organization::MCN_TELEKOM, $regionId = Region::HUNGARY)
    {
        $isNotificationsOn = false;
        $switchOffParam = Param::findOne(Param::NOTIFICATIONS_SWITCH_OFF_DATE);
        if ($switchOffParam) {
            $isNotificationsOn = DateTimeZoneHelper::getDateTime($switchOffParam->value);
        }

        $isEnabledRecalcWhenEditBill = !((bool)Param::findOne(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL));

        return $this->render('index', [
            'organizationId' => $organizationId,
            'regionId' => $regionId,
            'isNotificationsOn' => $isNotificationsOn,
            'isEnabledRecalcWhenEditBill' => $isEnabledRecalcWhenEditBill
        ]);
    }

    /**
     * Публикация счетов в регионе
     *
     * @param $regionId
     * @return \yii\web\Response
     */
    public function actionRegion($regionId)
    {
        $result =
            Yii::$app->db->createCommand('
                UPDATE `newbills` nb 
                  LEFT JOIN `clients` c ON c.`id` = nb.`client_id`
                SET
                    nb.`is_lk_show` = 1
                WHERE
                    nb.`bill_no` LIKE :bill_no
                    AND nb.`is_lk_show` = 0
                    AND c.`region` = :region',
                [
                    ':bill_no' => date('Ym') . '-%',
                    ':region' => $regionId,
                ]
            )->execute();

        Yii::$app->session->addFlash('success', 'Опубликовано ' . $result . ' счетов');

        return $this->redirect(['/bill/publish/index', 'regionId' => $regionId]);
    }

    /**
     * Публикация счетов по организации
     *
     * @param int $organizationId
     */
    public function actionOrganization($organizationId)
    {
        $result = Yii::$app->db->createCommand("
                UPDATE newbills b
                  LEFT JOIN `clients` c ON c.`id` = b.`client_id`
                  LEFT JOIN `client_contract` cc ON cc.`id` = c.contract_id
                SET b.is_lk_show = 1
                WHERE
                  b.bill_no LIKE :bill_mask
                  AND cc.organization_id = :organizationId
                  AND b.is_lk_show = 0",
            [
                ':bill_mask' => date('Ym') . '-%',
                ':organizationId' => $organizationId
            ])->execute();

        Yii::$app->session->addFlash('success', 'Опубликовано ' . $result . ' счетов');

        return $this->redirect(['/bill/publish/index', 'organizationId' => $organizationId]);
    }

}