<?php
namespace QrHelper\QrReader\Common;
use InvalidArgumentException;

final class BitSource {

  private $bytes;
  private $byteOffset = 0;
  private $bitOffset = 0;

  /**
   * @param bytes bytes from which this will read bits. Bits will be read from the first byte first.
   * Bits are read within a byte from most-significant to least-significant bit.
   */
  public function __construct($bytes) {
    $this->bytes = $bytes;
  }

  /**
   * @return index of next bit in current byte which would be read by the next call to {@link #readBits(int)}.
   */
  public function getBitOffset() {
    return $this->bitOffset;
  }

  /**
   * @return index of next byte in input byte array which would be read by the next call to {@link #readBits(int)}.
   */
  public function getByteOffset() {
    return $this->byteOffset;
  }

  /**
   * @param numBits number of bits to read
   * @return int representing the bits read. The bits will appear as the least-significant
   *         bits of the int
   * @throws IllegalArgumentException if numBits isn't in [1,32] or more than is available
   */
  public function readBits($numBits) {
    if ($numBits < 1 || $numBits > 32 || $numBits > $this->available()) {
      throw new InvalidArgumentException(strval($numBits));
    }

    $result = 0;

    // First, read remainder from current byte
    if ($this->bitOffset > 0) {
      $bitsLeft = 8 - $this->bitOffset;
      $toRead = $numBits < $bitsLeft ? $numBits : $bitsLeft;
      $bitsToNotRead = $bitsLeft - $toRead;
      $mask = (0xFF >> (8 - $toRead)) << $bitsToNotRead;
      $result = ($this->bytes[$this->byteOffset] & $mask) >> $bitsToNotRead;
      $numBits -= $toRead;
      $this->bitOffset += $toRead;
      if ($this->bitOffset == 8) {
        $this->bitOffset = 0;
        $this->byteOffset++;
      }
    }

    // Next read whole bytes
    if ($numBits > 0) {
      while ($numBits >= 8) {
        $result = ($result << 8) | ($this->bytes[$this->byteOffset] & 0xFF);
        $this->byteOffset++;
        $numBits -= 8;
      }

      // Finally read a partial byte
      if ($numBits > 0) {
        $bitsToNotRead = 8 - $numBits;
        $mask = (0xFF >> $bitsToNotRead) << $bitsToNotRead;
        $result = ($result << $numBits) | (($this->bytes[$this->byteOffset] & $mask) >> $bitsToNotRead);
        $this->bitOffset += $numBits;
      }
    }

    return $result;
  }

  /**
   * @return number of bits that can be read successfully
   */
  public function available() {
    return 8 * (count($this->bytes) - $this->byteOffset) - $this->bitOffset;
  }

}
