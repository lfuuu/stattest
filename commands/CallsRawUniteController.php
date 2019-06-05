<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class CallsRawUniteController extends Controller
{
    /**
     * Склеивание данных из таблицы call_raw на уровне СУБД
     *
     */
    public function actionMakeUnite()
    {
        $time0 = microtime(true);;
        echo 'Status: ' .  Yii::$app->dbPg
            ->createCommand("select calls_raw_unite.make_calls_raw_unite_new_step01();")
            ->queryScalar() . PHP_EOL;
        echo 'Done in ' . round(microtime(true) - $time0, 2).' sec' . PHP_EOL;

        $time0 = microtime(true);;
        echo 'Status: ' .  Yii::$app->dbPg
            ->createCommand("select calls_raw_unite.make_calls_raw_unite_new_step02();")
            ->queryScalar() . PHP_EOL;
        echo 'Done in ' . round(microtime(true) - $time0, 2).' sec' . PHP_EOL;
    }

    /**
     * Склеивание данных из таблицы call_raw на уровне СУБД за перод (YYYY-mm-dd - YYYY-mm-dd)
     * @param string $beginning
     * @param string $ending
     * @throws \Exception
     */
    public function actionMakeUnitePeriod($beginning = 'yesterday', $ending = 'yesterday')
    {
        $begin = new \DateTime($beginning);
        $end = new \DateTime($ending);

        $end->modify('+1 day');
        echo "####################" . PHP_EOL;
        echo sprintf("Uniting period: %s - %s", $begin->format("l Y-m-d H:i:s"), $end->format("l Y-m-d H:i:s")) . PHP_EOL;
        echo "--------------------" . PHP_EOL;

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);

        $cnt = 1;
        foreach ($period as $dt) {
            $dtEnd = clone $dt;
            $dtEnd->modify('+1 day');

            //echo $cnt++ . '. ' . $dt->format("Y-m-d H:i:s") . ' - ' . $dtEnd->format("Y-m-d H:i:s") . ': ';
            echo sprintf("%2d: Processing %s - %s: ", $cnt++, $dt->format("Y-m-d H:i:s"), $dtEnd->format("Y-m-d H:i:s")) . PHP_EOL;

            $params = sprintf("'%s', '%s'", $dt->format("Y-m-d H:i:s"), $dtEnd->format("Y-m-d H:i:s"));
            // ---
            $time0 = microtime(true);;
            echo "     Uniting: ";
            Yii::$app->dbPg
                ->createCommand("select calls_raw_unite.make_calls_raw_unite_new_step01({$params});")
                ->queryScalar();
            echo 'done in ' . round(microtime(true) - $time0, 2).' sec' . PHP_EOL;

            // ---
            $time0 = microtime(true);;
            echo "    Updating: ";
            Yii::$app->dbPg
                ->createCommand("select calls_raw_unite.make_calls_raw_unite_new_step02({$params});")
                ->queryScalar();
            echo 'done in ' . round(microtime(true) - $time0, 2).' sec' . PHP_EOL;
        }
    }

    /**
     * Накатить миграцию
     */
    public function actionApplyMigration()
    {
        $this->createTable();
        $this->addComment();
        $this->createIndexes();

        $this->createFunction();
    }

    protected function createTable()
    {
        $sqlCreateTable = <<<ESQL
create table if not exists calls_raw_unite.calls_raw_unite
(
  id_orig bigint,
  id_term bigint,
  connect_time_orig timestamp not null,
  connect_time_term timestamp,
  connect_day_key integer,
  market_place_id smallint not null,
  type smallint not null,
  hub_id_orig integer,
  hub_id_term integer,
  cdr_id_orig bigint,
  cdr_id_term bigint,
  trunk_id_orig integer,
  trunk_id_term integer,
  account_id_orig integer,
  account_id_term integer,
  trunk_service_id_orig integer,
  trunk_service_id_term integer,
  number_service_id_orig integer,
  number_service_id_term integer,
  src_number bigint,
  dst_number bigint,
  currency_orig public.currencies,
  currency_term public.currencies,
  billed_time_orig integer,
  billed_time_term integer,
  session_time_orig bigint,
  session_time_term bigint,
  rate_orig double precision,
  rate_term double precision,
  tax_rate_orig double precision,
  tax_rate_term double precision,
  cost_orig double precision,
  cost_term double precision,
  tax_cost_orig double precision,
  tax_cost_term double precision,
  our_orig boolean,
  our_term boolean,
  disconnect_cause_orig smallint,
  disconnect_cause_term smallint,
  has_asterisk boolean,
  has_mvno boolean,
  is_by_peer boolean,
  nnp_operator_id_A integer,
  nnp_operator_id_B integer,
  nnp_region_id_A integer,
  nnp_region_id_B integer,
  nnp_city_id_A integer,
  nnp_city_id_B integer,
  nnp_country_code_A integer,
  nnp_country_code_B integer,
  ndc_type_id_A integer,
  ndc_type_id_B integer,
  nnp_filter_id1_orig integer,
  nnp_filter_id1_term integer,
  nnp_filter_id2_orig integer,
  nnp_filter_id2_term integer,
  mcn_callid uuid
);

alter table calls_raw_unite.calls_raw_unite
  owner to postgres;
ESQL;

        $db = Yii::$app->dbPg;
        $db->createCommand($sqlCreateTable);
    }

    protected function createFunction()
    {
        $sqlCreateFunction01 = <<<ESQL
create function calls_raw_unite.make_calls_raw_unite_new_step01(beginning character varying DEFAULT ((((now() - '1 day'::interval))::date + '00:00:00'::time without time zone))::character varying, ending character varying DEFAULT (((now())::date + '00:00:00'::time without time zone))::character varying) returns void
  language plpgsql
as
$$
BEGIN

--clear
  DELETE FROM
    calls_raw_unite.calls_raw_unite
  WHERE
    connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp;

-- insert data
WITH mvno_trunks as (
  SELECT trunk_id
  FROM (
         SELECT DISTINCT UNNEST(ARRAY_CAT(ARRAY_AGG(orig_trunk_id), ARRAY_AGG(term_trunk_id))) trunk_id
         FROM billing_uu.sim_imsi_partner
       ) mvno
  WHERE trunk_id IS NOT NULL
),
     asterisks as (
       SELECT id trunk_id
       FROM auth.trunk
       WHERE id_pbx is not null
     ),
     mega_trunks as (
       select trunk_id
       from (
              select trunk_id, client_account_id
              from billing.service_trunk
              where activation_dt < now()
                and expire_dt > now()
            ) tr

              join
            (
              select client_account_id
              from billing.service_number
              where activation_dt < now()
                and expire_dt > now()
              group by client_account_id
            ) nm on
              tr.client_account_id = nm.client_account_id

              join auth.trunk tr2 on
         tr.trunk_id = tr2.id
     ),
     origs as (
       SELECT cr.id                                                                     id_orig,
              cr2.id                                                                    id_term,
              cr.connect_time                                                           connect_time_orig,
              cr2.connect_time                                                          connect_time_term,
              to_char(COALESCE(cr.connect_time, cr2.connect_time), 'YYYYMMDD')::integer connect_day_key,
              h.market_place_id                                                         market_place_id,
              (CASE
                 WHEN cr2.id IS NULL AND cr.session_time > 0 THEN 2 --'not_merged'
                 WHEN cr2.id IS NULL THEN 1 --'unfinished'
                 WHEN cr2.id IS NOT NULL AND cr.session_time = 0 AND cr2.session_time > 0 THEN 4 --'broken'
                 WHEN cr.trunk_id IN (select trunk_id from mega_trunks)
                   THEN 30 --'OTT'
                 WHEN cr2.trunk_id IN (select trunk_id from mega_trunks)
                   THEN 30 --'OTT'
                 WHEN cr2.id IS NOT NULL AND cr.number_service_id IS NULL AND cr2.number_service_id IS NULL
                   THEN 20 --'transit'
                 ELSE 10 --'retail'
                END) AS                                                                 type,
              cr.hub_id                                                                 hub_id_orig,
              cr2.hub_id                                                                hub_id_term,
              cr.cdr_id                                                                 cdr_id_orig,
              cr2.cdr_id                                                                cdr_id_term,
              cr.trunk_id                                                               trunk_id_orig,
              cr2.trunk_id                                                              trunk_id_term,
              cr.account_id                                                             account_id_orig,
              cr2.account_id                                                            account_id_term,
              cr.trunk_service_id                                                       trunk_service_id_orig,
              cr2.trunk_service_id                                                      trunk_service_id_term,
              cr.number_service_id                                                      number_service_id_orig,
              cr2.number_service_id                                                     number_service_id_term,
              cr.src_number                                                             src_number,
              cr.dst_number                                                             dst_number,
              c1.currency                                                               currency_orig,
              c2.currency                                                               currency_term,
              cr.billed_time                                                            billed_time_orig,
              cr2.billed_time                                                           billed_time_term,
              cr.session_time                                                           session_time_orig,
              cr2.session_time                                                          session_time_term,
              cr.rate                                                                   rate_orig,
              cr2.rate                                                                  rate_term,
              cr.tax_rate                                                               tax_rate_orig,
              cr2.tax_rate                                                              tax_rate_term,
              cr.cost                                                                   cost_orig,
              cr2.cost                                                                  cost_term,
              cr.tax_cost                                                               tax_cost_orig,
              cr2.tax_cost                                                              tax_cost_term,
              cr.our                                                                    our_orig,
              cr2.our                                                                   our_term,
              cr.disconnect_cause                                                       disconnect_cause_orig,
              cr2.disconnect_cause                                                      disconnect_cause_term,
              (cr.trunk_id IN (select trunk_id from asterisks) OR
               cr2.trunk_id IN (select trunk_id from asterisks))                        has_asterisk,
              (cr.trunk_id IN (select trunk_id from mvno_trunks) OR
               cr2.trunk_id IN (select trunk_id from mvno_trunks))                      has_mvno,
              (cr.peer_id = cr2.id AND cr.id = cr2.peer_id)                             is_by_peer,
              COALESCE(cr.nnp_own_operator_id, cr2.nnp_operator_id)                     nnp_operator_id_A,
              cr.nnp_operator_id                                                        nnp_operator_id_B,
              COALESCE(cr.nnp_own_region_id, cr2.nnp_region_id)                         nnp_region_id_A,
              cr.nnp_region_id                                                          nnp_region_id_B,
              COALESCE(cr.nnp_own_city_id, cr2.nnp_city_id)                             nnp_city_id_A,
              cr.nnp_city_id                                                            nnp_city_id_B,
              COALESCE(cr.nnp_own_country_code, cr2.nnp_country_code)                   nnp_country_code_A,
              cr.nnp_country_code                                                       nnp_country_code_B,
              dst_nr.ndc_type_id                                                        ndc_type_id_A,
              src_nr.ndc_type_id                                                        ndc_type_id_B,
              COALESCE(cr.nnp_filter_orig_id, cr.nnp_filter_orig_ids [ 1])              nnp_filter_id1_orig,
              COALESCE(cr.nnp_filter_term_id, cr.nnp_filter_term_ids [ 1])              nnp_filter_id1_term,
              cr.nnp_filter_orig_ids [ 2]                                               nnp_filter_id2_orig,
              cr.nnp_filter_term_ids [ 2]                                               nnp_filter_id2_term,
              cr.mcn_callid
       FROM "calls_raw"."calls_raw" "cr"
              INNER JOIN "auth"."hub" "h" ON
         h.id = cr.hub_id

              LEFT JOIN "calls_raw"."calls_raw" "cr2" ON
           cr2.mcn_callid = cr.mcn_callid
           AND cr2.orig IS FALSE
           AND (cr2.account_id IS NOT NULL)
           AND (cr2.session_time > 0)
           AND (cr2.connect_time >= beginning :: timestamp AND
                cr2.connect_time < ending :: timestamp)

              LEFT JOIN nnp.number_range src_nr ON
           src_nr.id = COALESCE(cr.nnp_own_number_range_id, cr2.nnp_number_range_id)

              LEFT JOIN nnp.number_range dst_nr ON
         dst_nr.id = cr.nnp_number_range_id

              LEFT JOIN "auth"."hub" "h2" ON
           h2.id = cr2.hub_id
           AND h2.market_place_id = h.market_place_id
           
              LEFT JOIN "billing"."clients" "c1" ON 
           c1.id = cr.account_id
              LEFT JOIN "billing"."clients" "c2" ON 
           c2.id = cr2.account_id

       WHERE ("cr"."mcn_callid" IS NOT NULL)
         AND ("cr"."account_id" IS NOT NULL)
         AND ("cr"."orig" IS TRUE)
         AND (cr.connect_time >= beginning :: timestamp AND
              cr.connect_time < ending :: timestamp)
         AND ((cr2.id IS NOT NULL) AND (h2.id IS NULL)) IS FALSE
     ),
     terms as (
       SELECT NULL::bigint                                                   id_orig,
              cr2.id                                                         id_term,
              cr2.connect_time                                               connect_time_orig,
              cr2.connect_time                                               connect_time_term,
              to_char(cr2.connect_time, 'YYYYMMDD')::integer                 connect_day_key,
              h2.market_place_id                                             market_place_id,
              3 AS                                                           type,
              NULL::integer                                                  hub_id_orig,
              cr2.hub_id                                                     hub_id_term,
              NULL::integer                                                  cdr_id_orig,
              cr2.cdr_id                                                     cdr_id_term,
              NULL::integer                                                  trunk_id_orig,
              cr2.trunk_id                                                   trunk_id_term,
              NULL::integer                                                  account_id_orig,
              cr2.account_id                                                 account_id_term,
              NULL::integer                                                  trunk_service_id_orig,
              cr2.trunk_service_id                                           trunk_service_id_term,
              NULL::integer                                                  number_service_id_orig,
              cr2.number_service_id                                          number_service_id_term,
              cr2.src_number                                                 src_number,
              cr2.dst_number                                                 dst_number,
              NULL::public.currencies                                        currency_orig,
              c2.currency                                                    currency_term,
              NULL::integer                                                  billed_time_orig,
              cr2.billed_time                                                billed_time_term,
              NULL::bigint                                                   session_time_orig,
              cr2.session_time                                               session_time_term,
              NULL::double precision                                         rate_orig,
              cr2.rate                                                       rate_term,
              NULL::double precision                                         tax_rate_orig,
              cr2.tax_rate                                                   tax_rate_term,
              NULL::double precision                                         cost_orig,
              cr2.cost                                                       cost_term,
              NULL::double precision                                         tax_cost_orig,
              cr2.tax_cost                                                   tax_cost_term,
              NULL::boolean                                                  our_orig,
              cr2.our                                                        our_term,
              NULL::smallint                                                 disconnect_cause_orig,
              cr2.disconnect_cause                                           disconnect_cause_term,
              cr2.trunk_id IN (select trunk_id from asterisks)               has_asterisk,
              cr2.trunk_id IN (select trunk_id from mvno_trunks)             has_mvno,
              NULL::boolean                                                  is_by_peer,
              cr2.nnp_operator_id                                            nnp_operator_id_A,
              cr2.nnp_own_operator_id                                        nnp_operator_id_B,
              cr2.nnp_region_id                                              nnp_region_id_A,
              cr2.nnp_own_region_id                                          nnp_region_id_B,
              cr2.nnp_city_id                                                nnp_city_id_A,
              cr2.nnp_own_city_id                                            nnp_city_id_B,
              cr2.nnp_country_code                                           nnp_country_code_A,
              cr2.nnp_own_country_code                                       nnp_country_code_B,
              dst_nr.ndc_type_id                                             ndc_type_id_A,
              src_nr.ndc_type_id                                             ndc_type_id_B,
              COALESCE(cr2.nnp_filter_orig_id, cr2.nnp_filter_orig_ids [ 1]) nnp_filter_id1_orig,
              COALESCE(cr2.nnp_filter_term_id, cr2.nnp_filter_term_ids [ 1]) nnp_filter_id1_term,
              cr2.nnp_filter_orig_ids [ 2]                                   nnp_filter_id2_orig,
              cr2.nnp_filter_term_ids [ 2]                                   nnp_filter_id2_term,
              cr2.mcn_callid
       FROM "calls_raw"."calls_raw" "cr2"

              INNER JOIN "auth"."hub" "h2" ON
         h2.id = cr2.hub_id

              LEFT JOIN "calls_raw"."calls_raw" "cr" ON
           cr.mcn_callid = cr2.mcn_callid
           AND cr.orig IS TRUE
           AND (cr.account_id IS NOT NULL)
           AND (cr.connect_time >= beginning :: timestamp AND
                cr.connect_time < ending :: timestamp)

              LEFT JOIN "auth"."hub" "h" ON
           h.market_place_id = h2.market_place_id
           AND h.id = cr.hub_id

              LEFT JOIN nnp.number_range src_nr ON
         src_nr.id = cr2.nnp_number_range_id

              LEFT JOIN nnp.number_range dst_nr ON
         dst_nr.id = cr2.nnp_own_number_range_id

              LEFT JOIN "billing"."clients" "c2" ON 
         c2.id = cr2.account_id

       WHERE ("cr2"."mcn_callid" IS NOT NULL)
         AND ("cr2"."account_id" IS NOT NULL)
         AND (cr2.session_time > 0)
         AND ("cr2"."orig" IS FALSE)
         AND (cr2.connect_time >= beginning :: timestamp AND
              cr2.connect_time < ending :: timestamp)
         AND (cr.id IS NULL OR h2.id IS NULL)
     )
INSERT
INTO calls_raw_unite.calls_raw_unite
SELECT
  *
FROM (
  SELECT
    *
  FROM origs
  UNION ALL
  SELECT
    *
  FROM terms
) res
ORDER BY connect_time_orig;

END;
$$;


alter function calls_raw_unite.make_calls_raw_unite_new_step01(varchar, varchar) owner to postgres;
ESQL;

        $db = Yii::$app->dbPg;
        $db->createCommand($sqlCreateFunction01);


        $sqlCreateFunction02 = <<<ESQL
create function calls_raw_unite.make_calls_raw_unite_new_step02(beginning character varying DEFAULT ((((now() - '1 day'::interval))::date + '00:00:00'::time without time zone))::character varying, ending character varying DEFAULT (((now())::date + '00:00:00'::time without time zone))::character varying) returns void
  language plpgsql
as
$$
DECLARE
  market_id INTEGER := 1;
BEGIN

-- 3. Update OTT
FOR market_id IN 1..2 LOOP
WITH callids as (
  SELECT mcn_callid
  FROM calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type = 30
    AND market_place_id = market_id
)
UPDATE
  calls_raw_unite.calls_raw_unite
SET
  type = 30
WHERE
  (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type IN (10, 20)
  AND market_place_id = market_id
  AND mcn_callid IN (SELECT mcn_callid FROM callids);
END LOOP;


-- 4. Update mvno
FOR market_id IN 1..2 LOOP
WITH callids as (
  SELECT mcn_callid
  FROM calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type IN (10, 20)
    AND has_mvno IS TRUE
    AND market_place_id = market_id
)
UPDATE
  calls_raw_unite.calls_raw_unite
SET
  type = 12
WHERE
  (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type IN (10, 20)
  AND market_place_id = market_id
  AND mcn_callid IN (SELECT mcn_callid FROM callids);
END LOOP;


-- 5. Update asterisks
FOR market_id IN 1..2 LOOP
WITH callids as (
  SELECT mcn_callid
  FROM calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type IN (10, 20)
    AND has_asterisk IS TRUE
    AND market_place_id = market_id
)
UPDATE
  calls_raw_unite.calls_raw_unite
SET
  type = 11
WHERE
  (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type IN (10, 20)
  AND market_place_id = market_id
  AND mcn_callid IN (SELECT mcn_callid FROM callids);
END LOOP;


-- 6. Fix asterisks by_peer
UPDATE
  calls_raw_unite.calls_raw_unite
SET type = 5
WHERE (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type IN (10, 11, 12)
  AND is_by_peer IS FALSE
  AND has_mvno IS FALSE
  AND id_orig IN (
  SELECT id_orig
  FROM calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type IN (10, 11, 12)
    AND is_by_peer IS TRUE
);

UPDATE
  calls_raw_unite.calls_raw_unite
SET type = 5
WHERE (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type IN (10, 11, 12)
  AND is_by_peer IS FALSE
  AND has_mvno IS FALSE
  AND id_term IN (
  SELECT id_term
  FROM calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type IN (10, 11, 12)
    AND is_by_peer IS TRUE
);


-- 7. Fix asterisk by mismatch
FOR market_id IN 1..2 LOOP
UPDATE
  calls_raw_unite.calls_raw_unite
SET type = 5
WHERE (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type IN (10, 11, 12)
  AND market_place_id = market_id
  AND is_by_peer IS FALSE
  AND connect_time_orig != connect_time_term
  AND mcn_callid IN (
  SELECT mcn_callid
  FROM (
         SELECT mcn_callid,
                count(*)
         FROM calls_raw_unite.calls_raw_unite
         WHERE
           (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
           AND type IN (10, 11, 12)
           AND market_place_id = market_id
         GROUP BY mcn_callid
         HAVING count(*) > 1
       ) ast_stat
);
END LOOP;


-- 8. Fix mvno with origs
UPDATE
  calls_raw_unite.calls_raw_unite
SET
  type = 13,
  cost_term = 0,
  tax_cost_term = 0,
  rate_term = 0,
  tax_rate_term = 0,
  --      id_term = null,
  connect_time_term = null,
  hub_id_term = null,
  cdr_id_term = null,
  trunk_id_term = null,
  account_id_term = null,
  trunk_service_id_term = null,
  number_service_id_term = null,
  billed_time_term = null,
  session_time_term = null,
  dst_number = null,
  currency_term = null,
  our_term = null,
  disconnect_cause_term = null,
  nnp_operator_id_b = null,
  nnp_region_id_b = null,
  nnp_city_id_b = null,
  nnp_country_code_b = null,
  ndc_type_id_b = null,
  nnp_filter_id1_term = null,
  nnp_filter_id2_term = null
WHERE
  (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type = 12
  AND has_mvno IS TRUE
  AND id_term IN (
  select
    id_term
  from calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type = 12
  GROUP BY id_term
  HAVING count(*) > 1
     AND SUM(CASE WHEN has_mvno IS TRUE THEN 1 ELSE 0 END) = 1
);


-- 9. Fix mvno with terms
UPDATE
  calls_raw_unite.calls_raw_unite
SET
  type = 13,
  cost_orig = 0,
  tax_cost_orig = 0,
  rate_orig = 0,
  tax_rate_orig = 0,
  --      id_orig = null,
  --      connect_time_orig = null,
  hub_id_orig = null,
  cdr_id_orig = null,
  trunk_id_orig = null,
  account_id_orig = null,
  trunk_service_id_orig = null,
  number_service_id_orig = null,
  billed_time_orig = null,
  session_time_orig = null,
  dst_number = null,
  currency_orig = null,
  our_orig = null,
  disconnect_cause_orig = null,
  nnp_operator_id_a = null,
  nnp_region_id_a = null,
  nnp_city_id_a = null,
  nnp_country_code_a = null,
  ndc_type_id_a = null,
  nnp_filter_id1_orig = null,
  nnp_filter_id2_orig = null
WHERE
  (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type = 12
  AND has_mvno IS TRUE
  AND id_orig IN (
  select
    id_orig
  from calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type = 12
  GROUP BY id_orig
  HAVING count(*) > 1
     AND SUM(CASE WHEN has_mvno IS TRUE THEN 1 ELSE 0 END) = 1
);


-- 10. Fix asterisks by_peer again
UPDATE
  calls_raw_unite.calls_raw_unite
SET type = 5
WHERE (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type IN (10, 11, 12)
  AND is_by_peer IS FALSE
  AND id_orig IN (
  SELECT id_orig
  FROM calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type IN (10, 11, 12)
    AND is_by_peer IS TRUE
);

UPDATE
  calls_raw_unite.calls_raw_unite
SET type = 5
WHERE (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
  AND type IN (10, 11, 12)
  AND is_by_peer IS FALSE
  AND id_term IN (
  SELECT id_term
  FROM calls_raw_unite.calls_raw_unite
  WHERE
    (connect_time_orig >= beginning :: timestamp AND connect_time_orig < ending :: timestamp)
    AND type IN (10, 11, 12)
    AND is_by_peer IS TRUE
);

END;
$$;

alter function calls_raw_unite.make_calls_raw_unite_new_step02(varchar, varchar) owner to postgres;
ESQL;

        $db = Yii::$app->dbPg;
        $db->createCommand($sqlCreateFunction02);
    }

    protected function addComment()
    {
        $sqlAddComment = <<<ESQL
comment on column calls_raw_unite.calls_raw_unite.id_orig is 'Оригинация. Ссылка на соответствующую запись calls_raw.';
comment on column calls_raw_unite.calls_raw_unite.id_term is 'Терминация. Ссылка на соответствующую запись calls_raw.';

comment on column calls_raw_unite.calls_raw_unite.connect_time_orig is 'Оригинация. Время начала разговора (UTC).';
comment on column calls_raw_unite.calls_raw_unite.connect_time_term is 'Терминация. Время начала разговора (UTC).';

comment on column calls_raw_unite.calls_raw_unite.connect_day_key is 'Ключ для дня начала разговора (UTC).';

comment on column calls_raw_unite.calls_raw_unite.market_place_id is 'Биржа, к которой принадлежат плечи звонка.';

comment on column calls_raw_unite.calls_raw_unite.type is 'Тип звонка: unfinished (1)/not_merged (2)/not_merged_term (3)/broken (4)/wrong (5)/retail (10)/asterisk (11)/MVNO (12)/MVNO Cost (13)/transit (20)/OTT (30).';

comment on column calls_raw_unite.calls_raw_unite.hub_id_orig is 'Оригинация. Хаб плеча.auth.hub.id';
comment on column calls_raw_unite.calls_raw_unite.hub_id_term is 'Терминация. Хаб плеча.auth.hub.id';

comment on column calls_raw_unite.calls_raw_unite.cdr_id_orig is 'Оригинация. Ссылка на соответствующую запись calls_cdr';
comment on column calls_raw_unite.calls_raw_unite.cdr_id_term is 'Терминация. Ссылка на соответствующую запись calls_cdr';

comment on column calls_raw_unite.calls_raw_unite.trunk_id_orig is 'Оригинация. Физический транк плеча.auth.trunk';
comment on column calls_raw_unite.calls_raw_unite.trunk_id_term is 'Терминация. Физический транк плеча.auth.trunk';

comment on column calls_raw_unite.calls_raw_unite.account_id_orig is 'Оригинация. Лицевой счет плеча.billing.clients.id';
comment on column calls_raw_unite.calls_raw_unite.account_id_term is 'Терминация. Лицевой счет плеча.billing.clients.id';

comment on column calls_raw_unite.calls_raw_unite.trunk_service_id_orig is 'Оригинация. Услуга "транк" плеча.billing.service_trunk.id. Если есть.';
comment on column calls_raw_unite.calls_raw_unite.trunk_service_id_term is 'Терминация. Услуга "транк" плеча.billing.service_trunk.id. Если есть.';

comment on column calls_raw_unite.calls_raw_unite.number_service_id_orig is 'Оригинация. Услуга "номер" плеча. billing.service_number.id. Если есть.';
comment on column calls_raw_unite.calls_raw_unite.number_service_id_term is 'Терминация. Услуга "номер" плеча. billing.service_number.id. Если есть.';

comment on column calls_raw_unite.calls_raw_unite.src_number is 'Номер А звонка.';
comment on column calls_raw_unite.calls_raw_unite.dst_number is 'Номер Б звонка.';

comment on column calls_raw_unite.calls_raw_unite.currency_orig is 'Оригинация. Валюта лицевого счета плеча.billing.clients.currency';
comment on column calls_raw_unite.calls_raw_unite.currency_term is 'Терминация. Валюта лицевого счета плеча.billing.clients.currency';

comment on column calls_raw_unite.calls_raw_unite.billed_time_orig is 'Оригинация. Время по которому происходит тарификация. Применяются настройки округления и бесплатных секунд.';
comment on column calls_raw_unite.calls_raw_unite.billed_time_term is 'Терминация. Время по которому происходит тарификация. Применяются настройки округления и бесплатных секунд.';

comment on column calls_raw_unite.calls_raw_unite.session_time_orig is 'Оригинация. Продолжительность звонка.';
comment on column calls_raw_unite.calls_raw_unite.session_time_term is 'Терминация. Продолжительность звонка.';

comment on column calls_raw_unite.calls_raw_unite.rate_orig is 'Оригинация. Цена минуты звонка.';
comment on column calls_raw_unite.calls_raw_unite.rate_term is 'Терминация. Цена минуты звонка.';

comment on column calls_raw_unite.calls_raw_unite.tax_rate_orig is 'Оригинация. НДС для цены минуты звонка.';
comment on column calls_raw_unite.calls_raw_unite.tax_rate_term is 'Терминация. НДС для цены минуты звонка.';

comment on column calls_raw_unite.calls_raw_unite.cost_orig is 'Оригинация. Общая стоимость звона.На нее меняется баланс.Если есть минуты в пакетах - они уходят из стоимости.';
comment on column calls_raw_unite.calls_raw_unite.cost_term is 'Терминация. Общая стоимость звона.На нее меняется баланс.Если есть минуты в пакетах - они уходят из стоимости.';

comment on column calls_raw_unite.calls_raw_unite.tax_cost_orig is 'Оригинация. НДС для общей стоимости звонка.';
comment on column calls_raw_unite.calls_raw_unite.tax_cost_term is 'Терминация. НДС для общей стоимости звонка.';

comment on column calls_raw_unite.calls_raw_unite.our_orig is 'Оригинация. Транк плеча наш. флаг берется из auth.trunk.our_trunk';
comment on column calls_raw_unite.calls_raw_unite.our_term is 'Терминация. Транк плеча наш. флаг берется из auth.trunk.our_trunk';

comment on column calls_raw_unite.calls_raw_unite.disconnect_cause_orig is 'Оригинация. Причина завершения вызова. billing.disconnect_cause.cause_id';
comment on column calls_raw_unite.calls_raw_unite.disconnect_cause_term is 'Терминация. Причина завершения вызова. billing.disconnect_cause.cause_id';

comment on column calls_raw_unite.calls_raw_unite.has_asterisk is 'Один из транков плеч - asterisk';

comment on column calls_raw_unite.calls_raw_unite.has_mvno is 'Один из транков плеч - радиосеть';

comment on column calls_raw_unite.calls_raw_unite.is_by_peer is 'Связаны по peer_id';

comment on column calls_raw_unite.calls_raw_unite.nnp_operator_id_A is 'Оригинация. Расчитанный оператор для плеча.nnp.operator.id';
comment on column calls_raw_unite.calls_raw_unite.nnp_operator_id_B is 'Терминация. Расчитанный оператор для плеча.nnp.operator.id';

comment on column calls_raw_unite.calls_raw_unite.nnp_region_id_A is 'Оригинация. Расчитанный регион для плеча.nnp.region.id';
comment on column calls_raw_unite.calls_raw_unite.nnp_region_id_B is 'Терминация. Расчитанный регион для плеча.nnp.region.id';

comment on column calls_raw_unite.calls_raw_unite.nnp_city_id_A is 'Оригинация. Рассчитанный город для плеча. nnp.city.id';
comment on column calls_raw_unite.calls_raw_unite.nnp_city_id_B is 'Терминация. Рассчитанный город для плеча. nnp.city.id';

comment on column calls_raw_unite.calls_raw_unite.nnp_country_code_A is 'Оригинация. Расчитанная страна для плеча.nnp.country.id';
comment on column calls_raw_unite.calls_raw_unite.nnp_country_code_B is 'Терминация. Расчитанная страна для плеча.nnp.country.id';

comment on column calls_raw_unite.calls_raw_unite.ndc_type_id_A is 'Оригинация. Тип номера. nnp.ndc_type.id';
comment on column calls_raw_unite.calls_raw_unite.ndc_type_id_B is 'Терминация. Тип номера. nnp.ndc_type.id';

comment on column calls_raw_unite.calls_raw_unite.nnp_filter_id1_orig is 'Группа 1 оригинационного плеча.';
comment on column calls_raw_unite.calls_raw_unite.nnp_filter_id1_term is 'Группа 1 терминационного плеча.';

comment on column calls_raw_unite.calls_raw_unite.nnp_filter_id2_orig is 'Группа 2 оригинационного плеча.';
comment on column calls_raw_unite.calls_raw_unite.nnp_filter_id2_term is 'Группа 2 терминационного плеча.';

comment on column calls_raw_unite.calls_raw_unite.mcn_callid is 'Уникальный идентификатор звонка';
ESQL;

        $db = Yii::$app->dbPg;
        $db->createCommand($sqlAddComment);
    }

    protected function createIndexes()
    {
        $sqlAddIndexes = <<<ESQL
create index calls_raw_unite_id_orig_idx
  on calls_raw_unite.calls_raw_unite (id_orig);

create index calls_raw_unite_id_term_idx
  on calls_raw_unite.calls_raw_unite (id_term);

create index calls_raw_unite_connect_time_orig_idx
  on calls_raw_unite.calls_raw_unite (connect_time_orig);

create index calls_raw_unite_type_market_place_id_idx
  on calls_raw_unite.calls_raw_unite (type, market_place_id);

create index calls_raw_unite_connect_day_key_type_market_place_id_idx
  on calls_raw_unite.calls_raw_unite.calls_raw_unite (connect_day_key, type, market_place_id);

create index calls_raw_unite_mcn_callid_idx
  on calls_raw_unite.calls_raw_unite (mcn_callid);
ESQL;
        $db = Yii::$app->dbPg;
        $db->createCommand($sqlAddIndexes);
    }
}
