<?php
namespace QrHelper\QrReader\Common;

use QrHelper\QrReader\ResultPoint;

class DetectorResult {

    private  $bits;
    private  $points;

    public function __construct($bits, $points) {
        $this->bits = $bits;
        $this->points = $points;
    }

    public final function getBits() {
        return $this->bits;
    }

    public final function getPoints() {
        return $this->points;
    }

}