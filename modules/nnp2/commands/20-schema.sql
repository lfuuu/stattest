CREATE SEQUENCE nnp2.operator_id_seq;
ALTER SEQUENCE nnp2.operator_id_seq OWNER to postgres;

create table nnp2.operator
(
    id            integer default nextval('nnp2.operator_id_seq'::regclass) not null
        constraint operator_pkey
            primary key,
    name          varchar(255),
    cnt           bigint  default 0                                         not null,
    country_code  integer                                                   not null,
    name_translit varchar(255),
    "group"       integer default 0                                         not null,
    parent_id     integer
        constraint operator_parent_id_fkey
            references nnp2.operator
            on update cascade on delete set null,
    is_valid      boolean default false
);

alter table nnp2.operator
    owner to postgres;

comment on column nnp2.operator.parent_id is 'Родитель';
comment on column nnp2.operator.is_valid is 'Подтверждён';









CREATE SEQUENCE nnp2.ndc_type_id_seq;
ALTER SEQUENCE nnp2.ndc_type_id_seq OWNER to postgres;

create table nnp2.ndc_type
(
    id                integer default nextval('nnp2.ndc_type_id_seq'::regclass) not null
        constraint ndc_type_pkey
            primary key,
    name              varchar(255),
    is_city_dependent boolean default false                                     not null,
    parent_id         integer
        constraint ndc_type_parent_id_fkey
            references nnp2.ndc_type
            on update cascade on delete set null,
    is_valid          boolean default false
);

alter table nnp2.ndc_type
    owner to postgres;

comment on column nnp2.ndc_type.parent_id is 'Родитель';
comment on column nnp2.ndc_type.is_valid is 'Подтверждён';










CREATE SEQUENCE nnp2.region_id_seq;
ALTER SEQUENCE nnp2.region_id_seq OWNER to postgres;

create table nnp2.region
(
    id            integer    default nextval('nnp2.region_id_seq'::regclass) not null
        constraint region_pkey
            primary key,
    name          varchar(255),
    name_translit varchar(255),
    country_code  integer                                                    not null,
    cnt           bigint     default 0                                       not null,
    iso           varchar(3) default ''::character varying,
    parent_id     integer
        constraint region_parent_id_fkey
            references nnp2.region
            on update cascade on delete set null,
    is_valid      boolean    default false
);

alter table nnp2.region
    owner to postgres;

comment on column nnp2.region.parent_id is 'Родитель';
comment on column nnp2.region.is_valid is 'Подтверждён';

create index region_country_code_idx
    on nnp2.region (country_code);

create index region_parent_id_idx
    on nnp2.region (parent_id);














CREATE SEQUENCE nnp2.city_id_seq;
ALTER SEQUENCE nnp2.city_id_seq OWNER to postgres;

create table nnp2.city
(
    id            integer default nextval('nnp2.city_id_seq'::regclass) not null
        constraint city_pkey
            primary key,
    name          varchar(255),
    name_translit varchar(255),
    country_code  integer                                               not null,
    region_id     integer
        constraint city_region_id_fkey
            references nnp2.region
            on update cascade on delete set null,
    cnt           bigint  default 0                                     not null,
    parent_id     integer
        constraint city_parent_id_fkey
            references nnp2.city
            on update cascade on delete set null,
    is_valid      boolean default false
);

alter table nnp2.city
    owner to postgres;

comment on column nnp2.city.parent_id is 'Родитель';
comment on column nnp2.city.is_valid is 'Подтверждён';

create index city_country_code_idx
    on nnp2.city (country_code);

create index city_region_id_idx
    on nnp2.city (region_id);














CREATE SEQUENCE nnp2.geo_place_id_seq;
ALTER SEQUENCE nnp2.geo_place_id_seq OWNER to postgres;

create table nnp2.geo_place
(
    id             integer default nextval('nnp2.geo_place_id_seq'::regclass) not null
        constraint geo_place_pkey
            primary key,
    country_code   integer                                                    not null,
    ndc            varchar(10)                                                not null,
    region_id      integer
        constraint geo_place_reg_id_fkey
            references nnp2.region
            on update cascade on delete set null,
    city_id        integer
        constraint geo_place_city_id_fkey
            references nnp2.city
            on update cascade on delete set null,
    cnt            bigint  default 0                                         not null,
    parent_id      integer
        constraint geo_place_parent_id_fkey
            references nnp2.geo_place
            on update cascade on delete set null,
    is_valid       boolean default false,
    insert_time    timestamp(0)                                               not null,
    insert_user_id integer,
    update_time    timestamp(0),
    update_user_id integer
);

alter table nnp2.geo_place
    owner to postgres;

comment on column nnp2.geo_place.country_code is 'Код страны.';

comment on column nnp2.geo_place.ndc is 'NDC.';

comment on column nnp2.geo_place.region_id is 'Регион nnp2.region.id';
comment on column nnp2.geo_place.city_id is 'Город nnp2.city.id';

comment on column nnp2.geo_place.parent_id is 'Родитель';
comment on column nnp2.geo_place.is_valid is 'Подтверждён';

comment on column nnp2.geo_place.insert_time is 'Когда создал';
comment on column nnp2.geo_place.insert_user_id is 'Кто создал';
comment on column nnp2.geo_place.update_time is 'Когда изменено';
comment on column nnp2.geo_place.update_user_id is 'Кем изменено';

create unique index geo_place_unique_key
    on nnp2.geo_place (country_code, ndc, region_id, city_id);











CREATE SEQUENCE nnp2.range_short_id_seq;
ALTER SEQUENCE nnp2.range_short_id_seq OWNER to postgres;

create table nnp2.range_short
(
    id                    integer default nextval('nnp2.range_short_id_seq'::regclass) not null
        constraint range_short_pkey
            primary key,
    country_code          integer                                                             not null,
    ndc                   varchar(10),
    ndc_type_id           integer
        constraint range_short_ndc_type_id_fkey
            references nnp2.ndc_type
            on update cascade on delete set null,
    region_id             integer
        constraint range_short_reg_id_fkey
            references nnp2.region
            on update cascade on delete set null,
    city_id               integer
        constraint range_short_city_id_fkey
            references nnp2.city
            on update cascade on delete set null,
    operator_id           integer
        constraint range_short_operator_id_fkey
            references nnp2.operator
            on update cascade on delete set null,
    number_from           bigint,
    number_to             bigint,
    full_number_from      bigint,
    full_number_to        bigint,
    allocation_date_start date,
    insert_time           timestamp(0)                                                        not null,
    insert_user_id        integer,
    update_time           timestamp(0),
    update_user_id        integer
);

comment on column nnp2.range_short.country_code is 'Код страны.';
comment on column nnp2.range_short.ndc is 'NDC.';
comment on column nnp2.range_short.ndc_type_id is 'Тип NDC.';
comment on column nnp2.range_short.region_id is 'Регион nnp2.region.id';
comment on column nnp2.range_short.city_id is 'Город nnp2.city.id';
comment on column nnp2.range_short.operator_id is 'Оператор nnp2.operator.id';

comment on column nnp2.range_short.number_from is 'Номер от.';
comment on column nnp2.range_short.number_to is 'Номер до.';

comment on column nnp2.range_short.full_number_from is 'Полный номер от.';
comment on column nnp2.range_short.full_number_to is 'Полный номер до.';

comment on column nnp2.range_short.insert_time is 'Когда создал';
comment on column nnp2.range_short.insert_user_id is 'Кто создал';
comment on column nnp2.range_short.update_time is 'Когда изменено';
comment on column nnp2.range_short.update_user_id is 'Кем изменено';

alter table nnp2.range_short
    owner to postgres;

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







CREATE SEQUENCE nnp2.range_short_log_id_seq;
ALTER SEQUENCE nnp2.range_short_log_id_seq OWNER to postgres;

CREATE TABLE nnp2.range_short_log (
    id integer DEFAULT nextval('nnp2.range_short_log_id_seq'::regclass) NOT NULL,
    event character varying(32) NOT NULL,
    inserted_at timestamp without time zone NOT NULL
);

ALTER TABLE nnp2.range_short_log OWNER TO postgres;








CREATE SEQUENCE nnp2.number_range_id_seq;
ALTER SEQUENCE nnp2.number_range_id_seq OWNER to postgres;

create table nnp2.number_range
(
    id                    integer default nextval('nnp2.number_range_id_seq'::regclass) not null
        constraint number_range_pkey
            primary key,
    geo_place_id          integer
        constraint number_range_geo_place_id_fkey
            references nnp2.geo_place
            on update cascade on delete set null,
    ndc_type_id           integer
        constraint geo_place_ndc_type_id_fkey
            references nnp2.ndc_type
            on update cascade on delete set null,
    operator_id           integer
        constraint geo_place_operator_id_fkey
            references nnp2.operator
            on update cascade on delete set null,
    number_from           bigint,
    number_to             bigint,
    full_number_from      bigint,
    full_number_to        bigint,
    cnt                   bigint  default 1                                         not null,
    is_active             boolean default true,
    is_valid              boolean default false,
    allocation_reason     varchar(255),
    allocation_date_start date,
    allocation_date_stop  date,
    comment               varchar(255),

    previous_id integer
        constraint number_range_parent_id_fkey
            references nnp2.number_range
            on update cascade on delete set null,

    range_short_id       integer
--         constraint number_range_range_short_id_fkey
--             references nnp2.range_short
--             on update cascade on delete set null
    ,
    range_short_old_id   integer
--         constraint number_range_range_short_old_id_fkey
--             references nnp2.range_short
--             on update cascade on delete set null
    ,
    insert_time           timestamp(0)                                                  not null,
    insert_user_id        integer,
    update_time           timestamp(0),
    update_user_id        integer,
    stop_time             timestamp(0),
    stop_user_id          integer,
    constraint valid_range
        check ((((full_number_from <= full_number_to) AND (full_number_from <> 0)) AND (full_number_to <> 0)) AND
               (floor(log((full_number_to)::double precision)) = floor(log((full_number_from)::double precision))))
);

comment on column nnp2.number_range.geo_place_id is 'Местоположение.';
comment on column nnp2.range_short.ndc_type_id is 'Тип NDC.';
comment on column nnp2.range_short.operator_id is 'Оператор.';

comment on column nnp2.number_range.number_from is 'Номер от.';
comment on column nnp2.number_range.number_to is 'Номер до.';

comment on column nnp2.number_range.full_number_from is 'Полный номер от.';
comment on column nnp2.number_range.full_number_to is 'Полный номер до.';

comment on column nnp2.number_range.is_active is 'Активна.';

comment on column nnp2.number_range.allocation_reason is 'Причина подключения.';
comment on column nnp2.number_range.allocation_date_start is 'Дата подключения.';
comment on column nnp2.number_range.allocation_date_stop is 'Дата отключения.';
comment on column nnp2.number_range.comment is 'Комментарий.';

comment on column nnp2.number_range.previous_id is 'Предыдущий диапазон';

comment on column nnp2.number_range.range_short_id is 'Готовый диапазон';
comment on column nnp2.number_range.range_short_old_id is 'Прошлый готовый диапазон';

alter table nnp2.number_range
    owner to postgres;

create index "idx-nnp_number_range-number_from"
    on nnp2.number_range (number_from);

create index "idx-nnp_number_range-number_to"
    on nnp2.number_range (number_to);

create index "idx-nnp_number_range-full_number_from"
    on nnp2.number_range (full_number_from);

create index "idx-nnp_number_range-full_number_to"
    on nnp2.number_range (full_number_to);

create index "idx-nnp_number_range-geo_place_id"
    on nnp2.number_range (geo_place_id);

create index "idx-nnp_number_range-ndc_type_id"
    on nnp2.number_range (ndc_type_id);

create index "idx-nnp_number_range-operator_id"
    on nnp2.number_range (operator_id);