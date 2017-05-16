<?php
/**
 * Номера
 */

namespace app\controllers\voip;

use app\classes\Assert;
use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\voip\forms\NumberFormNew;
use app\forms\usage\NumberForm;
use app\models\Country;
use app\models\filter\voip\NumberFilter;
use app\models\Number;
use Yii;
use yii\filters\AccessControl;

class NumberController extends BaseController
{
    // Вернуть текущего клиента, если он есть
    use AddClientAccountFilterTraits;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['services_voip.e164'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['stats.report'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Вернуть имя колонки, в которую надо установить фильтр по клиенту
     *
     * @return string
     */
    protected function getClientAccountField()
    {
        return 'client_id';
    }

    /**
     * Список
     *
     * @return string
     */
    public function actionIndex()
    {
        $filterModel = new NumberFilter();

        $get = Yii::$app->request->get();
        $className = $filterModel->formName();
        !isset($get[$className]['country_id']) && $get[$className]['country_id'] = Country::RUSSIA;
//        !isset($get[$className]['ndc_type_id']) && $get[$className]['ndc_type_id'] = NdcType::ID_GEOGRAPHIC;

        $this->_addClientAccountFilter($filterModel, $get);

        return $this->render('index', [
            'filterModel' => $filterModel,
            'currentClientAccountId' => $this->_getCurrentClientAccountId(),
        ]);
    }

    /**
     * Создать
     *
     * @return string
     */
    public function actionNew()
    {
        /** @var NumberFormNew $form */
        $form = new NumberFormNew();

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
     * Просмотр и изменения состояния
     *
     * @param int $did
     * @return string
     */
    public function actionView($did)
    {
        $number = Number::findOne($did);
        Assert::isObject($number);

        $actionForm = new NumberForm();
        $actionForm->number_tech = $number->number_tech;

        if ($actionForm->load(Yii::$app->request->post()) && $actionForm->validate() && $actionForm->process()) {
            return $this->redirect(['view', 'did' => $did]);
        }

        if ($actionForm->hasErrors()) {
            foreach ($actionForm->firstErrors as $error) {
                Yii::$app->session->addFlash('error', $error);
            }
        }

        $actionForm->scenario = 'default';
        $actionForm->did = $did;
        global $fixclient_data;
        $actionForm->client_account_id = $fixclient_data ? $fixclient_data['id'] : null;

        return $this->render('view', [
            'number' => $number,
            'logList' => $number->getChangeStatusLog(),
            'actionForm' => $actionForm,
        ]);
    }
}