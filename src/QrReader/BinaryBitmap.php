<?php

namespace QrHelper\QrReader;

use QrHelper\QrReader\Common\BitArray;
use QrHelper\QrReader\Common\BitMatrix;


final class BinaryBitmap {

    private  $binarizer;
    private  $matrix;

    public function __construct($binarizer) {
        if ($binarizer == null) {
            throw new \InvalidArgumentException("Binarizer must be non-null.");
        }
        $this->binarizer = $binarizer;
    }

    /**
     * @return The width of the bitmap.
     */
    public function getWidth() {
        return $this->binarizer->getWidth();
    }

    /**
     * @return The height of the bitmap.
     */
    public function getHeight() {
        return $this->binarizer->getHeight();
    }

    /**
     * Converts one row of luminance data to 1 bit data. May actually do the conversion, or return
     * cached data. Callers should assume this method is expensive and call it as seldom as possible.
     * This method is intended for decoding 1D barcodes and may choose to apply sharpening.
     *
     * @param y The row to fetch, which must be in [0, bitmap height)
     * @param row An optional preallocated array. If null or too small, it will be ignored.
     *            If used, the Binarizer will call BitArray.clear(). Always use the returned object.
     * @return The array of bits for this row (true means black).
     * @throws NotFoundException if row can't be binarized
     */
    public function getBlackRow($y, $row)  {
        return $this->binarizer->getBlackRow($y, $row);
    }

    /**
     * Converts a 2D array of luminance data to 1 bit. As above, assume this method is expensive
     * and do not call it repeatedly. This method is intended for decoding 2D barcodes and may or
     * may not apply sharpening. Therefore, a row from this matrix may not be identical to one
     * fetched using getBlackRow(), so don't mix and match between them.
     *
     * @return The 2D array of bits for the image (true means black).
     * @throws NotFoundException if image can't be binarized to make a matrix
     */
    public function getBlackMatrix(){
// The matrix is created on demand the first time it is requested, then cached. There are two
// reasons for this:
// 1. This work will never be done if the caller only installs 1D Reader objects, or if a
//    1D Reader finds a barcode before the 2D Readers run.
// 2. This work will only be done once even if the caller installs multiple 2D Readers.
        if ($this->matrix == null) {
            $this->matrix = $this->binarizer->getBlackMatrix();
        }
        return $this->matrix;
    }

    /**
     * @return Whether this bitmap can be cropped.
     */
    public function isCropSupported() {
        return $this->binarizer->getLuminanceSource()->isCropSupported();
    }

    /**
     * Returns a new object with cropped image data. Implementations may keep a reference to the
     * original data rather than a copy. Only callable if isCropSupported() is true.
     *
     * @param left The left coordinate, which must be in [0,getWidth())
     * @param top The top coordinate, which must be in [0,getHeight())
     * @param width The width of the rectangle to crop.
     * @param height The height of the rectangle to crop.
     * @return A cropped version of this object.
     */
    public function crop($left, $top, $width, $height) {
        $newSource = $this->binarizer->getLuminanceSource()->crop($left, $top, $width, $height);
        return new BinaryBitmap($this->binarizer->createBinarizer($newSource));
    }

    /**
     * @return Whether this bitmap supports counter-clockwise rotation.
     */
    public function isRotateSupported() {
        return $this->binarizer->getLuminanceSource()->isRotateSupported();
    }

    /**
     * Returns a new object with rotated image data by 90 degrees counterclockwise.
     * Only callable if {@link #isRotateSupported()} is true.
     *
     * @return A rotated version of this object.
     */
    public function rotateCounterClockwise() {
        $newSource = $this->binarizer->getLuminanceSource()->rotateCounterClockwise();
        return new BinaryBitmap($this->binarizer->createBinarizer($newSource));
    }

    /**
     * Returns a new object with rotated image data by 45 degrees counterclockwise.
     * Only callable if {@link #isRotateSupported()} is true.
     *
     * @return A rotated version of this object.
     */
    public function rotateCounterClockwise45() {
        $newSource = $this->binarizer->getLuminanceSource()->rotateCounterClockwise45();
        return new BinaryBitmap($this->binarizer->createBinarizer($newSource));
    }

//@Override
    public function toString() {
        try {
            return $this->getBlackMatrix()->toString();
        } catch (NotFoundException $e) {
            return "";
        }
    }

}