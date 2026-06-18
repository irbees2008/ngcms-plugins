<?php

class CsvImport implements CsvImportInterface
{
/**
 *  $import = new CsvImport();
 *  $import->setFile('read.csv',1000,',');
 *  print_r($import->getRows());
 *  print_r($import->getHeader());
 */
    private $header = array();
    
    private $rows = array();
    
    private $line;
    
    private $init;
    
    private $delimiter ;
    
    private $enclosure ;
    
    private $handle;
    
    /**
     * @param string $file
     * @param string $length
     * @param string $delimiter
     * @param string $enclosure
     */
    public function setFile ($file, $length= 1000, $delimiter = "\t", $enclosure = '"'){
        if ( ! file_exists($file)) {
           return false;
        }
        
        $this->handle = fopen($file, 'r+');
        $this->length = $length;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->line = 0;
    }
    
    public function getHeader (){
        $this->init();
        return $this->header;
    }
    
    /**
     * @return array(array('key'=>'value'));
     */
    public function getRows (){
        $data = array();
        while ($row = $this->getRow()) {
            $data[] = $row;
        }
        return $data;
    }

    private function getRow()
    {
        $this->init();
        if (($row = fgetcsv($this->handle, $this->length, $this->delimiter, $this->enclosure)) !== false) {
            $this->line++;
            return $this->header ? array_combine($this->header, $row) : $row;
        } else {
            return false;
        }
    }

    // the purpose : get header
    private function init()
    {
        if (true === $this->init) {
            return;
        }
        $this->init    = true;
        $this->header = $this->getRow();
    }
    
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}
