<?php

namespace QrHelper\QrReader;

final class FormatException extends ReaderException {

    private static $instance;


    public function __construct($cause=null) {

        if($cause){
            parent::__construct($cause);
        }

    }


    public static function getFormatInstance($cause=null) {
        if(!self::$instance){
            self::$instance = new FormatException();
        }
        if (self::$isStackTrace) {
            return new FormatException($cause);
        } else {
            return self::$instance;
        }
    }
}

