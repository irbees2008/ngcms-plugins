<?php

interface CsvExportInterface
{

    public function setHeader ($header);

    /**
     * @param $rows   array();
     * @param $autoHeader when $autoHeader is true , we dont't nedd the method setHeader
     * @return true/false
     */
    public function append ($rows, $autoHeader = false);

    /**
     * @param  $file string timestamp.csv
     */
    public function export ($file = null, $delimiter = ',', $enclosure = '"');
}