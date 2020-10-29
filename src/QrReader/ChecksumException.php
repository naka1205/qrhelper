<?php
namespace QrHelper\QrReader;

final class ChecksumException extends ReaderException {

  private static $instance;



  public static function  getChecksumInstance($cause=null) {
    if (self::$isStackTrace) {
      return new ChecksumException($cause);
    } else {
        if(!self::$instance){
            self::$instance = new ChecksumException($cause);
        }
      return self::$instance;
    }
  }


}