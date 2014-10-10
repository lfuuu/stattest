<?php

class ats2passcrypt
{
    public function cryptId($id, $salt = null)
    {
        $t = $salt === null ? time() : $salt;
        return $id.":".$t.":".md5($id.$t."aaa".$t);
    }

    public function decryptId($str)
    {
        list($id, $salt, $md5) = explode(":", $str."::::");
        return self::cryptId($id, $salt) == $str ? $id : false;
    }
}
