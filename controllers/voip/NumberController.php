<?php
/**
 * Номера
 */

namespace app\controllers\voip;

use app\classes\Assert;
use app\classes\BaseController;
use app\classes\Html;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\voip\forms\NumberFormNew;
use app\dao\NumberDao;
use app\exceptions\ModelValidationException;
use app\forms\usage\NumberForm;
use app\models\Country;
use app\models\DidGroup;
use app\models\filter\voip\NumberFilter;
use app\models\Number;
use Exception;
use InvalidArgumentException;
use Yii;
use yii\filters\AccessControl;

class NumberController extends BaseController
{
    // Вернуть текущего клиента, если он есть
    use AddClientAccountFilterTraits;

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
                        'actions' => ['view'],
                        'roles' => ['services_voip.e164'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['stats.report'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['change-status'],
                        'roles' => ['voip.change-number-status'],
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
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $filterModel = new NumberFilter();

        $get = Yii::$app->request->get();
        $className = $filterModel->formName();
        !isset($get[$className]['country_id']) && $get[$className]['country_id'] = Country::RUSSIA;

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
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        /** @var NumberFormNew $form */
        $form = new NumberFormNew();

        if ($form->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'formModel' => $form,
        ]);
    }

    /**
     * Просмотр и изменения состояния
     *
     * @param int $did
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionView($did)
    {
        $number = Number::findOne($did);
        Assert::isObject($number);

        // редактирование модели
        $post = Yii::$app->request->post();
        if (
            $post && $number->load($post) // загрузить beauty_level
            && ($number->did_group_id = DidGroup::dao()->getIdByNumber($number)) // по beauty_level установить did_group_id
            && $number->save()
        ) {
            return $this->redirect(['view', 'did' => $did]);
        }

        // редактирование формы
        $actionForm = new NumberForm();
        $actionForm->number_tech = $number->number_tech;
        if ($actionForm->load($post) && $actionForm->validate() && $actionForm->process()) {
            return $this->redirect(['view', 'did' => $did]);
        }

        if ($actionForm->hasErrors()) {
            foreach ($actionForm->firstErrors as $error) {
                Yii::$app->session->addFlash('error', $error);
            }
        }

        $actionForm->scenario = 'default';
        $actionForm->did = $did;
        $actionForm->client_account_id = $this->_getCurrentClientAccountId();

        return $this->render('view', [
            'number' => $number,
            'logList' => $number->getChangeStatusLog(),
            'actionForm' => $actionForm,
        ]);
    }

    /**
     * Массовое изменение статуса
     * @return \yii\web\Response
     * @throws Exception
     * @internal param string $numbers
     * @internal param string $status
     */
    public function actionChangeStatus()
    {
        $post = Yii::$app->request->post();

        $numbers = isset($post['numbers']) ? json_decode($post['numbers']) : [];
        $status = isset($post['status']) ? $post['status'] : null;

        if (!$numbers || !$status) {
            throw new InvalidArgumentException('Выберите номера и статус');
        }
        $numbersQuery = Number::find()->where(['number' => $numbers]);
        $transaction = Yii::$app->db->beginTransaction();

        $number = null;
        try {
            $count = 0;
            foreach ($numbersQuery->each() as $number) {
                switch ($status) {
                    case Number::STATUS_INSTOCK:
                        NumberDao::me()->toInstock($number, true);
                        break;

                    case Number::STATUS_RELEASED:
                        NumberDao::me()->toRelease($number, true);
                        break;

                    case Number::STATUS_NOTSALE:
                        NumberDao::me()->startNotSell($number);
                        break;

                    default:
                        throw new \LogicException('Неправильный статус');
                }

                $count++;
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Статус был изменен у ' . $count . ' номера(ов)');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', ($number ? 'Нельзя изменить статус у номера ' . $number->number . ' ' : '') . '<br/>' . Html::tag('small', $e->getMessage()));
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}