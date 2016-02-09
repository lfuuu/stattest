<?php
namespace app\controllers\bill;

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

    public function actionIndex()
    {
        return $this->render('index', []);
    }

    /**
     * Публикация счетов в регионе
     *
     * @param int $region
     */
    public function actionRegion($region)
    {
        $result =
            Yii::$app->db->createCommand('
                UPDATE `newbills` nb LEFT JOIN `clients` c ON c.`id` = nb.`client_id`
                SET
                    nb.`is_lk_show` = 1
                WHERE
                    nb.`bill_no` LIKE :bill_no
                    AND !nb.`is_lk_show`
                    AND c.`region` = :region',
                [
                    ':bill_no' => date('Ym') . '-%',
                    ':region' => $region,
                ]
            )
            ->execute();

        Yii::$app->session->addFlash('success', 'Опубликовано ' . $result . ' счетов');
        return $this->redirect('/bill/publish/index');
    }
}