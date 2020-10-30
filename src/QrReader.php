<?php
namespace QrHelper;

use QrHelper\QrReader\IMagickLuminanceSource;
use QrHelper\QrReader\GDLuminanceSource;
use QrHelper\QrReader\BinaryBitmap;
use QrHelper\QrReader\NotFoundException;
use QrHelper\QrReader\FormatException;
use QrHelper\QrReader\ChecksumException;
use QrHelper\QrReader\Common\HybridBinarizer;
use QrHelper\QrReader\Qrcode\QRCodeReader;

final class QrReader
{
    const SOURCE_TYPE_FILE = 'file';
    const SOURCE_TYPE_BLOB = 'blob';
    const SOURCE_TYPE_RESOURCE = 'resource';

    public $result;

    function __construct($imgsource, $sourcetype = QrReader::SOURCE_TYPE_FILE, $isUseImagickIfAvailable = true)
    {

        try {
            switch($sourcetype) {
                case QrReader::SOURCE_TYPE_FILE:
                    if($isUseImagickIfAvailable && extension_loaded('imagick')) {
                        $im = new Imagick();
                        $im->readImage($imgsource);
                    }else {
                        $image = file_get_contents($imgsource);
                        $im = imagecreatefromstring($image);
                    }

                    break;

                case QrReader::SOURCE_TYPE_BLOB:
                    if($isUseImagickIfAvailable && extension_loaded('imagick')) {
                        $im = new Imagick();
                        $im->readimageblob($imgsource);
                    }else {
                        $im = imagecreatefromstring($imgsource);
                    }

                    break;

                case QrReader::SOURCE_TYPE_RESOURCE:
                    $im = $imgsource;
                    if($isUseImagickIfAvailable && extension_loaded('imagick')) {
                        $isUseImagickIfAvailable = true;
                    }else {
                        $isUseImagickIfAvailable = false;
                    }

                    break;
            }
            
            $im = $this->resize($im,370,370);

            if($isUseImagickIfAvailable && extension_loaded('imagick')) {
                $width = $im->getImageWidth();
                $height = $im->getImageHeight();
                $source = new IMagickLuminanceSource($im, $width, $height);
            }else {
                $width = imagesx($im);
                $height = imagesy($im);
                $source = new GDLuminanceSource($im, $width, $height);
            }
            $histo = new HybridBinarizer($source);
            $bitmap = new BinaryBitmap($histo);
            $reader = new QRCodeReader();

            $this->result = $reader->decode($bitmap);
        }catch ( NotFoundException $er){
            $this->result = false;
        }catch( FormatException $er){
            $this->result = false;
        }catch( ChecksumException $er){
            $this->result = false;
        }
    }

    public function resize($image,$w,$h){
        $width = imagesx($image);
        $height = imagesy($image);
        $thumb = imagecreatetruecolor ($w, $h);
        imagecopyresized ($thumb, $image, 0, 0, 0, 0, $w, $h, $width, $height);
        return $thumb;
    }

    public function text()
    {
        if(method_exists($this->result,'toString')) {
            return  ($this->result->toString());
        }else{
            return $this->result;
        }
    }

    public function decode()
    {
        return $this->text();
    }
}

