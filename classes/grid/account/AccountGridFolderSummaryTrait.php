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
        /** @var Query $query */
        $query = clone $this->spawnDataProvider()->query;
        $result = $query
            ->select($this->getQuerySummarySelect())
            ->orderBy(null)
            ->groupBy(null)
            ->one();

        return $result ?: [];
    }
}