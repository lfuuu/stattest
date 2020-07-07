DROP TABLE IF EXISTS nnp2.import_history;
DROP SEQUENCE IF EXISTS nnp2.import_history_id_seq;

CREATE SEQUENCE nnp2.import_history_id_seq;
ALTER SEQUENCE nnp2.import_history_id_seq OWNER to postgres;

create table nnp2.import_history
(
    id                integer default nextval('nnp2.import_history_id_seq'::regclass) not null
        constraint import_history_pkey
            primary key,
    version           integer,
    country_file_id   integer
        constraint import_history_country_file_id_id_fkey
            references nnp.country_files
            on update cascade on delete set null,
    lines_load        integer,
    lines_processed   integer,
    ranges_before     integer,
    ranges_updated    integer,
    ranges_added      integer,
    state             integer default 0 not null,
    started_at        timestamp(0) not null,
    finished_at       timestamp(0)
);

alter table nnp2.import_history
    owner to postgres;