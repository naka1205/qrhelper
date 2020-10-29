<?php
namespace QrHelper\QrReader\Common;

use QrHelper\QrReader\Binarizer;
use QrHelper\QrReader\LuminanceSource;
use QrHelper\QrReader\NotFoundException;

final class HybridBinarizer extends GlobalHistogramBinarizer {

// This class uses 5x5 blocks to compute local luminance, where each block is 8x8 pixels.
// So this is the smallest dimension in each axis we can accept.
    private static  $BLOCK_SIZE_POWER = 3;
    private static $BLOCK_SIZE = 8; // ...0100...00
    private static $BLOCK_SIZE_MASK  = 7;   // ...0011...11
    private static $MINIMUM_DIMENSION = 40;
    private static $MIN_DYNAMIC_RANGE=24;

    private $matrix;

    public function __construct($source) {

        parent::__construct($source);
        self::$BLOCK_SIZE_POWER = 3;
        self::$BLOCK_SIZE = 1 << self::$BLOCK_SIZE_POWER; // ...0100...00
        self::$BLOCK_SIZE_MASK = self::$BLOCK_SIZE - 1;   // ...0011...11
        self::$MINIMUM_DIMENSION = self::$BLOCK_SIZE * 5;
        self::$MIN_DYNAMIC_RANGE = 24;

    }

    /**
     * Calculates the final BitMatrix once for all requests. This could be called once from the
     * constructor instead, but there are some advantages to doing it lazily, such as making
     * profiling easier, and not doing heavy lifting when callers don't expect it.
     */
//@Override
    public function getBlackMatrix(){
        if ($this->matrix != null) {
            return $this->matrix;
        }
        $source = $this->getLuminanceSource();
        $width = $source->getWidth();
        $height = $source->getHeight();
        if ($width >= self::$MINIMUM_DIMENSION && $height >= self::$MINIMUM_DIMENSION) {
            $luminances = $source->getMatrix();
            $subWidth = $width >> self::$BLOCK_SIZE_POWER;
            if (($width & self::$BLOCK_SIZE_MASK) != 0) {
                $subWidth++;
            }
            $subHeight = $height >> self::$BLOCK_SIZE_POWER;
            if (($height & self::$BLOCK_SIZE_MASK) != 0) {
                $subHeight++;
            }
            $blackPoints = $this->calculateBlackPoints($luminances, $subWidth, $subHeight, $width, $height);

            $newMatrix = new BitMatrix($width, $height);
            $this->calculateThresholdForBlock($luminances, $subWidth, $subHeight, $width, $height, $blackPoints, $newMatrix);
            $this->matrix = $newMatrix;
        } else {
// If the image is too small, fall back to the global histogram approach.
            $this->matrix = parent::getBlackMatrix();
        }
        return $this->matrix;
    }

//@Override
    public  function createBinarizer($source) {
        return new HybridBinarizer($source);
    }

    /**
     * For each block in the image, calculate the average black point using a 5x5 grid
     * of the blocks around it. Also handles the corner cases (fractional blocks are computed based
     * on the last pixels in the row/column which are also used in the previous block).
     */
    private static function calculateThresholdForBlock($luminances,
                                                       $subWidth,
                                                       $subHeight,
                                                       $width,
                                                       $height,
                                                       $blackPoints,
                                                       $matrix) {
        for ($y = 0; $y < $subHeight; $y++) {
            $yoffset = intval32bits($y << self::$BLOCK_SIZE_POWER);
            $maxYOffset = $height - self::$BLOCK_SIZE;
            if ($yoffset > $maxYOffset) {
                $yoffset = $maxYOffset;
            }
            for ($x = 0; $x < $subWidth; $x++) {
                $xoffset = intval32bits($x << self::$BLOCK_SIZE_POWER);
                $maxXOffset = $width - self::$BLOCK_SIZE;
                if ($xoffset > $maxXOffset) {
                    $xoffset = $maxXOffset;
                }
                $left = self::cap($x, 2, $subWidth - 3);
                $top = self::cap($y, 2, $subHeight - 3);
                $sum = 0;
                for ($z = -2; $z <= 2; $z++) {
                    $blackRow = $blackPoints[$top + $z];
                    $sum += $blackRow[$left - 2] + $blackRow[$left - 1] + $blackRow[$left] + $blackRow[$left + 1] + $blackRow[$left + 2];
                }
                $average = intval($sum / 25);

                self::thresholdBlock($luminances, $xoffset, $yoffset, $average, $width, $matrix);
            }
        }
    }

    private static function  cap($value, $min, $max) {
        if($value<$min){
            return $min;
        }elseif($value>$max){
            return $max;
        }else{
            return $value;
        }



    }

    /**
     * Applies a single threshold to a block of pixels.
     */
    private static function thresholdBlock($luminances,
                                           $xoffset,
                                           $yoffset,
                                           $threshold,
                                           $stride,
                                           $matrix) {

        for ($y = 0, $offset = $yoffset * $stride + $xoffset; $y < self::$BLOCK_SIZE; $y++, $offset += $stride) {
            for ($x = 0; $x < self::$BLOCK_SIZE; $x++) {
// Comparison needs to be <= so that black == 0 pixels are black even if the threshold is 0.
                if (($luminances[$offset + $x] & 0xFF) <= $threshold) {
                    $matrix->set($xoffset + $x, $yoffset + $y);
                }
            }
        }
    }

    private static function calculateBlackPoints($luminances,
                                                 $subWidth,
                                                 $subHeight,
                                                 $width,
                                                 $height) {
        $blackPoints = fill_array(0,$subHeight,0);
        foreach($blackPoints as $key=>$point){
            $blackPoints[$key] = fill_array(0,$subWidth,0);
        }
        for ($y = 0; $y < $subHeight; $y++) {
            $yoffset = intval32bits($y << self::$BLOCK_SIZE_POWER);
            $maxYOffset = $height - self::$BLOCK_SIZE;
            if ($yoffset > $maxYOffset) {
                $yoffset = $maxYOffset;
            }
            for ($x = 0; $x < $subWidth; $x++) {
                $xoffset = intval32bits($x << self::$BLOCK_SIZE_POWER);
                $maxXOffset = $width - self::$BLOCK_SIZE;
                if ($xoffset > $maxXOffset) {
                    $xoffset = $maxXOffset;
                }
                $sum = 0;
                $min = 0xFF;
                $max = 0;
                for ($yy = 0, $offset = $yoffset * $width + $xoffset; $yy < self::$BLOCK_SIZE; $yy++, $offset += $width) {
                    for ($xx = 0; $xx < self::$BLOCK_SIZE; $xx++) {
                        $pixel = intval32bits(intval($luminances[intval($offset +$xx)]) & 0xFF);
                        $sum += $pixel;
// still looking for good contrast
                        if ($pixel < $min) {
                            $min = $pixel;
                        }
                        if ($pixel > $max) {
                            $max = $pixel;
                        }
                    }
// short-circuit min/max tests once dynamic range is met
                    if ($max - $min > self::$MIN_DYNAMIC_RANGE) {
// finish the rest of the rows quickly
                        for ($yy++, $offset += $width; $yy < self::$BLOCK_SIZE; $yy++, $offset += $width) {
                            for ($xx = 0; $xx < self::$BLOCK_SIZE; $xx++) {
                                $sum += intval32bits($luminances[$offset +$xx] & 0xFF);
                            }
                        }
                    }
                }

// The default estimate is the average of the values in the block.
                $average = intval32bits($sum >> (self::$BLOCK_SIZE_POWER * 2));
                if ($max - $min <= self::$MIN_DYNAMIC_RANGE) {
// If variation within the block is low, assume this is a block with only light or only
// dark pixels. In that case we do not want to use the average, as it would divide this
// low contrast area into black and white pixels, essentially creating data out of noise.
//
// The default assumption is that the block is light/background. Since no estimate for
// the level of dark pixels exists locally, use half the min for the block.
                    $average = intval($min / 2);

                    if ($y > 0 && $x > 0) {
// Correct the "white background" assumption for blocks that have neighbors by comparing
// the pixels in this block to the previously calculated black points. This is based on
// the fact that dark barcode symbology is always surrounded by some amount of light
// background for which reasonable black point estimates were made. The bp estimated at
// the boundaries is used for the interior.

// The (min < bp) is arbitrary but works better than other heuristics that were tried.
                        $averageNeighborBlackPoint =
                            intval(($blackPoints[$y - 1][$x] + (2 * $blackPoints[$y][$x - 1]) + $blackPoints[$y - 1][$x - 1]) / 4);
                        if ($min < $averageNeighborBlackPoint) {
                            $average = $averageNeighborBlackPoint;
                        }
                    }
                }
                $blackPoints[$y][$x] = intval($average);
            }
        }
        return $blackPoints;
    }

}
