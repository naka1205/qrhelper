<?php

namespace QrHelper\QrReader;

final class NotFoundException extends ReaderException {

private static $instance;


public static function getNotFoundInstance() {
    if(!self::$instance ){
        self::$instance =  new NotFoundException();
    }
    return self::$instance;
}

}