<?php

namespace app\forms\dictonary\city_billing_method;

use Yii;
use InvalidArgumentException;
use app\classes\Form;

abstract class CityBillingMethodForm extends Form
{

    public
        $id,
        $isSaved = false,
        $validateErrors = [],
        $record;

    abstract public function getRecordModel();

    public function init()
    {
        $this->record = $this->getRecordModel();
        $this->loadFromInput();
    }

    protected function loadFromInput()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            if (isset($post['dropButton'])) {
                $this->record->delete();
                $this->id = null;
                $this->isSaved = true;

            }
            elseif ($this->record->load($post)) {
                if ($this->record->validate() && $this->record->save()) {
                    $this->id = $this->record->id;
                    $this->isSaved = true;
                }
                else {
                    $this->validateErrors += $this->record->getFirstErrors();
                }
            }

            if ($this->validateErrors) {
                throw new InvalidArgumentException();
            }

            $transaction->commit();

        }
        catch (InvalidArgumentException $e) {
            $transaction->rollBack();
            $this->isSaved = false;

        }
        catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            $this->isSaved = false;
            $this->validateErrors[] = YII_DEBUG ? $e->getMessage() : Yii::t('common', 'Internal error');
        }
    }
}
