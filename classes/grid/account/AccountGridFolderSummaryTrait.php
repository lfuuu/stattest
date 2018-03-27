<?php

namespace app\classes\grid\account;

use yii\db\Query;

trait AccountGridFolderSummaryTrait
{
    /**
     * Возврат ассоциативного массива с суммами по колонкам abon, over, total
     * из основной выборки. В противном случае будет возвращен пустой массив
     *
     * @return array
     */
    public function getSummary()
    {
        $result = (new Query)
            ->select([
                'SUM(abon) AS abon',
                'SUM(`over`) AS `over`',
                'SUM(total) AS total',
            ])
            ->from([
                'summary' => $this->spawnDataProvider()->query
            ])
            ->one();

        return $result ?: [];
    }
}