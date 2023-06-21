<?php

namespace app\classes\contragent\importer\lk\typeFactory;

use app\classes\contragent\importer\lk\CoreLkContragent;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\ClientContragent;

class CoreLkContragentTypeDefault
{
    const ORG_TYPE_PHYSICAL = 'physical';
    const ORG_TYPE_BUSINESS = 'business';
    const ORG_TYPE_LEGAL = 'legal';
    const ORG_TYPE_INDIVIDUAL = 'individual';

    public static $orgType = false;

    protected ?CoreLkContragent $coreLkContragent;
    protected ?ClientContragent $contragent;

    private $diffContragent = [];
    private $diffContragentPerson = [];

    public function __construct(CoreLkContragent $coreLkContragent)
    {
        $this->coreLkContragent = $coreLkContragent;
        $this->makeStatModel();
    }

    public function getStatLegalType()
    {
        return false;
    }

    private function checkIfNeed(): array
    {
        if (!isset($this->contragent) || !isset($this->coreLkContragent)) {
            return [];
        }

        $lkContragent = $this->contragent;
        $statContragent = $this->coreLkContragent->getStatContragent();

//        if ($lkContragent->legal_type == ClientContragent::IP_TYPE) {
//            return []; //@TODO
//        }

        $diff = $this->diffContragent = $this->compareModels($lkContragent, $statContragent, 'contragent', $statContragent->id);

        if ($lkContragent->legal_type == ClientContragent::PERSON_TYPE || $lkContragent->legal_type == ClientContragent::IP_TYPE) {

            $lkContragentPerson = $lkContragent->personModel;
            $statContragentPerson = $statContragent->personModel;

            if ($lkContragentPerson && $statContragentPerson) {
                $this->diffContragentPerson = $this->compareModels($lkContragentPerson, $statContragentPerson, '    person', $statContragentPerson->contragent_id);
            }

            $diff = array_merge($this->diffContragentPerson, $diff);
        }

        return $diff;
    }

    public function update(): ?bool
    {
        if (!$this->checkIfNeed()) {
            return null;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->diffContragent) {
                $this->updateModel($this->coreLkContragent->getStatContragent(), $this->diffContragent,);
            }

            if ($this->diffContragentPerson) {
                $this->updateModel($this->coreLkContragent->getStatContragent()->personModel, $this->diffContragentPerson);
            }
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            echo PHP_EOL . 'ERROR: ' . $e->getMessage();
//            throw $e;
        }

        return false;
    }

    private function updateModel(ActiveRecord $model, array $setArray): ActiveRecord
    {
        $model->setAttributes($setArray, false);
        if (!$model->save()) {
            throw new ModelValidationException($model);
        }

        return $model;
    }

    private function compareModels(ActiveRecord $model1, ActiveRecord $model2, string $modelName, int $id): array
    {
        $model1Attributes = array_keys($model1->getDirtyAttributes());

        $lkPa = $model1->getAttributes($model1Attributes);
        $statPa = $model2->getAttributes($model1Attributes);

        $diff = array_diff_assoc($lkPa, $statPa);

        foreach ($diff as $k => $v) {
            if ($v === null) {
                unset($diff[$k]);
                continue;
            }


            $v1 = $statPa[$k];
            $v2 = $lkPa[$k];

            if (is_string($v1) && is_string($v2)) {

                $v1 = preg_replace('/\s+/', '', $statPa[$k]);
                $v2 = preg_replace('/\s+/', '', $lkPa[$k]);

                if ($v1 === $v2) {
                    unset($diff[$k]);
                    continue;
                }
            }

            echo PHP_EOL . $modelName . ': ' . $id . ': ' . $k . ': ' . $statPa[$k] . ' => ' . $lkPa[$k];
        }

        return $diff;
    }

    public function transform()
    {
        return false;
    }

    public function helper_date(?string $dateStr): ?string
    {
        if ($dateStr == 'Invalid date') {
            return null;
        }

        if (
            !$dateStr
            || (strpos($dateStr, '-') === false && strpos($dateStr, '.') === false)
        ) {
            return null;
        }

        if (strpos($dateStr, '-') !== false) {
            return $dateStr;
        }

        $d = explode('.', $dateStr);

        return $d[2] . '-' . $d[1] . '-' . $d[0];
    }

    protected function makeStatModel()
    {
        echo PHP_EOL . 'ERROR: contragent: ' . $this->coreLkContragent->getContragentId() . ': ' . $this->coreLkContragent->getOrgType();

        return false;
    }
}

