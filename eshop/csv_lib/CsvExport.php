<?php

class CsvExport implements CsvExportInterface
{
/**
 *   $export = new CsvExport();
 *   //$export->setHeader(array('id','username','age'));
 *     
 *   $export->append(
 *       array(
 *           array('id'=>1,'username'=>'Michael','age'=>25),
 *           array('id'=>2,'username'=>'Han','age'=>24)
 *       ),true
 *   );
 *   $export->append(
 *       array(
 *           array('id'=>3,'username'=>'Mike','age'=>25),
 *       )
 *    );
 *   $export->export('user.csv',",");
 */
    private $header = array();

    private $rows = array();

    
    /**
     * Set the header of Csv
     * @param array $header
     */
    public function setHeader ($header)
    {
        $this->header = $header;
    }

    /**
     * Append data to the file of csv
     * @param array $rows             
     * @param true|false $autoHeader  auto get the header of Csv
     */
    public function append ($rows, $autoHeader = false)
    {
        foreach ($rows as $row) {
            $this->rows[] = $row;
        }
        
        if (true === $autoHeader && ! empty($rows[0])) {
           
            $this->header = array_keys($rows[0]);
        }
    }

    /**
     * Export the csv file
     * @param string|null $file 
     * @param string $delimiter 
     * @param string $enclosure 
     */
    public function export ($file = null, $delimiter = ",", $enclosure = '"')
    {
        if (null === $file) {
            $file = time() . '.csv';
        }
        header('Content-type:text/csv');
        header('Content-Disposition: attachment; filename='. basename($file));
        header('Expires: 0');  
        header('Cache-Control: no-cache');
        ob_clean();
        flush();
        $handle = fopen('php://output', 'w');
        array_unshift($this->rows,$this->header);
        
        foreach ($this->rows as $key=>$row) {
           fputcsv($handle, $row, $delimiter, $enclosure);
        }
        fclose($handle);

        //header('Content-Length: '. ob_get_length());
        //ob_clean();
        //flush();
    }
}
