<?php

class Encoding {

	public static function toUtf8($data)
	{
		if (is_string($data)) {
			return iconv('koi8-r', 'utf-8', $data);
		} elseif (is_array($data)) {
			$translated = array();
			foreach ($data as $k => $v) {
				if (is_string($k)) {
					$k = iconv('koi8-r', 'utf-8', $k);
				}
				$translated[$k] = self::toUtf8($v);
			}
			return $translated;
		} elseif ($data instanceof stdClass) {
			$translated = array();
			foreach ((array)$data as $k => $v) {
				$k = iconv('koi8-r', 'utf-8', $k);
				$translated[$k] = self::toUtf8($v);
			}
			return (object)$translated;
		} else {
			return $data;
		}
	}

	public static function toKoi8r($data)
	{
		if (is_string($data)) {
			return iconv('utf-8', 'koi8-r//TRANSLIT', $data);
		} elseif (is_array($data)) {
			$translated = array();
			foreach ($data as $k => $v) {
				if (is_string($k)) {
					$k = iconv('utf-8', 'koi8-r//TRANSLIT', $k);
				}
				$translated[$k] = self::toKoi8r($v);
			}
			return $translated;
		} elseif ($data instanceof stdClass) {
			$translated = array();
			foreach ((array)$data as $k => $v) {
				$k = iconv('utf-8', 'koi8-r//TRANSLIT', $k);
				$translated[$k] = self::toKoi8r($v);
			}
			return (object)$translated;
		} else {
			return $data;
		}
	}
}
