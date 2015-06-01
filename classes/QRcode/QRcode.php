<?php
namespace app\classes\QRcode;

use \app\classes\QRcode\QRencode;

class QRcode extends \PHPQRCode\QRcode
{
    public static function gif($text, $outfile = false, $level = Constants::QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint=false)
    {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodeGIF($text, $outfile, $saveandprint=false);
    }

}

