<?php
namespace QrHelper\QrReader\Common\Reedsolomon;

final class GenericGF {

    public static $AZTEC_DATA_12;
    public static $AZTEC_DATA_10;
    public static $AZTEC_DATA_6;
    public static $AZTEC_PARAM;
    public static $QR_CODE_FIELD_256;
    public static $DATA_MATRIX_FIELD_256;
    public static $AZTEC_DATA_8;
    public static $MAXICODE_FIELD_64;

    private $expTable;
    private $logTable;
    private $zero;
    private $one;
    private $size;
    private $primitive;
    private $generatorBase;


    public static function Init(){
        self::$AZTEC_DATA_12 = new GenericGF(0x1069, 4096, 1); // x^12 + x^6 + x^5 + x^3 + 1
        self::$AZTEC_DATA_10 = new GenericGF(0x409, 1024, 1); // x^10 + x^3 + 1
        self::$AZTEC_DATA_6 = new GenericGF(0x43, 64, 1); // x^6 + x + 1
        self::$AZTEC_PARAM = new GenericGF(0x13, 16, 1); // x^4 + x + 1
        self::$QR_CODE_FIELD_256 = new GenericGF(0x011D, 256, 0); // x^8 + x^4 + x^3 + x^2 + 1
        self::$DATA_MATRIX_FIELD_256 = new GenericGF(0x012D, 256, 1); // x^8 + x^5 + x^3 + x^2 + 1
        self::$AZTEC_DATA_8 = self::$DATA_MATRIX_FIELD_256;
        self::$MAXICODE_FIELD_64 = self::$AZTEC_DATA_6;
    }


    /**
     * Create a representation of GF(size) using the given primitive polynomial.
     *
     * @param primitive irreducible polynomial whose coefficients are represented by
     *  the bits of an int, where the least-significant bit represents the constant
     *  coefficient
     * @param size the size of the field
     * @param b the factor b in the generator polynomial can be 0- or 1-based
     *  (g(x) = (x+a^b)(x+a^(b+1))...(x+a^(b+2t-1))).
     *  In most cases it should be 1, but for QR code it is 0.
     */
    public function __construct($primitive, $size, $b) {
        $this->primitive = $primitive;
        $this->size = $size;
        $this->generatorBase = $b;

        $this->expTable = array();
        $this->logTable =array();
        $x = 1;
        for ($i = 0; $i < $size; $i++) {
            $this->expTable[$i] = $x;
            $x *= 2; // we're assuming the generator alpha is 2
            if ($x >= $size) {
                $x ^= $primitive;
                $x &= $size-1;
            }
        }
        for ($i = 0; $i < $size-1; $i++) {
            $this->logTable[$this->expTable[$i]] = $i;
        }
        // logTable[0] == 0 but this should never be used
        $this->zero = new GenericGFPoly($this, array(0));
        $this->one = new GenericGFPoly($this, array(1));
    }

    function getZero() {
        return $this->zero;
    }

    function getOne() {
        return $this->one;
    }

    /**
     * @return the monomial representing coefficient * x^degree
     */
    function buildMonomial($degree, $coefficient) {
        if ($degree < 0) {
            throw new InvalidArgumentException();
        }
        if ($coefficient == 0) {
            return $this->zero;
        }
        $coefficients = fill_array(0,$degree+1,0);//new int[degree + 1];
        $coefficients[0] = $coefficient;
        return new GenericGFPoly($this, $coefficients);
    }

    /**
     * Implements both addition and subtraction -- they are the same in GF(size).
     *
     * @return sum/difference of a and b
     */
    static function addOrSubtract($a, $b) {
        return $a ^ $b;
    }

    /**
     * @return 2 to the power of a in GF(size)
     */
    function exp($a) {
        return $this->expTable[$a];
    }

    /**
     * @return base 2 log of a in GF(size)
     */
    function log($a) {
        if ($a == 0) {
            throw new InvalidArgumentException();
        }
        return $this->logTable[$a];
    }

    /**
     * @return multiplicative inverse of a
     */
    function inverse($a) {
        if ($a == 0) {
            throw new Exception();
        }
        return $this->expTable[$this->size - $this->logTable[$a] - 1];
    }

    /**
     * @return product of a and b in GF(size)
     */
    function multiply($a, $b) {
        if ($a == 0 || $b == 0) {
            return 0;
        }
        return $this->expTable[($this->logTable[$a] + $this->logTable[$b]) % ($this->size - 1)];
    }

    public function getSize() {
        return $this->size;
    }

    public function getGeneratorBase() {
        return $this->generatorBase;
    }

    // @Override
    public function  toString() {
        return "GF(0x" . dechex(intval($this->primitive)) . ',' . $this->size . ')';
    }

}
GenericGF::Init();