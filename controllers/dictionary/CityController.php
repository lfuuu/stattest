<?php
/**
 * Страны
 */

namespace app\controllers\dictionary;

use app\classes\BaseController;
use app\classes\dictionary\forms\CityFormEdit;
use app\classes\dictionary\forms\CityFormNew;
use app\models\filter\CityFilter;
use app\modules\nnp\models\City;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

class CityController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['ajax-city-list'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['dictionary.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['dictionary.city'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new CityFilter();
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        /** @var CityFormNew $form */
        $form = new CityFormNew();

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }

    /**
     * Редактировать
     *
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($id)
    {
        /** @var CityFormEdit $form */
        $form = new CityFormEdit([
            'id' => $id
        ]);

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
            return $this->redirect(['index']);
        } else {
            return $this->render('edit', [
                'formModel' => $form,
            ]);
        }
    }

    public function actionAjaxCityList($country_id)
    {
        if (!Yii::$app->request->isAjax) {
            return;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = '';

        $city_arr = City::getList($isWithEmpty = false, $isWithNullAndNotNull = false, $country_id, null, $minCnt = 1, $minCntActive = 1);

        foreach ($city_arr as $city_id => $value) {
            $data .= '<option value="' . $city_id . '">' . $value . '</option>';
        }

        return $data;
    }

}