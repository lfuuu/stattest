<?php

namespace app\modules\sim\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\modules\sim\filters\CardFilter;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * SIM-карты
 */
class CardController extends BaseController
{
    // Установить юзерские фильтры + добавить фильтр по клиенту, если он есть
    use AddClientAccountFilterTraits;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['sim.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit'],
                        'roles' => ['sim.write'],
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
        $filterModel = new CardFilter();
        // $this->_addClientAccountFilter($filterModel);
        $filterModel->load(Yii::$app->request->get());

        return $this->render('index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Создать
     *
     * @return string
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew()
    {
        $card = new Card;
        $card->is_active = true;
        $card->status_id = CardStatus::ID_DEFAULT;

        $imsies = [];
        if ($this->loadFromInput($card, $imsies, new Imsi())) {
            return $this->redirect($card->getUrl());
        }

        return $this->render('edit', [
            'card' => $card,
        ]);
    }

    /**
     * Редактировать
     *
     * @param int $iccid
     * @return string
     * @throws \yii\db\Exception
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit($iccid)
    {
        $card = Card::findOne(['iccid' => $iccid]);
        if (!$card) {
            throw new NotFoundHttpException();
        }


        $imsies = $card->imsies;
        if ($this->loadFromInput($card, $imsies, new Imsi())) {
            return $this->redirect($card->getUrl());
        }

        return $this->render('edit', [
            'card' => $card,
        ]);
    }
}
