<?php

namespace app\forms\tariff;

use app\classes\Form;
use app\models\DidGroup;
use InvalidArgumentException;
use yii;

abstract class DidGroupForm extends Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var DidGroup */
    public $didGroup;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return DidGroup
     */
    abstract public function getDidGroupModel();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->didGroup = $this->getDidGroupModel();

        // Обработать submit (создать, редактировать, удалить)
        $this->loadFromInput();
    }

    /**
     * Обработать submit (создать, редактировать, удалить)
     */
    protected function loadFromInput()
    {
        // загрузить параметры от юзера
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            // название
            if (isset($post['dropButton'])) {

                // удалить
                $this->didGroup->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->didGroup->load($post)) {

                if (isset($post['isFake']) && $post['isFake']) {
                    // установили значения и хватит
                } else {
                    // создать/редактировать
                    if ($this->didGroup->validate() && $this->didGroup->save()) {
                        $this->id = $this->didGroup->id;
                        $this->isSaved = true;
                    } else {
                        // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                        $this->validateErrors += $this->didGroup->getFirstErrors();
                    }
                }
            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException();
            }

            $transaction->commit();

        } catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;

            if (!count($this->validateErrors)) {
                $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
            }
        }
    }
}
