<?php

namespace app\classes;

trait CdrReportTotalCountTrait
{
    /**
     * Получение примерного количества записей
     * в результате запроса.
     * Использует хранимую процедуру
     * https://github.com/welltime/billing_voip/blob/master/install/sql/20-lite_count.sql
     * Метод актуален для тяжелых запросов.
     *
     * @param null $db
     * @return int
     */
    public function prepareTotalCount($db)
    {
        list($sql, $params) = $this->createSQL();
        $sql = str_replace(
            array_map(
                function ($el) {
                    return strpos($el, ':') === false ? ":$el" : $el;
                },
                array_keys($params)
            ),
            array_map(
                function ($el) {
                    return "'$el'";
                },
                array_values($params)
            ),
            $sql
        );
        $sql = str_replace('"', '', $sql);
        $sql = str_replace("'", "''", $sql);
        $sql = "SELECT * FROM calls_cdr.lite_count('$sql')";

        return (int)$db->createCommand($sql)->queryScalar();
    }
}
