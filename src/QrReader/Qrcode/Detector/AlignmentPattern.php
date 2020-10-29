<?php
namespace QrHelperQrReaderQrcodeDetector;
use QrHelperQrReaderResultPoint;

final class AlignmentPattern extends ResultPoint {
    private $estimatedModuleSize;
    function __construct($posX, $posY, $estimatedModuleSize) {
        parent::__construct($posX, $posY);
        $this->estimatedModuleSize = $estimatedModuleSize;
    }
    /**
     * <p>Determines if this alignment pattern "about equals" an alignment pattern at the stated
     * position and size -- meaning, it is at nearly the same center with nearly the same size.</p>
     */
    function aboutEquals($moduleSize, $i, $j) {
        if (abs($i - $this->getY()) <= $moduleSize && abs($j - $this->getX()) <= $moduleSize) {
            $moduleSizeDiff = abs($moduleSize - $this->estimatedModuleSize);
            return $moduleSizeDiff <= 1.0 || $moduleSizeDiff <= $this->estimatedModuleSize;
        }
        return false;
    }
    /**
     * Combines this object's current estimate of a finder pattern position and module size
     * with a new estimate. It returns a new {@code FinderPattern} containing an average of the two.
     */
    function combineEstimate($i, $j, $newModuleSize) {
        $combinedX = ($this->getX() + $j) / 2.0;
        $combinedY = ($this->getY() + $i) / 2.0;
        $combinedModuleSize = ($this->estimatedModuleSize + $newModuleSize) / 2.0;
        return new AlignmentPattern($combinedX, $combinedY, $combinedModuleSize);
    }
}
