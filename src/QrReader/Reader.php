<?php

namespace QrHelper\QrReader;

interface Reader {

    public function decode($image);


    public  function reset();


}