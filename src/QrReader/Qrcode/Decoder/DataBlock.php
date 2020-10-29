<?php
namespace QrHelper\QrReader\Qrcode\Decoder;
use InvalidArgumentException;
final class DataBlock {

    private $numDataCodewords;
    private $codewords; //byte[]

    private function __construct($numDataCodewords, $codewords) {
        $this->numDataCodewords = $numDataCodewords;
        $this->codewords = $codewords;
    }

    /**
     * <p>When QR Codes use multiple data blocks, they are actually interleaved.
     * That is, the first byte of data block 1 to n is written, then the second bytes, and so on. This
     * method will separate the data into original blocks.</p>
     *
     * @param rawCodewords bytes as read directly from the QR Code
     * @param version version of the QR Code
     * @param ecLevel error-correction level of the QR Code
     * @return DataBlocks containing original bytes, "de-interleaved" from representation in the
     *         QR Code
     */
    static function getDataBlocks($rawCodewords,
                                  $version,
                                  $ecLevel) {

        if (count($rawCodewords) != $version->getTotalCodewords()) {
            throw new InvalidArgumentException();
        }

        // Figure out the number and size of data blocks used by this version and
        // error correction level
        $ecBlocks = $version->getECBlocksForLevel($ecLevel);

        // First count the total number of data blocks
        $totalBlocks = 0;
        $ecBlockArray = $ecBlocks->getECBlocks();
        foreach ($ecBlockArray as $ecBlock) {
            $totalBlocks += $ecBlock->getCount();
        }

        // Now establish DataBlocks of the appropriate size and number of data codewords
        $result = array();//new DataBlock[$totalBlocks];
        $numResultBlocks = 0;
        foreach ($ecBlockArray as $ecBlock) {
            for ($i = 0; $i < $ecBlock->getCount(); $i++) {
                $numDataCodewords = $ecBlock->getDataCodewords();
                $numBlockCodewords = $ecBlocks->getECCodewordsPerBlock() + $numDataCodewords;
                $result[$numResultBlocks++] = new DataBlock($numDataCodewords, fill_array(0,$numBlockCodewords,0));
            }
        }

        // All blocks have the same amount of data, except that the last n
        // (where n may be 0) have 1 more byte. Figure out where these start.
        $shorterBlocksTotalCodewords = count($result[0]->codewords);
        $longerBlocksStartAt = count($result) - 1;
        while ($longerBlocksStartAt >= 0) {
            $numCodewords = count($result[$longerBlocksStartAt]->codewords);
            if ($numCodewords == $shorterBlocksTotalCodewords) {
                break;
            }
            $longerBlocksStartAt--;
        }
        $longerBlocksStartAt++;

        $shorterBlocksNumDataCodewords = $shorterBlocksTotalCodewords - $ecBlocks->getECCodewordsPerBlock();
        // The last elements of result may be 1 element longer;
        // first fill out as many elements as all of them have
        $rawCodewordsOffset = 0;
        for ($i = 0; $i < $shorterBlocksNumDataCodewords; $i++) {
            for ($j = 0; $j < $numResultBlocks; $j++) {
                $result[$j]->codewords[$i] = $rawCodewords[$rawCodewordsOffset++];
            }
        }
        // Fill out the last data block in the longer ones
        for ($j = $longerBlocksStartAt; $j < $numResultBlocks; $j++) {
            $result[$j]->codewords[$shorterBlocksNumDataCodewords] = $rawCodewords[$rawCodewordsOffset++];
        }
        // Now add in error correction blocks
        $max = count($result[0]->codewords);
        for ($i = $shorterBlocksNumDataCodewords; $i < $max; $i++) {
            for ($j = 0; $j < $numResultBlocks; $j++) {
                $iOffset = $j < $longerBlocksStartAt ? $i : $i + 1;
                $result[$j]->codewords[$iOffset] = $rawCodewords[$rawCodewordsOffset++];
            }
        }
        return $result;
    }

    function getNumDataCodewords() {
        return $this->numDataCodewords;
    }

    function getCodewords() {
        return $this->codewords;
    }

}
