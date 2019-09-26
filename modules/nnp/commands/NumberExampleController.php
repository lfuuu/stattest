<?php

namespace app\modules\nnp\commands;

use Yii;
use yii\console\Controller;

class NumberExampleController extends Controller
{
    /**
     * Полный пересчёт таблицы nnp.number_example на уровне СУБД
     *
     */
    public function actionRenew()
    {
        $time0 = microtime(true);;
        echo 'Status: ' .  Yii::$app->dbPg
            ->createCommand("select nnp.number_example_renew();")
            ->queryScalar() . PHP_EOL;
        echo 'Done in ' . round(microtime(true) - $time0, 2).' sec' . PHP_EOL;
    }

    /**
     * Накатить миграцию
     */
    public function actionApplyMigration()
    {
        $this->createTable();
        $this->createTableHistory();

        $this->createFunctions();
    }

    protected function createTable()
    {
        $sqlCreateTable = <<<ESQL
DROP SEQUENCE IF EXISTS nnp.number_example_id_seq;

CREATE SEQUENCE nnp.number_example_id_seq;
ALTER SEQUENCE nnp.number_example_id_seq OWNER to postgres;
        
CREATE TABLE nnp.number_example
(
  id            integer default nextval('nnp.number_example_id_seq'::regclass) not null
    constraint number_example_pkey
      primary key,
  country_code  integer                                                         not null,
  prefix        integer,
  ndc           integer,
  ndc_type_id   integer,

  region_id     integer,
  region_name   varchar(255),

  city_id       integer,
  city_name     varchar(255),

  operator_id   integer,
  operator_name varchar(255),

  full_number   bigint                                                          not null,

  ranges        integer,
  numbers       bigint
);

ALTER TABLE nnp.number_example
  OWNER to postgres;
  
-- comments
comment on column nnp.number_example.country_code is 'Код страны.';
comment on column nnp.number_example.prefix is 'Префикс страны.';
comment on column nnp.number_example.ndc is 'NDC.';
comment on column nnp.number_example.ndc_type_id is 'Тип NDC.';

comment on column nnp.number_example.region_id is 'Регион nnp.region.id';
comment on column nnp.number_example.region_name is 'Название регион nnp.region.name';

comment on column nnp.number_example.city_id is 'Город nnp.city_id.id';
comment on column nnp.number_example.city_name is 'Название города nnp.city_id.name';

comment on column nnp.number_example.operator_id is 'Оператор nnp.operator.id';
comment on column nnp.number_example.operator_name is 'Название оператора nnp.operator.name';

comment on column nnp.number_example.full_number is 'Пример полного номерa.';

comment on column nnp.number_example.ranges is 'Кол-во диапазонов.';
comment on column nnp.number_example.numbers is 'Кол-во номеров.';
ESQL;

        $db = Yii::$app->dbPg;
        $db->createCommand($sqlCreateTable);
    }

    protected function createTableHistory()
    {
        $sqlCreateTable = <<<ESQL
DROP SEQUENCE IF EXISTS nnp.number_example_log_id_seq;

CREATE SEQUENCE nnp.number_example_log_id_seq;
ALTER SEQUENCE nnp.number_example_log_id_seq OWNER to postgres;
        
CREATE TABLE nnp.number_example_log
(
  id          integer default nextval('nnp.number_example_log_id_seq'::regclass) not null
    constraint number_example_log_pkey primary key,
  event       varchar(10)                                                        not null,
  inserted_at timestamp                                                          not null
);

ALTER TABLE nnp.number_example_log
  OWNER to postgres;
ESQL;

        $db = Yii::$app->dbPg;
        $db->createCommand($sqlCreateTable);
    }

    protected function createFunctions()
    {
        $sqlCreateFunction = <<<ESQL
create function nnp.number_example_renew() returns void
  language plpgsql
as
$$
BEGIN

  -- clear table
  DELETE FROM nnp.number_example WHERE TRUE;

  -- log delete
  INSERT INTO nnp.number_example_log(event, inserted_at) VALUES('delete', NOW() AT TIME ZONE 'utc');

  -- update sequence
  ALTER SEQUENCE nnp.number_example_id_seq RESTART WITH 1;

  -- log reset sequence
  INSERT INTO nnp.number_example_log(event, inserted_at) VALUES('reset', NOW() AT TIME ZONE 'utc');

  -- insert data
  WITH precount as (
    SELECT is_active,
           country_code,
           ndc,
           ndc_type_id,
           region_id,
           city_id,
           operator_id,
           GREATEST(number_to - number_from + 1, 0) as numbers,
           first_value(full_number_from)
                       OVER (PARTITION BY country_code, ndc, ndc_type_id, region_id, city_id, operator_id) as full_number_from
    FROM nnp.number_range
    WHERE is_active IS TRUE
  ),
       precont_grouped as (
         SELECT country_code,
                ndc,
                ndc_type_id,
                region_id,
                city_id,
                operator_id,
                min(full_number_from) as full_number,
                count(*)              as ranges,
                sum(numbers)          as numbers
         from precount
         GROUP BY country_code, ndc, ndc_type_id, region_id, city_id, operator_id
         ORDER BY region_id, city_id, operator_id
       )
  INSERT
  INTO nnp.number_example
  (country_code, prefix, ndc, ndc_type_id, region_id, region_name, city_id, city_name, operator_id, operator_name, full_number, ranges, numbers)
    (SELECT precont_grouped.country_code,
            cntr.prefix,
            ndc,
            ndc_type_id,
            precont_grouped.region_id,
            reg.name as reg_name,
            city_id,
            city.name as city_name,
            operator_id,
            op.name as operator_name,
            full_number,
            precont_grouped.ranges,
            precont_grouped.numbers
     FROM precont_grouped
            LEFT JOIN nnp.country cntr ON cntr.code = precont_grouped.country_code
            LEFT JOIN nnp.region reg ON reg.id = precont_grouped.region_id
            LEFT JOIN nnp.city city ON city.id = precont_grouped.city_id
            LEFT JOIN nnp.operator op ON op.id = precont_grouped.operator_id);

  -- log insert
  INSERT INTO nnp.number_example_log(event, inserted_at) VALUES('insert', NOW() AT TIME ZONE 'utc');

END;
$$;

alter function nnp.number_example_renew() owner to postgres;
ESQL;

        $db = Yii::$app->dbPg;
        $db->createCommand($sqlCreateFunction);
    }
}
