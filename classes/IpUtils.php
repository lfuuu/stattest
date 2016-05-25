<?php
namespace app\classes;

abstract class IpUtils
{

    /**
     * Converts a printable IP into a packed binary string
     *
     * @author Mike Mackintosh - mike@bakeryphp.com
     * @param string $ip
     * @return string|null
     */
    public static function dtr_pton($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return current(unpack('A4', inet_pton($ip)));
        }
        else if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return current(unpack('A16', inet_pton($ip)));
        }
        return null;
    }

    /**
     * Converts an unpacked binary string into a printable IP
     *
     * @author Mike Mackintosh - mike@bakeryphp.com
     * @param string $str
     * @return string
     */
    public static function dtr_ntop($str) {
        $length = strlen($str);
        if ($length == 16 || $length == 4) {
            return inet_ntop(pack('A' . strlen($str), $str));
        }
        return $str;
    }

}