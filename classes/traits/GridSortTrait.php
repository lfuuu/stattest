<?php

namespace app\classes\traits;

trait GridSortTrait
{

    public static $sortableAttribute = 'order';

    /**
     * @param int $elementId
     * @param int $nextElementId
     * @return bool
     */
    public function gridSort($elementId, $nextElementId)
    {
        if (is_null(self::$primaryField)) {
            throw new \LogicException('Static property "primaryField" not found');
        }

        $transaction = self::getDb()->beginTransaction();

        try {
            $movedElement = self::findOne([self::$primaryField => $elementId]);

            if ($nextElementId) {
                $nextElement = self::findOne([self::$primaryField => $nextElementId]);

                $movedElement->{self::$sortableAttribute} = $nextElement->{self::$sortableAttribute} ?: 1;
                $movedElement->save();

                $query = self::find()->where([
                    'AND',
                    ['>=', self::$sortableAttribute, $nextElement->{self::$sortableAttribute}],
                    ['!=', self::$primaryField, $movedElement->{self::$primaryField}],
                ]);


                $counter = 0;

                foreach($query->each() as $model) {
                    $model->{self::$sortableAttribute} = $movedElement->{self::$sortableAttribute} + ++$counter;
                    $model->save();
                }
/*
                self::updateAllCounters([
                    self::$sortableAttribute => 1
                ], [
                    'AND',
                    ['!=', self::$sortableAttribute, 0],
                    ['>=', self::$sortableAttribute, $nextElement->{self::$sortableAttribute}],
                    ['!=', self::$primaryField, $movedElement->{self::$primaryField}],
                ]);
*/
            } else {
                $maxSequence = self::find()->max('`' . self::$sortableAttribute . '`');
                $movedElement->{self::$sortableAttribute} = (int)$maxSequence + 1;
                $movedElement->save();
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $e->getMessage();
        }

        return true;
    }

}