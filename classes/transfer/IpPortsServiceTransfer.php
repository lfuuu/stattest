<?php

namespace app\classes\transfer;

/**
 * Класс переноса услуг типа "IP Port"
 * @package app\classes\transfer
 */
class IpPortsServiceTransfer extends ServiceTransfer
{
    /*
    -- select * from usage_ip_ports where id=7720;
    -- select * from usage_ip_routes where port_id=7720;
    --  select * from tech_cpe where service='usage_ip_ports' and id_service = 7720
    --  select * from usage_ip_ppp ?
    */
}