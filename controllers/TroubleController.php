<?php
namespace app\controllers;

use app\classes\BaseController;
use app\dao\TroubleDao;
use app\models\Trouble;
use app\models\User;
use Yii;

class TroubleController extends BaseController
{
    public function actionAddStage()
    {
        $post = Yii::$app->request->post();
        if (
               !isset($post['trouble_ids']) || !$post['trouble_ids']
            || !isset($post['user']) || !$post['user']
            || !isset($post['state']) || !$post['state']
        ) {
            Yii::$app->session->setFlash('error', 'Необходимо выбрать пользователя, состояние и траблы');
            return $this->redirect(Yii::$app->request->referrer);
        }

        $currentUser = Yii::$app->user->identity->user;
        $troublesQuery = Trouble::find()
            ->joinWith('stages')
            ->where(['id' => $post['trouble_ids']]);

        if (!\Yii::$app->user->can('tt.admin')) {
            $troublesQuery->andWhere(['user_main' => $currentUser]);
        }

        $userId = User::find()->select('id')->where(['user' => $post['user']])->scalar();

        $state = $post['state'];

        $updatedTroubleIds = [];
        foreach ($troublesQuery->each() as $trouble) {
            TroubleDao::me()->addStage($trouble, $state, '', null, $userId);
            $updatedTroubleIds[] = $trouble->id;
        }
        $errorMsg = '';
        if ($diff = array_diff($post['trouble_ids'], $updatedTroubleIds)) {
            $errorMsg = 'Заявки: ' . implode(', ', $diff) . ' не были обновлены. Они назначены на другого пользователя.';
        }
        if ($errorMsg) {
            Yii::$app->session->setFlash('error', $errorMsg);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}
