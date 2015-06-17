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

            self::gif($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin,$saveandprint);
        } catch (Exception $e) {
            echo $e->getMessage();
            die();

            \PHPQRCode\QRtools::log($outfile, $e->getMessage());
        }
    }

    private static function gif($frame, $filename = false, $pixelPerPoint = 8, $outerFrame = 4, $q = 85)
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if ($filename === false) {
            Header("Content-type: image/gif");
            ImageGif($image, null, $q);
        } else {
            ImageGif($image, $filename, $q);
        }

        ImageDestroy($image);
    }

    private static function image($frame, $pixelPerPoint = 4, $outerFrame = 4)
    {
        $h = count($frame);
        $w = strlen($frame[0]);

        $imgW = $w + 2*$outerFrame;
        $imgH = $h + 2*$outerFrame;

        $base_image =ImageCreate($imgW, $imgH);

        $col[0] = ImageColorAllocate($base_image,255,255,255);
        $col[1] = ImageColorAllocate($base_image,0,0,0);

        imagefill($base_image, 0, 0, $col[0]);

        for($y=0; $y<$h; $y++) {
            for($x=0; $x<$w; $x++) {
                if ($frame[$y][$x] == '1') {
                    ImageSetPixel($base_image,$x+$outerFrame,$y+$outerFrame,$col[1]);
                }
            }
        }

        $target_image =ImageCreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
        ImageCopyResized($target_image, $base_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH);
        ImageDestroy($base_image);

        return $target_image;
    }
}

