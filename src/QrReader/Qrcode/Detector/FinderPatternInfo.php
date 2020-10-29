<?php
namespace QrHelperQrReaderQrcodeDetector;
final class FinderPatternInfo {
    private $bottomLeft;
    private $topLeft;
    private $topRight;
    public function __construct($patternCenters) {
        $this->bottomLeft = $patternCenters[0];
        $this->topLeft = $patternCenters[1];
        $this->topRight = $patternCenters[2];
    }
    public function getBottomLeft() {
        return $this->bottomLeft;
    }
    public function getTopLeft() {
        return $this->topLeft;
    }
    public function getTopRight() {
        return $this->topRight;
    }
}
