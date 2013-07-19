CREATE TABLE `tarifs_saas` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `status` enum('public','archive') NOT NULL DEFAULT 'public',
        `description` varchar(100) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL DEFAULT '',
        `period` enum('month') CHARACTER SET latin1 DEFAULT 'month',
        `currency` enum('USD','RUR') NOT NULL DEFAULT 'RUR',
        `price` decimal(13,4) NOT NULL DEFAULT '0.0000',

        `num_ports` int(4) not null default 0,
        `overrun_per_port` decimal(13,4) not null default '0.0000',
        `space` int(4) not null default 0,
        `overrun_per_mb` decimal(13,4) null null default '0.0000',

        `edit_user` int(11) NOT NULL DEFAULT '0',
        `edit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=koi8r;
