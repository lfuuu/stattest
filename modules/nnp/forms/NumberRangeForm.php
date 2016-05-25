<?php

namespace app\modules\nnp\forms;

use app\classes\Form;
use app\modules\nnp\models\NumberRange;
use InvalidArgumentException;
use yii;

abstract class NumberRangeForm extends Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var NumberRange */
    public $numberRange;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return NumberRange
     */
    abstract public function getNumberRangeModel();

    /**
     * конструктор
     */
    public function init()
    {
        $this->numberRange = $this->getNumberRangeModel();

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

            if ($this->numberRange->load($post)) {

                // создать/редактировать
                if ($this->numberRange->validate() && $this->numberRange->save()) {
                    $this->id = $this->numberRange->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->numberRange->getFirstErrors();
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
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
        }
    }
}
