<?php
namespace QrHelper\QrReader\Common;
use InvalidArgumentException;

final class BitMatrix {
    private $width;
    private $height;
    private $rowSize;
    private $bits;

    public function __construct($width, $height=false, $rowSize=false, $bits=false) {
        if(!$height){
            $height = $width;
        }
        if(!$rowSize){
            $rowSize = intval(($width + 31) / 32);
        }
        if(!$bits){
            $bits = fill_array(0,$rowSize*$height,0);array();//new int[rowSize * height];
        }
        $this->width = $width;
        $this->height = $height;
        $this->rowSize = $rowSize;
        $this->bits = $bits;
    }
    public static  function parse($stringRepresentation, $setString, $unsetString){
        if (!$stringRepresentation) {
            throw new InvalidArgumentException();
        }
        $bits = array();
        $bitsPos = 0;
        $rowStartPos = 0;
        $rowLength = -1;
        $nRows = 0;
        $pos = 0;
        while ($pos < strlen($stringRepresentation)) {
            if ($stringRepresentation{$pos} == '\n' ||
                $stringRepresentation->{$pos} == '\r') {
                if ($bitsPos > $rowStartPos) {
                    if($rowLength == -1) {
                        $rowLength = $bitsPos - $rowStartPos;
                    }
                    else if ($bitsPos - $rowStartPos != $rowLength) {
                        throw new InvalidArgumentException("row lengths do not match");
                    }
                    $rowStartPos = $bitsPos;
                    $nRows++;
                }
                $pos++;
            }
            else if (substr($stringRepresentation,$pos, strlen($setString))==$setString) {
                $pos += strlen($setString);
                $bits[$bitsPos] = true;
                $bitsPos++;
            }
            else if (substr($stringRepresentation, $pos + strlen($unsetString))==$unsetString) {
                $pos += strlen($unsetString);
                $bits[$bitsPos] = false;
                $bitsPos++;
            } else {
                throw new InvalidArgumentException(
                    "illegal character encountered: " . substr($stringRepresentation,$pos));
            }
        }

        // no EOL at end?
        if ($bitsPos > $rowStartPos) {
            if($rowLength == -1) {
                $rowLength = $bitsPos - $rowStartPos;
            } else if ($bitsPos - $rowStartPos != $rowLength) {
                throw new InvalidArgumentException("row lengths do not match");
            }
            $nRows++;
        }

        $matrix = new BitMatrix($rowLength, $nRows);
        for ($i = 0; $i < $bitsPos; $i++) {
            if ($bits[$i]) {
                $matrix->set($i % $rowLength, $i / $rowLength);
            }
        }
        return $matrix;
    }

    /**
     * <p>Gets the requested bit, where true means black.</p>
     *
     * @param $x;  The horizontal component (i.e. which column)
     * @param $y;  The vertical component (i.e. which row)
     * @return value of given bit in matrix
     */
    public function get($x, $y) {

         $offset = intval($y * $this->rowSize + ($x / 32));
        if(!isset($this->bits[$offset])){
            $this->bits[$offset] = 0;
        }

       // return (($this->bits[$offset] >> ($x & 0x1f)) & 1) != 0;
        return (uRShift($this->bits[$offset],($x & 0x1f)) & 1) != 0;//было >>> вместо >>, не знаю как эмулировать беззнаковый сдвиг
    }

    /**
     * <p>Sets the given bit to true.</p>
     *
     * @param $x;  The horizontal component (i.e. which column)
     * @param $y;   The vertical component (i.e. which row)
     */
    public function set($x, $y) {
        $offset = intval($y * $this->rowSize + ($x / 32));
            if(!isset($this->bits[$offset])){
                $this->bits[$offset] = 0;
            }
        //$this->bits[$offset] = $this->bits[$offset];

      //  if($this->bits[$offset]>200748364){
            //$this->bits= array(0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-16777216,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-1090519040,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,1056964608,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,-1358954496,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,117440512,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,50331648,-1,-1,-1,-1,65535,0,0,0,0,0,0,0,33554432,-1,-1,536870911,-4096,65279,0,0,0,0,0,0,0,0,-1,-1,65535,-4096,65535,0,0,0,0,0,0,0,0,-193,536870911,0,-4096,65279,0,0,0,0,0,0,0,0,-254,32767,0,-4096,61951,0,0,0,0,0,0,0,0,20913920,0,0,-4096,50175,0,0,0,0,0,0,0,0,0,0,0,-4096,60159,0,0,0,0,0,0,0,0,0,0,0,-4096,64255,0,0,0,0,0,0,0,0,0,0,0,-8192,56319,0,0,0,0,0,0,0,0,0,0,0,-4096,16777215,0,0,0,0,0,0,0,0,0,0,0,-4096,16777215,0,0,0,0,0,0,0,0,0,0,0,-4096,16777215,0,0,0,0,0,0,0,0,0,0,0,-4096,16777215,0,0,0,0,0,0,0,0,0,0,0,-4096,16777215,0,0,0,0,0,0,0,0,0,0,0,-4096,16777215,0,0,0,0,0,0,0,0,0,0,0,-4096,16777215,0,0,0,0,0,0,0,0,0,0,0,-4096,16777215,0,0,0,0,0,0,0,251658240,0,0,0,-4096,-1,255,0,256,0,0,0,0,117440512,0,0,0,-4096,-1,255,0,512,0,0,0,0,117440512,0,0,0,-4096,-1,255,0,1024,0,0,0,0,117440512,0,0,0,-4096,-1,223,0,256,0,0,0,0,117440512,0,0,33030144,-4096,-1,191,0,256,0,0,0,0,117440512,0,0,33554428,-4096,-1,255,0,768,0,0,0,0,117440512,0,402849792,67108862,-8192,-1,255,0,768,0,0,0,0,117440512,0,470278396,63045630,-8192,-1,255,0,256,0,0,0,0,251658240,-8388608,470278399,58720286,-8192,-1,2686975,0,3842,0,0,0,0,251658240,-131072,1007149567,58720286,-8192,-1,2031615,0,3879,0,0,0,0,251658240,536739840,1007092192,58720286,-8192,-1,851967,0,3840,0,0,0,0,251658240,917504,1007092192,58720284,-8192,-1,2031615,0,3968,0,0,0,0,251658240,917504,1007092160,59244060,-8192,-1,65535,0,7936,0,0,0,0,251658240,917504,1009779136,59244060,-8192,-1,9371647,0,1792,0,0,0,0,251658240,917504,946921920,59244060,-8192,-1,8585215,0,1792,0,0,0,0,117440512,-15859712,477159875,59244060,-8192,-1,65535,0,12032,0,0,0,0,251658240,-15859712,52490691,59244060,-8192,-1,-1,0,65408,0,0,0,0,251658240,-15859712,58778051,59244060,-8192,-1,-1,0,65473,0,0,0,0,251658240,-15859712,125886915,59244060,-8192,-1,-1,0,65472,0,0,0,0,251658240,-15859712,58778051,59244060,-8192,-1,-1,0,65408,0,0,0,0,251658240,-15859712,8380867,59244060,-8192,-1,-1,0,65473,0,0,0,0,251658240,-15859712,8380867,59244060,-8192,-1,-1,0,131011,0,0,0,0,251658240,-15859712,8380867,58720284,-8192,-1,-1,0,130947,0,0,0,0,251658240,-15859712,2089411,58720284,-8192,-1,-1,0,130947,0,0,0,0,251658240,-32636928,449,58720284,-8192,-1,-1,33554431,131015,0,0,0,0,251658240,786432,448,62914588,-8192,-1,-1,16777215,131015,0,0,0,0,251658240,786432,448,67108860,-8192,-1,-1,553648127,131015,0,0,0,0,251658240,786432,946864576,67108860,-8192,-1,-1,32505855,131015,0,0,0,0,251658240,786432,946921976,8388604,-8192,-1,-1,8191999,131015,0,0,0,0,251658240,-262144,946921983,248,-8192,-1,-1,8126463,196551,0,0,0,0,251658240,-262144,7397887,0,-8192,-1,-1,16777215,262087,0,0,0,0,251658240,-262144,8257543,0,-8192,-1,-1,-2121269249,262095,0,0,0,0,520093696,0,8257536,0,-8192,-1,-1,-201326593,262095,0,0,0,0,520290304,0,8257536,117963776,-8192,-1,-1,-201326593,262095,0,0,0,0,520093696,0,-2140143616,118488579,-8192,-1,-1,-201326593,131023,0,0,0,0,520093696,0,-2131697280,118488579,-8192,-1,-1,-503316481,131023,0,0,0,0,520093696,2145386496,-2131631232,118484995,-16384,-1,-1,-469762049,262095,0,0,0,0,520093696,2147221504,552649600,118481344,-16384,-1,-1,-469762049,131023,0,0,0,0,520290304,2147221504,2029002240,118481344,-16384,-1,-1,-469762049,262031,0,0,0,0,520290304,-266600448,2029001791,125952960,-16384,-1,-1,-469762049,262031,0,0,0,0,1057423360,-266600448,2027953215,133177312,-16384,-1,-1,-134217729,262111,0,0,0,0,1058471936,-266600448,-119531393,133177343,-16384,-1,-1,-134217729,262111,0,0,0,0,1058471936,-2145648640,-253754369,66068479,-16384,-1,-1,-134217729,262111,0,0,0,0,1058471936,236716032,-253754369,15729663,-16384,-1,-1,-134217729,262095,0,0,0,0,1057947648,236716032,-253754369,6348807,-16384,-1,-1,-134217729,262095,0,0,0,0,524222464,236716032,-253690305,6348803,-16384,-1,-1,-134217729,262111,0,0,0,0,521076736,2115764224,-253625344,14737411,-16384,-1,-1,-134217729,262095,0,0,0,0,522125312,2115764224,-253625344,14743555,-16384,-1,-1,-134217729,262111,0,0,16772608,0,1073676288,-31719424,-2014283776,14810115,-16384,-1,-1,-1,262143,0,0,16776704,0,1065287680,-1642594304,-1879178880,14810115,-16384,-1,-1,-1,524287,0,0,16776192,0,2139029504,264241152,-2013396089,14809091,-16384,-1,-1,-1,262095,0,0,16776192,0,2139029504,264241152,-2080636025,14803335,-16384,-1,-1,-1,262087,0,0,16776192,0,2147418112,264241152,-2132803581,14803847,-16384,-1,-1,-402653185,524259,0,0,8386048,0,2147418112,0,-2132688896,123783,-16384,-1,-1,1207959551,262112,0,0,16775168,0,2147418112,0,14794752,1046535,-16384,-1,-1,268435455,262128,0,0,16775168,0,2147418112,0,14712832,1047615,-16384,-1,-1,536870911,524284,0,0,16776705,0,2147418112,0,14680832,1047615,-16384,-1,-1,-1,524287,0,0,16776704,0,2147418112,-1048576,14681087,1046591,-32768,-1,-1,-1,524287,0,0,16776704,0,2147418112,-524288,-2132802561,2080831,-32768,-1,-1,-1,524287,0,0,16776705,0,2147418112,-524288,-31718401,2080831,-32768,-1,-1,-1,1048575,0,0,16776193,0,2147418112,3670016,-31718528,2080831,-32768,-1,-1,-1,524287,0,0,16776195,0,2147418112,3670016,-31718528,134086719,-32768,-1,-1,-1,524287,0,0,16776195,0,2147418112,3670016,253494144,268173368,-32768,-1,-1,-1,524287,0,0,16775171,0,2147418112,3670016,268174208,268173368,-32768,-1,-1,-1,1048575,0,0,16771072,0,2147418112,-63438848,268174223,31457328,-32768,-1,-1,-1,1048575,0,0,10418176,0,-65536,-63438848,133957519,14807040,-32768,-1,-1,-1,2097151,0,0,15923200,0,2147418112,-63438848,1968015,14809095,-32768,-1,-1,-1,1048575,0,0,12808192,0,2147418112,-63438848,2082703,12711943,-32768,-1,-1,-1,2097151,0,0,6420480,0,2147418112,-63438848,2082703,14343,-32768,-1,-1,-1,2097151,0,0,15202304,0,-65536,-63438848,2082703,1849351,-32768,-1,-1,-1,2097151,0,0,15464448,0,-65536,-63438848,264472335,1849351,-32768,-1,-1,-1,4194303,0,0,16371712,0,-65536,-63438848,264472335,14343,-32768,-1,-1,-1,8388607,0,0,0,0,-65536,-63438848,532907791,235010048,-32768,-1,-1,-1,16777215,0,0,0,0,-65536,-63438848,-1603833,235010160,-32768,-1,-1,-1,16777215,0,0,0,0,-65536,3670016,-30976,67238000,-32768,-1,-1,-1,16777215,0,0,0,0,-65536,3670016,-30976,48,-32768,-1,-1,-1,16777215,0,0,0,0,-65536,3670016,-29391104,768,-32768,-1,-1,-1,16777215,0,0,0,0,-65536,3670016,-29391104,768,-32768,-1,-1,-1,16777215,0,0,0,0,-65536,-524287,-65042433,768,-32768,-1,-1,-1,16777215,0,0,0,0,-65536,-524287,2082441215,0,-65536,-1,-1,-1,16777215,0,0,0,0,-13697024,-524287,511,0,-65536,-1,-1,-1,16777215,0,0,0,0,-12648448,1,0,0,-65536,-1,-1,-1,14680063,0,0,0,0,-12648448,1,0,0,-65536,-1,-1,-1,16777215,0,0,0,0,-65536,1,0,0,-65536,-1,-1,-1,14680063,0,0,0,0,-8454144,1,0,0,-65536,-1,-1,-1,12582911,0,0,0,0,-12648448,1,0,0,-65536,-1,-1,-1,2097151,0,0,0,0,-12648448,1,0,0,-65536,-1,-1,-1,1048575,0,0,0,0,-14745600,1,0,0,-65536,-1,-1,-1,3145727,0,0,0,0,1056964608,1,0,0,-65536,-1,-1,-1,1048575,0,0,0,0,1056964608,1,0,0,-65536,-1,-1,-1,1048575,0,0,0,0,1056964608,1,0,0,-65536,-1,-1,-1,524287,0,0,0,0,2130706432,1,0,0,-65536,-1,-1,-1,1048575,0,0,0,0,1056964608,1,0,0,-65536,-1,-1,-1,524287,0,0,0,0,2130706432,1,0,0,-65536,-1,-1,-1,524287,0,0,0,0,2130706432,1,0,0,-65536,-1,-1,-1,1048575,0,0,0,0,2130706432,1,0,0,-65536,-1,-1,-1,1048575,0,0,0,0,50331648,1,0,0,-65536,-1,-1,-1,1048575,0,0,0,0,117440512,1,0,-268435456,-1,-1,-1,-1,524287,0,0,0,0,251658240,1,0,-320,-1,-1,-1,-1,262143,0,0,0,0,520093696,1,-2048,-1,-1,-1,-1,-1,262143,0,0,0,0,1056964608,-16777213,-1,-1,-1,-1,-1,-1,131071,0,0,0,0,-16777216,-121,-1,-1,-1,-1,-1,-1,131071,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,131071,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,131071,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,131071,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,65535,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,65535,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,131071,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,262143,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,524287,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,524287,0,0,0,0,-16777216,-1,-1,-1,-1,-1,-1,-1,589823,0,0,0,0,0,-1,-1,-1,-1,-1,-1,-1,8179,0,0,0,0,50331648,-1,-1,-1,-1,-1,-1,-1,4080,0,0,0,0,117440512,-1,-1,-1,-1,-1,-1,-1,1016,0,0,0,0,251658240,-1,-1,-1,-1,-1,-1,1073741823,1020,0,0,0,0,50331648,-1,-1,-1,-1,-1,-1,536870911,254,0,0,0,0,50331648,-1,-1,-1,-1,-1,-1,536870911,255,0,0,0,0,50331648,-1,-1,-1,-1,-1,-1,-1879048193,127,0,0,0,0,50331648,-1,-1,-1,-1,-1,-1,-469762049,63,0,0,0,0,1191182336,-1,-1,-1,-1,1023999,0,-520093712,15,0,0,0,0,-218103808,-1,-1,-1,-1,0,-8454144,-260046849,7,0,0,0,0,0,-193,-1,-1,-1057947649,-2147483648,-1,-58720257,1,0,0,0,0,0,-251,-1,-1,-1057423361,-2074,-1,-1,0,0,0,0,0,0,-59648,-1,-1,-1,-1,-1,1073741823,0,0,0,0,0,0,-65536,-1,-1,-1,-1,-1,268435455,0,0,0,0,0,0,-65536,-1,-1,-1,-1,-1,67108863,0,0,0,0,0,0,-65536,-1,-1,-1,-1,-1,8388607,0,0,0,0,0,0,0,-403603456,-1,-1,-1,-1,262143,0,0,0,8388656,0,0,0,-1891434496,-1,-1,-1,-1,16383,0,0,0,8388608,0,0,0,-1612513280,-1,-1,-1,-1,63,0,0,0,0,0,0,0,-24320,-1,-1,-1,8388607,0,0,0,0,0,0,0,0,-256,-1,-1,1073741823,1,0,0,0,0,0,0,0,1610612736,-15,-1,-1,16383,0,0,0,0,0,0,0,0,-16646144,-1,-1,251658239,0,0,0,0,0,0,0,0,0,-51200,-1,-1,40959,0,0,0,0,0,0,268419584,103809024,-12713984,-1,-2147483137,4194303,0,0,0,0,0,0,0,402620416,-2144010240,-13631487,-32513,3,20480,0,0,0,0,0,0,0,419299328,0,-262144,-1,0,0,0,0,0,0,0,0,0,0,0,-5832704,268049407,0,0,0,0,0,0,0,0,0,0,0,0,33030144,0,0,0,0,0,0,0,0,0,0,0,0,3670016,0,0,0,0,0,0,0,0,0,0,0,0,1572864,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,458752,0,0,0,0,0,0,0,0,0,0,0,0,229376,0,0,0,0,0,0,0,0,0,0,0,0,32768,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,8192,0,0,0,0,0,0,0,0,0,0,0,0,8192,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,31744,0,0,0,0,0,0,0,0,0,0,0,0,31744,0,0,0,0,0,0,0,0,0,0,0,0,64512,0,0,0,0,0,0,0,0,0,0,0,0,15872,0,0,0,0,0,0,0,0,0,0,0,0,3584,0,0,0,0,0,0,0,0,0,0,0,0,7680,0,0,0,0,0,0,0,0,0,0,0,0,512,0,0,0,0,0,0,0,0,0,0,0,0,3968,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,3840,0,0,0,0,0,0,0,0,0,0,0,0,1855,0,0,0,0,0,0,0,0,0,0,0,0,63,0,0,0,0,0,0,0,0,0,0,0,0,15,0,0,0,0,0,0,0,0,0,0,0,0,3,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,134217728,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,32,0,0,0,0,0,0,0,0,0,0,0,0,124,0,0,0,0,0,0,0,0,0,0,0,-260046848,63,0,0,0,0,0,0,0,0,0,0,0,-17301504,127,0,0,0,0,0,0,0,0,0,0,0,-524288,127,0,0,0,0,0,0,0,0,0,0,0,-262144,127,0,0,0,0,0,0,0,0,0,0,0,-262144,63,0,0,0,0,0,0,0,0,0,0,0,-262144,63,0,0,0,0,0,0,0,0,0,0,0,-262144,63,0,0,0,0,0,0,0,0,0,0,0,-262144,31,0,0,0,0,0,0,0,0,0,0,0,-262144,63,0,0,0,0,3,0,0,0,0,0,0,-262144,63,0,0,0,0,7,0,0,0,0,0,0,-262144,63,0,0,0,0,63,0,0,0,0,0,0,-262144,63,0,0,0,0,511,0,0,0,0,0,0,-524288,31,0,0,0,0,8191,0,0,0,0,0,0,-1048576,63,0,0,0,0,131071,0,0,0,0,0,0,-524288,63,0,0,0,0,262143,0,0,0,0,0,0,-524288,63,0,0,0,0,131071,0,0,0,0,0,0,-1048576,63,0,0,0,0,262143,0,0,0,0,0,0,-1048576,63,0,0,0,0,262143,0,0,0,0,0,0,-1048576,63,0,0,0,0,262143,0,0,0,0,0,0,-1048576,63,0,0,0,0,262143,0,0,0,0,0,0,-2097152,127,0,0,0,0,262143,0,0,0,0,0,0,-2097152,127,0,0,0,0,262143,0,0,0,0,0,0,-1048576,127,0,0,0,0,262143,0,0,0,0,0,0,-1048576,127,0,0,0,0,262143,0,0,0,0,0,0,-2097152,255,0,0,0,0,262143,0,0,0,0,0,0,-2097152,255,0,0,0,0,262142,0,0,0,0,0,0,-2097152,255,0,0,0,0,262142,0,0,0,0,0,0,-2097152,255,0,0,0,0,262142,0,0,0,0,0,0,-2097152,255,0,0,0,0,262140,0,0,0,0,0,0,-2097152,255,0,0,0,0,131068,0,0,0,0,0,0,-4194304,255,0,0,0,0,131068,0,0,0,0,0,0,-4194304,255,0,0,0,0,65528,0,0,0,0,0,0,-8388608,255,0,0,0,0,65528,0,0,0,0,0,0,-8388608,255,0,0,0,0,65528,0,0,0,0,0,0,-8388608,255,0,0,0,0,32760,0,0,0,0,0,0,-8388608,255,0,0,0,0,32760,0,0,0,0,0,0,-16777216,255,0,0,-2147483648,255,16368,0,0,0,0,0,0,-16777216,255,0,0,-536870912,1023,16368,0,0,0,0,0,0,-33554432,255,0,0,-16777216,4095,16352,0,0,0,0,0,0,-33554432,255,0,0,-8388608,262143,16352,0,0,0,0,0,0,-33554432,255,0,0,-1048576,2097151,16352,0,0,0,0,0,0,-67108864,255,0,0,-524288,8388607,16352,0,0,0,0,0,0,-67108864,255,0,0,-262144,16777215,16320,0,0,0,0,0,0,-67108864,255,0,0,-131072,16777215,100679648,0,0,0,0,0,0,-67108864,255,0,0,-16384,16776959,125861824,0,0,0,0,0,0,-134217728,255,0,0,-4096,16773121,62930880,0,0,0,0,0,0,-134217728,127,0,0,2147482624,16252928,32704,0,0,0,0,0,0,-134217728,127,0,0,268435200,14680064,16320,0,0,0,0,0,0,-134217728,127,0,0,134217600,0,32704,0,0,0,0,0,0,-33554432,127,0,1056964608,67108736,0,32704,0,0,0,0,0,0,-33554432,127,0,2130706432,33554368,0,65408,0,0,0,0,0,0,-33554432,127,0,-16777216,8388576,0,32640,0,0,0,0,0,0,-134217728,127,0,-16777216,2097136,0,32640,0,0,0,0,0,0,-134217728,63,0,-16776960,1048573,0,32640,0,0,0,0,0,0,-536870912,63,0,-16776448,1048575,0,32640,0,0,0,0,0,0,-536870912,63,0,-33553664,6291455,66752,32640,0,0,0,0,0,0,-536870912,63,0,2013266688,2097148,229376,32640,0,0,0,0,0,0,-536870912,63,0,256,4194300,229376,32640,0,0,0,0,0,0,-536870912,63,0,0,524280,196608,32512,0,0,8,0,0,0,-1073741824,63,0,0,-200,15,65280,0,0,24,0,0,0,-1073741824,63,0,0,-1867768,127,32512,0,0,56,0,0,0,-1073741824,63,0,0,-1056768,4095,32512,0,0,124,0,0,0,-1073741824,63,0,0,-1050624,8191,32512,0,0,508,0,0,0,-2147483648,31,0,0,-7866368,8191,32512,0,0,1020,0,0,0,-2147483648,31,0,0,-33030656,8095,32512,0,0,2046,0,0,0,-2147483648,63,0,0,-66586624,771,32512,0,0,4094,0,0,0,0,63,0,0,-134184960,1,32256,0,0,8190,0,0,0,0,63,0,0,1610612736,0,32256,0,0,16382,0,0,0,-2147483648,63,0,0,0,0,15872,0,0,32767,0,0,0,-2147483648,31,0,0,0,0,15872,0,-2147483648,65535,0,0,0,-2147483648,31,0,0,0,0,7680,0,0,65535,0,0,0,-2147483648,31,0,0,134217728,0,7680,0,-2147483648,65535,0,0,0,-2147483648,31,0,0,0,0,7680,0,-2147483648,65535,0,0,0,-2147483648,31,0,0,0,0,7680,0,-1073741824,65535,0,0,0,-2147483648,31,0,0,0,0,3072,0,-1073741824,65535,0,0,0,-2147483648,31,0,0,0,0,3072,0,-1073741824,65535,0,0,0,-2147483648,31,0,0,0,0,0,0,-2147483648,65535,0,0,0,-2147483648,31,0,0,0,0,0,0,-1073741824,65535,0,0,0,0,31,0,0,0,0,0,0,-1073741824,65535,0,0,0,0,31,0,0,0,0,0,0,-2147483648,65535,0,0,0,0,30,0,0,0,0,0,0,-1073741824,65535,0,0,0,0,30,0,0,0,0,0,0,-2147483648,65535,0,0,0,0,30,0,0,0,0,0,0,0,65535,0,0,0,0,28,0,0,0,0,0,0,0,65535,0,0,0,0,28,0,0,0,0,0,0,0,65535,0,0,0,0,28,0,0,0,0,0,0,0,65535,0,0,0,0,24,0,0,0,0,0,0,-2147483648,65535,0,0,0,0,0,0,0,0,0,0,0,-536870912,65535);//[$offset] |= intval32bits(1 << ($x & 0x1f));
        $bob = $this->bits[$offset];
        $bob |= 1 << ($x & 0x1f);
           $this->bits[$offset] |= overflow32($bob);
            //$this->bits[$offset] = intval32bits($this->bits[$offset]);

        //}
//16777216
    }

    public function _unset($x, $y) {//было unset, php не позволяет использовать unset
        $offset = intval($y * $this->rowSize + ($x / 32));
        $this->bits[$offset] &= ~(1 << ($x & 0x1f));
    }


    /**1 << (249 & 0x1f)
     * <p>Flips the given bit.</p>
     *
     * @param $x;  The horizontal component (i.e. which column)
     * @param $y;  The vertical component (i.e. which row)
     */
    public function flip($x, $y) {
        $offset = $y * $this->rowSize + intval($x / 32);

        $this->bits[$offset] = overflow32($this->bits[$offset]^(1 << ($x & 0x1f)));
    }

    /**
     * Exclusive-or (XOR): Flip the bit in this {@code BitMatrix} if the corresponding
     * mask bit is set.
     *
     * @param $mask;  XOR mask
     */
    public function _xor($mask) {//было xor, php не позволяет использовать xor
        if ($this->width != $mask->getWidth() || $this->height != $mask->getHeight()
            || $this->rowSize != $mask->getRowSize()) {
            throw new InvalidArgumentException("input matrix dimensions do not match");
        }
        $rowArray = new BitArray($this->width / 32 + 1);
        for ($y = 0; $y < $this->height; $y++) {
            $offset = $y * $this->rowSize;
            $row = $mask->getRow($y, $rowArray)->getBitArray();
            for ($x = 0; $x < $this->rowSize; $x++) {
                $this->bits[$offset + $x] ^= $row[$x];
            }
        }
    }

    /**
     * Clears all bits (sets to false).
     */
    public function clear() {
        $max = count($this->bits);
        for ($i = 0; $i < $max; $i++) {
            $this->bits[$i] = 0;
        }
    }

    /**
     * <p>Sets a square region of the bit matrix to true.</p>
     *
     * @param $left;  The horizontal position to begin at (inclusive)
     * @param $top;  The vertical position to begin at (inclusive)
     * @param $width;  The width of the region
     * @param $height;  The height of the region
     */
    public function setRegion($left, $top, $width, $height) {
        if ($top < 0 || $left < 0) {
            throw new InvalidArgumentException("Left and top must be nonnegative");
        }
        if ($height < 1 || $width < 1) {
            throw new InvalidArgumentException("Height and width must be at least 1");
        }
        $right = $left + $width;
        $bottom = $top + $height;
        if ($bottom > $this->height || $right > $this->width) { //> this.height || right > this.width
            throw new InvalidArgumentException("The region must fit inside the matrix");
        }
        for ($y = $top; $y < $bottom; $y++) {
            $offset = $y * $this->rowSize;
            for ($x = $left; $x < $right; $x++) {
                $this->bits[$offset + intval($x / 32)] =  overflow32($this->bits[$offset + intval($x / 32)]|= 1 << ($x & 0x1f));
            }
        }
    }

    /**
     * A fast method to retrieve one row of data from the matrix as a BitArray.
     *
     * @param $y;  The row to retrieve
     * @param $row;  An optional caller-allocated BitArray, will be allocated if null or too small
     * @return The resulting BitArray - this reference should always be used even when passing
     *         your own row
     */
    public function getRow($y, $row) {
        if ($row == null || $row->getSize() < $this->width) {
            $row = new BitArray($this->width);
        } else {
            $row->clear();
        }
        $offset = $y * $this->rowSize;
        for ($x = 0; $x < $this->rowSize; $x++) {
            $row->setBulk($x * 32, $this->bits[$offset + $x]);
        }
        return $row;
    }

    /**
     * @param $y;  row to set
     * @param $row;  {@link BitArray} to copy from
     */
    public function setRow($y, $row) {
        $this->bits = arraycopy($row->getBitArray(), 0, $this->bits, $y * $this->rowSize, $this->rowSize);
    }

    /**
     * Modifies this {@code BitMatrix} to represent the same but rotated 180 degrees
     */
    public function rotate180() {
        $width = $this->getWidth();
        $height = $this-getHeight();
        $topRow = new BitArray($width);
        $bottomRow = new BitArray($width);
        for ($i = 0; $i < ($height+1) / 2; $i++) {
            $topRow = $this->getRow($i, $topRow);
            $bottomRow = $this->getRow($height - 1 - $i, $bottomRow);
            $topRow->reverse();
            $bottomRow->reverse();
            $this->setRow($i, $bottomRow);
            $this->setRow($height - 1 - $i, $topRow);
        }
    }

    /**
     * This is useful in detecting the enclosing rectangle of a 'pure' barcode.
     *
     * @return {@code left,top,width,height} enclosing rectangle of all 1 bits, or null if it is all white
     */
    public function getEnclosingRectangle() {
        $left = $this->width;
        $top = $this->height;
        $right = -1;
        $bottom = -1;

        for ($y = 0; $y < $this->height; $y++) {
            for ($x32 = 0; $x32 < $this->rowSize; $x32++) {
                $theBits = $this->bits[$y * $this->rowSize + $x32];
                if ($theBits != 0) {
                    if ($y < $top) {
                        $top = $y;
                    }
                    if ($y > $bottom) {
                        $bottom = $y;
                    }
                    if ($x32 * 32 < $left) {
                        $bit = 0;
                        while (($theBits << (31 - $bit)) == 0) {
                            $bit++;
                        }
                        if (($x32 * 32 + $bit) < $left) {
                            $left = $x32 * 32 + $bit;
                        }
                    }
                    if ($x32 * 32 + 31 > $right) {
                        $bit = 31;
                        while ((sdvig3($theBits, $bit)) == 0) {//>>>
                            $bit--;
                        }
                        if (($x32 * 32 + $bit) > $right) {
                            $right = $x32 * 32 + $bit;
                        }
                    }
                }
            }
        }

        $width = $right - $left;
        $height = $bottom - $top;

        if ($width < 0 || $height < 0) {
            return null;
        }

        return array($left, $top, $width, $height);
    }

    /**
     * This is useful in detecting a corner of a 'pure' barcode.
     *
     * @return {@code x,y} coordinate of top-left-most 1 bit, or null if it is all white
     */
    public function getTopLeftOnBit() {
        $bitsOffset = 0;
        while ($bitsOffset < count($this->bits) && $this->bits[$bitsOffset] == 0) {
            $bitsOffset++;
        }
        if ($bitsOffset == count($this->bits)) {
            return null;
        }
        $y = $bitsOffset / $this->rowSize;
        $x = ($bitsOffset % $this->rowSize) * 32;

        $theBits = $this->bits[$bitsOffset];
        $bit = 0;
        while (($theBits << (31-$bit)) == 0) {
            $bit++;
        }
        $x += $bit;
        return array($x, $y);
    }

    public function getBottomRightOnBit() {
        $bitsOffset = count($this->bits) - 1;
        while ($bitsOffset >= 0 && $this->bits[$bitsOffset] == 0) {
            $bitsOffset--;
        }
        if ($bitsOffset < 0) {
            return null;
        }

        $y = $bitsOffset / $this->rowSize;
        $x = ($bitsOffset % $this->rowSize) * 32;

        $theBits = $this->bits[$bitsOffset];
        $bit = 31;
        while ((sdvig3($theBits, $bit)) == 0) {//>>>
            $bit--;
        }
        $x += $bit;

        return array($x, $y);
    }

    /**
     * @return The width of the matrix
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @return The height of the matrix
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @return The row size of the matrix
     */
    public function getRowSize() {
        return $this->rowSize;
    }

    //@Override
    public function equals($o) {
        if (!($o instanceof BitMatrix)) {
            return false;
        }
        $other = $o;
        return $this->width == $other->width && $this->height == $other->height && $this->rowSize == $other->rowSize &&
        $this->bits===$other->bits;
    }

    //@Override
    public function hashCode() {
        $hash = $this->width;
        $hash = 31 * $hash + $this->width;
        $hash = 31 * $hash + $this->height;
        $hash = 31 * $hash + $this->rowSize;
        $hash = 31 * $hash + hashCode($this->bits);
        return $hash;
    }

    //@Override
    public function toString($setString='', $unsetString='',$lineSeparator='') {
        if(!$setString||!$unsetString){
            return (string)"X "."  ";
        }
        if($lineSeparator&&$lineSeparator!=="\n"){
            return $this->toString_($setString, $unsetString, $lineSeparator);
        }
        return (string)($setString. $unsetString. "\n");
    }

    /**
     * @deprecated call {@link #toString(String,String)} only, which uses \n line separator always
     */
    // @Deprecated
    public function toString_($setString, $unsetString, $lineSeparator) {
        //$result = new StringBuilder(height * (width + 1));
        $result = '';
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $result .= ($this->get($x, $y) ? $setString : $unsetString);
            }
            $result .= ($lineSeparator);
        }
        return (string)$result;
    }

//  @Override
    public function _clone() {//clone()
        return new BitMatrix($this->width, $this->height, $this->rowSize, $this->bits);
    }

}