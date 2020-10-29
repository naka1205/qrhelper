<?php
namespace QrHelper\QrReader\Qrcode\Detector;

use  QrHelper\QrReader\ResultPoint;

final class FinderPattern extends ResultPoint {

private $estimatedModuleSize;
private $count;



  function __construct($posX, $posY, $estimatedModuleSize, $count=1) {
    parent::__construct($posX, $posY);
    $this->estimatedModuleSize = $estimatedModuleSize;
    $this->count = $count;
}

  public function getEstimatedModuleSize() {
    return $this->estimatedModuleSize;
  }

  function getCount() {
    return $this->count;
  }

  /*
  void incrementCount() {
    this.count++;
  }
   */

  /**
   * <p>Determines if this finder pattern "about equals" a finder pattern at the stated
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
   * with a new estimate. It returns a new {@code FinderPattern} containing a weighted average
   * based on count.
   */
  function combineEstimate($i, $j, $newModuleSize) {
    $combinedCount = $this->count + 1;
    $combinedX = ($this->count * $this->getX() + $j) / $combinedCount;
    $combinedY = ($this->count * $this->getY() + $i) / $combinedCount;
    $combinedModuleSize = ($this->count * $this->estimatedModuleSize + $newModuleSize) / $combinedCount;
    return new FinderPattern($combinedX, $combinedY, $combinedModuleSize, $combinedCount);
  }

}
