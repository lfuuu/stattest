<?php
namespace app\classes\QRcode;

use \PHPQRCode;

class QRencode extends \PHPQRCode\QRencode
{
    public static function factory($level = Constants::QR_ECLEVEL_L, $size = 3, $margin = 4)
    {
        $enc = new static();
        $enc->size = $size;
        $enc->margin = $margin;

        switch ($level.'') {
            case '0':
            case '1':
            case '2':
            case '3':
                    $enc->level = $level;
                break;
            case 'l':
            case 'L':
                    $enc->level = \PHPQRCode\Constants::QR_ECLEVEL_L;
                break;
            case 'm':
            case 'M':
                    $enc->level = \PHPQRCode\Constants::QR_ECLEVEL_M;
                break;
            case 'q':
            case 'Q':
                    $enc->level = \PHPQRCode\Constants::QR_ECLEVEL_Q;
                break;
            case 'h':
            case 'H':
                    $enc->level = \PHPQRCode\Constants::QR_ECLEVEL_H;
                break;
        }

        return $enc;
    }

    public function encodeGIF($intext, $outfile = false,$saveandprint=false)
    {
        try {
            ob_start();
            $tab = $this->encode($intext);
            $err = ob_get_contents();
            ob_end_clean();

            if ($err != '')
                \PHPQRCode\QRtools::log($outfile, "ERROR: " . $err);

            $maxSize = (int)(\PHPQRCode\Constants::QR_PNG_MAXIMUM_SIZE / (count($tab)+2*$this->margin));

            \PHPQRCode\QRimage::gif($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin,$saveandprint);
        } catch (Exception $e) {
            echo $e->getMessage();
            die();

            \PHPQRCode\QRtools::log($outfile, $e->getMessage());
        }
    }
}

