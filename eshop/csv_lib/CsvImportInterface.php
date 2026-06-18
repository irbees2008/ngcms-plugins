<?php

interface CsvImportInterface
{

    public function setFile ($file, $length= 1000, $delimiter = ',', $enclosure = '"');

    public function getHeader ();

    public function getRows ();

}