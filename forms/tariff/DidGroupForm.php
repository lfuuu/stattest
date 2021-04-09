<?php

namespace app\forms\tariff;

use app\classes\Form;
use app\models\DidGroup;
use app\models\PriceLevel;
use app\models\DidGroupPriceLevel;
use InvalidArgumentException;
use app\exceptions\ModelValidationException;
use Exception;
use yii;

abstract class DidGroupForm extends Form
{
    /** @var DidGroup */
    public $didGroup;

    /** @var DidGroupPriceLevel[] */
    public $didGroupPriceLevels;

    /**
     * @return DidGroup
     */
    abstract public function getDidGroupModel();

    /**
     * @return DidGroupPriceLevel[]
     */
    abstract public function getDidGroupPriceLevels();

    /**
     * Конструктор
     */
    public function init()
    {
        $this->didGroup = $this->getDidGroupModel();
        $this->didGroupPriceLevels = $this->createPriceLevelInputFields($this->getDidGroupPriceLevels(), $this->didGroup->id);
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
            if ($this->didGroup->load($post) && DidGroupPriceLevel::loadMultiple($this->didGroupPriceLevels, $post)) {
                if (isset($post['isFake']) && $post['isFake']) {
                    // установили значения и хватит
                } else {
                    if ($this->didGroup->validate() && $this->didGroup->save()) {
                        $this->id = $this->didGroup->id;
                        $this->addValidDidGroupPriceLevels($this->id);
                        $this->isSaved = true;
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

    //создаем несуществующие поля уровней цен в DID группе
    private function createPriceLevelInputFields($didGroupPriceLevelModel, $id)
    {
        $priceLevels = PriceLevel::getList();
        foreach ($priceLevels as $index => $priceLevel) {
            $isThere = false;
            foreach ($didGroupPriceLevelModel as $didGroupPriceLevel) {
                if ($didGroupPriceLevel['price_level_id'] == $index) {
                    $isThere = true;
                }
            }
            if ($isThere == false) {
                $toAdd = new DidGroupPriceLevel();
                $toAdd->price_level_id = $index;
                $toAdd->did_group_id = intval($id);
                $didGroupPriceLevelModel[] = $toAdd;
            }
        }
        return $didGroupPriceLevelModel;
    }

    //сохраняем заполненые поля и удаляем пустые
    private function addValidDidGroupPriceLevels()
    {
        foreach ($this->didGroupPriceLevels as $model) {
            if ($model['did_group_id'] == null) {
                $model['did_group_id'] = $this->id;
            }
            if ($model['price'] != null) {
                $model->save();
            } else {
                if ($model['id'] != null) {
                    if (!$model->delete()) {
                        throw new \Exception();
                    }
                }
            }
        }
    }
}
