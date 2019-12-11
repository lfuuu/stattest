<?php

namespace app\modules\sbisTenzor\forms\contractor;

use app\exceptions\ModelValidationException;
use app\modules\sbisTenzor\models\SBISContractor;
use Yii;

class AddForm extends \app\classes\Form
{
    /**
     * Index form constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ссылка на страницу со списком контрагентов
     *
     * @return string
     */
    public static function getIndexUrl()
    {
        return '/sbisTenzor/contractor/roaming/';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Добавить роуминг';
    }

    /**
     * Попробовать сохранить контрагентов, если POST
     *
     * @return bool
     * @throws ModelValidationException
     */
    public function tryToSave()
    {
        $post = Yii::$app->request->post();
        if (!empty($post['exchange_id'])) {
            $query = SBISContractor::find()->where(['exchange_id' => $post['exchange_id']]);

            foreach ($query->each() as $contractor) {
                /** @var SBISContractor $contractor */
                $contractor->is_roaming = !$contractor->is_roaming;

                if (!$contractor->save()) {
                    throw new ModelValidationException($contractor);
                }
            }

            return true;
        }

        return false;
    }
}