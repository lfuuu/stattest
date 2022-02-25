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
        try {
            Assert::isObject($number, 'Номер не найден');
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', $e->getMessage());
            return $this->redirect('/voip/number');
        }

        // редактирование модели
        $post = Yii::$app->request->post();
        if (
            $post && $number->load($post) // загрузить beauty_level
            && ($number->did_group_id = DidGroup::dao()->getIdByNumber($number)) // по beauty_level установить did_group_id
            && $number->save()
        ) {
            return $this->redirect(['view', 'did' => $did]);
        }

        $do = \Yii::$app->request->get('do');

        if ($do == 'checkStatus') {
            Number::dao()->actualizeStatus($number);
            return $this->redirect($number->url);
        }

        if (in_array($do, ['forcePort', 'forcePortAndSync'])) {
            try {
                \app\modules\nnp\models\Number::forcePorting($number->number, $do == 'forcePortAndSync');
            } catch (Exception $e) {
                \Yii::$app->session->addFlash('error', $e->getMessage());
            }
            return $this->redirect($number->url);
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

        $whoStrMap = [
            '' => '',
            'set-status' => 'Статус',
            'set-beauty-level' => 'Степень красивости',
            'set-did-group' => 'DID-группа',
        ];

        $numbers = isset($post['numbers']) ? json_decode($post['numbers']) : [];
        $status = $post['status'] ?? null;
        $beautyLevel = $post['beauty-level'] ?? null;
        $didGroupId = $post['did_group_id'] ?? null;

        $action = '';
        foreach ([NumberFilter::ACTION_SET_STATUS, NumberFilter::ACTION_SET_BEAUTY_LEVEL, NumberFilter::ACTION_SET_DID_GROUP] as $action) {
            if (isset($post[$action])) {
                break;
            }
        }

        if (!$action) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        $whoStr = $whoStrMap[$action];

        $transaction = Yii::$app->db->beginTransaction();

        $number = null;

        try {
            if ($action == NumberFilter::ACTION_SET_STATUS && !in_array($status, array_keys(Number::$statusList))) {
                throw new InvalidArgumentException('Неизвестный статус');
            } elseif ($action == NumberFilter::ACTION_SET_BEAUTY_LEVEL && !array_key_exists($beautyLevel, DidGroup::$beautyLevelNames + ['original' => ''])) {
                throw new InvalidArgumentException('Неизвестная степень красивости');
            } elseif ($action == NumberFilter::ACTION_SET_DID_GROUP && !array_key_exists($didGroupId, DidGroup::getList())) {
                throw new InvalidArgumentException('Неизвестная DID-группа');
            }

            $numbersQuery = Number::find()
                ->where(['number' => $numbers])
                ->joinWith('registry');

            if (!$numbers) {
                throw new InvalidArgumentException('Выберите номера');
            }

            $cacheDidGroup = [];

            $count = 0;
            /** @var Number $number */
            foreach ($numbersQuery->each() as $number) {
                if ($action == NumberFilter::ACTION_SET_STATUS) {
                    switch ($status) {
                        case Number::STATUS_INSTOCK:
                            NumberDao::me()->toInstock($number, true);
                            break;

                        case Number::STATUS_RELEASED:
                            NumberDao::me()->toRelease($number, true, $status);
                            break;

                        case Number::STATUS_NOTSALE:
                            NumberDao::me()->startNotSell($number);
                            break;

                        default:
                            throw new \LogicException('Неправильный статус');
                    }
                } elseif ($action == NumberFilter::ACTION_SET_BEAUTY_LEVEL) {
                    // set beauty level
                    /** @var \app\models\Number $number */
                    $newBeautyLevel = $beautyLevel == 'original' ? $number->original_beauty_level : $beautyLevel;

                    if ($number->beauty_level == $newBeautyLevel) {
                        continue;
                    }

                    $number->beauty_level = $newBeautyLevel;

                    $registry = $number->registry;

                    if (!$registry) {
                        throw new InvalidArgumentException('Не установлен реестр');
                    }

                    $didGroupId = DidGroup::dao()->getIdByNumber($number);

                    if (isset($cacheDidGroup[$didGroupId])) {
                        $didGroup = $cacheDidGroup[$didGroupId];
                    } else {
                        $didGroup = DidGroup::findOne(['id' => $didGroupId]);
                        $cacheDidGroup[$didGroupId] = $didGroup;
                    }

                    $numberParams = [
                        'number' => $number->number,
                        'beautyLevel' => \Yii::t('app', DidGroup::$beautyLevelNames[$newBeautyLevel])
                    ];

                    if (!$didGroup) {
                        throw new \LogicException(
                            \Yii::t(
                                'number',
                                'For the number {number} ({ndc_type}) with beauty: "{beautyLevel}" no DID group was found',
                                $numberParams + ['ndc_type' => $registry->ndcType->name]
                            )
                        );
                    }

                    if ($didGroup->ndc_type_id != $registry->ndc_type_id) {
                        throw new \LogicException(
                            \Yii::t(
                                'app',
                                'Number type {number} ("{beautyLevel}") in the DID-group (id: {didId}) and in the registry do not match',
                                $numberParams + ['didId' => $didGroup->id]
                            )
                        );
                    }

                    $number->did_group_id = $didGroup->id;

                    if (!$number->save()) {
                        throw new ModelValidationException($numbers);
                    }
                } elseif ($action == NumberFilter::ACTION_SET_DID_GROUP) {
                    /** @var \app\models\Number $number */
                    $number->did_group_id = $didGroupId;

                    if (!$number->save()) {
                        throw new ModelValidationException($numbers);
                    }

                }

                $count++;
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', $whoStr . ' был изменен у ' . $count . ' номера(ов)');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', ($number ? 'Нельзя изменить ' . mb_strtolower($whoStr) . ' у номера ' . $number->number . ' ' : '') . '<br/>' . Html::tag('small', $e->getMessage()));
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}