CREATE FUNCTION nnp2.range_short_renew() RETURNS void
    LANGUAGE plpgsql
    AS $$
 BEGIN

     -- log start
     INSERT INTO nnp2.range_short_log(event, inserted_at) VALUES('--- start', timeofday()::timestamp);

     -- drop indexes
     DROP INDEX nnp2."idx-range_short-country_code";
     DROP INDEX nnp2."idx-range_short-ndc_type_id";
     DROP INDEX nnp2."idx-range_short-region_id";
     DROP INDEX nnp2."idx-range_short-city_id";
     DROP INDEX nnp2."idx-range_short-operator_id";
     DROP INDEX nnp2."idx-range_short-number_from";
     DROP INDEX nnp2."idx-range_short-number_to";
     DROP INDEX nnp2."idx-range_short-full_number_from";
     DROP INDEX nnp2."idx-range_short-full_number_to";

     -- log indexes dropped
     INSERT INTO nnp2.range_short_log(event, inserted_at) VALUES('indexes dropped', timeofday()::timestamp);

     -- drop constraints
     ALTER TABLE nnp2.range_short DROP CONSTRAINT range_short_ndc_type_id_fkey;
     ALTER TABLE nnp2.range_short DROP CONSTRAINT range_short_reg_id_fkey;
     ALTER TABLE nnp2.range_short DROP CONSTRAINT range_short_city_id_fkey;
     ALTER TABLE nnp2.range_short DROP CONSTRAINT range_short_operator_id_fkey;

     -- log constraints dropped
     INSERT INTO nnp2.range_short_log(event, inserted_at) VALUES('constraints dropped', timeofday()::timestamp);

     -- clear table
     DELETE FROM nnp2.range_short WHERE TRUE;

     -- log delete
     INSERT INTO nnp2.range_short_log(event, inserted_at) VALUES('deleted', timeofday()::timestamp);

     -- update sequence
     ALTER SEQUENCE nnp2.range_short_id_seq RESTART WITH 1;

     -- log reset sequence
     INSERT INTO nnp2.range_short_log(event, inserted_at) VALUES('reset', timeofday()::timestamp);

     -- insert data
     INSERT
     INTO nnp2.range_short
     (
         country_code, ndc, ndc_type_id, region_id, city_id, operator_id,
         number_from, number_to,
         full_number_from, full_number_to,
         allocation_date_start,
         insert_time, insert_user_id, update_time, update_user_id
     )
     SELECT
         geo.country_code,
         geo.ndc,
         nr.ndc_type_id,
         geo.region_id,
         geo.city_id,
         nr.operator_id,

         nr.number_from,
         nr.number_to,

         nr.full_number_from,
         nr.full_number_to,

         nr.allocation_date_start,

         nr.insert_time,
         nr.insert_user_id,
         nr.update_time,
         nr.update_user_id
     FROM
         nnp2.number_range nr
             LEFT JOIN nnp2.geo_place geo ON geo.id = nr.geo_place_id;


     -- log insert
     INSERT INTO nnp2.range_short_log(event, inserted_at) VALUES('inserted', timeofday()::timestamp);

     -- add constraints
     ALTER TABLE nnp2.range_short
         ADD CONSTRAINT range_short_ndc_type_id_fkey
             FOREIGN KEY (ndc_type_id)
                 REFERENCES nnp2.ndc_type ON UPDATE CASCADE ON DELETE SET NULL;
     ALTER TABLE nnp2.range_short
         ADD CONSTRAINT range_short_reg_id_fkey
             FOREIGN KEY (region_id)
                 REFERENCES nnp2.region ON UPDATE CASCADE ON DELETE SET NULL;
     ALTER TABLE nnp2.range_short
         ADD CONSTRAINT range_short_city_id_fkey
             FOREIGN KEY (city_id)
                 REFERENCES nnp2.city ON UPDATE CASCADE ON DELETE SET NULL;
     ALTER TABLE nnp2.range_short
         ADD CONSTRAINT range_short_operator_id_fkey
             FOREIGN KEY (operator_id)
                 REFERENCES nnp2.operator ON UPDATE CASCADE ON DELETE SET NULL;

     -- log delete
     INSERT INTO nnp2.range_short_log(event, inserted_at) VALUES('constraints created', timeofday()::timestamp);

     -- add indexes
     create index "idx-range_short-country_code"
         on nnp2.range_short (country_code);

     create index "idx-range_short-ndc_type_id"
         on nnp2.range_short (ndc_type_id);

     create index "idx-range_short-region_id"
         on nnp2.range_short (region_id);

     create index "idx-range_short-city_id"
         on nnp2.range_short (city_id);

     create index "idx-range_short-operator_id"
         on nnp2.range_short (operator_id);

     create index "idx-range_short-number_from"
         on nnp2.range_short (number_from);

     create index "idx-range_short-number_to"
         on nnp2.range_short (number_to);

     create index "idx-range_short-full_number_from"
         on nnp2.range_short (full_number_from);

     create index "idx-range_short-full_number_to"
         on nnp2.range_short (full_number_to);

     -- log delete
     INSERT INTO nnp2.range_short_log(event, inserted_at) VALUES('indexes created', timeofday()::timestamp);

 END;
 $$;


ALTER FUNCTION nnp2.range_short_renew() OWNER TO postgres;