<?php

namespace app\modules\nnp\forms;

use app\classes\Form;
use app\modules\nnp\models\Region;
use InvalidArgumentException;
use yii;

abstract class RegionForm extends Form
{
    /** @var int ID сохраненный модели */
    public $id;

    /** @var bool */
    public $isSaved = false;

    /** @var Region */
    public $region;

    /** @var string[] */
    public $validateErrors = [];

    /**
     * @return Region
     */
    abstract public function getRegionModel();

    /**
     * конструктор
     */
    public function init()
    {
        $this->region = $this->getRegionModel();

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
                $this->region->delete();
                $this->id = null;
                $this->isSaved = true;

            } elseif ($this->region->load($post)) {

                // создать/редактировать
                if ($this->region->validate() && $this->region->save()) {
                    $this->id = $this->region->id;
                    $this->isSaved = true;
                } else {
                    // продолжить выполнение, чтобы показать юзеру массив с недозаполненными данными вместо эталонных
                    $this->validateErrors += $this->region->getFirstErrors();
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
