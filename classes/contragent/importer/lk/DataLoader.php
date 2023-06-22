<?php

namespace app\classes\contragent\importer\lk;


use app\models\ClientContragent;

class DataLoader
{
    private static ?BufferedLoader $bufferedLoader;

    public static function getObjectsForSync($contragentId = null)
    {
        return (new BufferedLoader(self::_getObjectsForSync($contragentId)))
            ->setPostLoader(function ($buffer) {
                $ids = array_map(function ($value) {
                    return $value->getContragentId();
                }, $buffer);

                $statContragents = self::loadStatContragents($ids);

                /** @var CoreLkContragent $coreContragent */
                foreach ($buffer as $coreContragent) {
                    if (!isset($statContragents[$coreContragent->getContragentId()])) {
                        continue;
                    }

                    $coreContragent->setStatContragent($statContragents[$coreContragent->getContragentId()]);
                }
            })
            ->getGenerator();
    }

    private static function loadStatContragents($ids)
    {
        return ClientContragent::find()->where(['id' => $ids])->with('personModel')->indexBy('id')->all();
    }

    private static function _getObjectsForSync($contragentId = null): iterable
    {
        $sqlWhere = "is_lk_first ";
        if ($contragentId) {
            $sqlWhere .= " and contragent_id = " . $contragentId;
        }

        foreach (\Yii::$app
                     ->db
                     ->createCommand("select * from import_dict.core_contragent where {$sqlWhere} order by contragent_id /* limit 100 offset 100 */")
                     ->query() as $dbRow
        ) {
            yield (new CoreLkContragent($dbRow));
        }
    }
}
