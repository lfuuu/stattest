SELECT 
                cl.status, cl.id, cl.client, cl.name, cl.manager, cl.support, cl.telemarketing, s.name AS sale_channel, cl.created, cl.currency, 
                DATE(cls.ts) date_zayavka
           FROM (SELECT c.*, cg.name, cc.business_process_status_id, cc.manager FROM clients c INNER JOIN client_contract cc INNER JOIN client_contragent cg) cl         LEFT JOIN sale_channels s ON (s.id = cl.sale_channel)
inner join client_grid_statuses cs 
on (cs.client_id = cl.id and cs.grid_status_id = 110)
          WHERE
               cl.contract_type_id=4