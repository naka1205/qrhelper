<?php
namespace QrHelper\QrReader\Qrcode\Decoder;

use QrHelper\QrReader\Common\BitMatrix;

abstract class DataMask
{

    /**
     * See ISO 18004:2006 6.8.1
     */
    private static $DATA_MASKS = array();

    static function Init()
    {
        self::$DATA_MASKS = array(
            new DataMask000(),
            new DataMask001(),
            new DataMask010(),
            new DataMask011(),
            new DataMask100(),
            new DataMask101(),
            new DataMask110(),
            new DataMask111(),
        );
    }

    function __construct()
    {

    }

    /**
     * <p>Implementations of this method reverse the data masking process applied to a QR Code and
     * make its bits ready to read.</p>
     *
     * @param bits representation of QR Code bits
     * @param dimension dimension of QR Code, represented by bits, being unmasked
     */
    final function unmaskBitMatrix($bits, $dimension)
    {
        for ($i = 0; $i < $dimension; $i++) {
            for ($j = 0; $j < $dimension; $j++) {
                if ($this->isMasked($i, $j)) {
                    $bits->flip($j, $i);
                }
            }
        }
    }

    abstract function isMasked($i, $j);

    /**
     * @param reference a value between 0 and 7 indicating one of the eight possible
     * data mask patterns a QR Code may use
     * @return DataMask encapsulating the data mask pattern
     */
    static function forReference($reference)
    {
        if ($reference < 0 || $reference > 7) {
            throw new InvalidArgumentException();
        }
        return self::$DATA_MASKS[$reference];
    }
}
DataMask::Init();
/**
 * 000: mask bits for which (x + y) mod 2 == 0
 */
final class DataMask000 extends DataMask {
    // @Override
    function isMasked($i, $j) {
        return (($i + $j) & 0x01) == 0;
    }
}

/**
 * 001: mask bits for which x mod 2 == 0
 */
final class DataMask001 extends DataMask {
    //@Override
    function isMasked($i, $j) {
        return ($i & 0x01) == 0;
    }
}

/**
 * 010: mask bits for which y mod 3 == 0
 */
final class DataMask010 extends DataMask {
    //@Override
    function isMasked($i, $j) {
        return $j % 3 == 0;
    }
}

/**
 * 011: mask bits for which (x + y) mod 3 == 0
 */
final class DataMask011 extends DataMask {
    //@Override
    function isMasked($i, $j) {
        return ($i + $j) % 3 == 0;
    }
}

/**
 * 100: mask bits for which (x/2 + y/3) mod 2 == 0
 */
final class DataMask100 extends DataMask {
    //@Override
    function isMasked($i, $j) {
        return intval((intval($i / 2) + intval($j /3)) & 0x01) == 0;
    }
}

/**
 * 101: mask bits for which xy mod 2 + xy mod 3 == 0
 */
final class DataMask101 extends DataMask {
    //@Override
    function isMasked($i, $j) {
        $temp = $i * $j;
        return ($temp & 0x01) + ($temp % 3) == 0;
    }
}

/**
 * 110: mask bits for which (xy mod 2 + xy mod 3) mod 2 == 0
 */
final class DataMask110 extends DataMask {
    //@Override
    function isMasked($i, $j) {
        $temp = $i * $j;
        return ((($temp & 0x01) + ($temp % 3)) & 0x01) == 0;
    }
}

/**
 * 111: mask bits for which ((x+y)mod 2 + xy mod 3) mod 2 == 0
 */
final class DataMask111 extends DataMask {
    //@Override
    function isMasked($i, $j) {
        return (((($i + $j) & 0x01) + (($i * $j) % 3)) & 0x01) == 0;
    }
}

