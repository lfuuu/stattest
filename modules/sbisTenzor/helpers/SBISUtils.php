<?php

namespace app\modules\sbisTenzor\helpers;

use app\models\Organization;

class SBISUtils
{
    /**
     * Generate Uuid
     *
     * @return string
     */
    public static function generateUuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * Check directory and create it (/tmp/test/dir)
     *
     * @param $dirPath
     */
    public static function checkDirectory($dirPath)
    {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0775, true);
        }
    }

    /**
     * Get short organization name
     *
     * @param Organization $organization
     * @return string
     */
    public static function getShortOrganizationName(Organization $organization)
    {
        return strtr(
            $organization->name,
            [
                'ООО ' => '',
                '«' => '',
                '»' => '',
            ]
        );
    }

    /**
     * Удаление параметра из URL
     *
     * @param string $url
     * @param string $variableName
     * @return string
     */
    public static function removeVariableFromURL($url, $variableName) {
        return trim(preg_replace('/([?&])'.$variableName.'=[^&]+(&|$)/','$1',$url), '?');
    }
}