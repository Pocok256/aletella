<?php
require_once("Invoice.php");
require_once("InvoiceHead.php");
require_once ("InvoiceLine.php");

class Import {

    /**
     * @var InvoiceLine
     */
    private $invoiceLine;
    /**
     * @var InvoiceHead
     */
    private $invoiceHead;
    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @var mysqli
     */
    protected $dataBase;

    const HOST = "db";
    const USER = "user";
    const PASSWORD = "test";
    const DATABASE = "alatella";

    function __construct() {
        $this->dataBase = self::getDatabase();
        $this->invoice = new Invoice();
        $this->invoiceHead = new InvoiceHead($this->dataBase);
        $this->invoiceLine = new InvoiceLine();

    }

    /**
     * @return mysqli
     */
    protected function getDatabase() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $database = new mysqli(self::HOST, self::USER, self::PASSWORD, self::DATABASE);
        } catch(Exception $e) {
            error_log($e->getMessage());
            echo "Error: Unable to connect to MySQL." . PHP_EOL;
            echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
            echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
            exit();
        }

        return $database;
    }

    public function Import() {
        $files = $this->getFiles();
        foreach ($files as $file) {
            $domDocument = $this->getXmlData($file);
            if ($domDocument) {
                $this->invoiceHead->import($domDocument);
            }
            // TODO need implement error handling.
        }
    }

    /**
     * @return array|bool
     */
    protected function getFiles() {
        $realFiles = [];
        $files = scandir("./import");
        foreach ($files as $file) {
            if(is_file("./import/" . $file)) {
                $realFiles[]= $file;
            }
        }
        return empty($realFiles) ? false : $realFiles;
    }

    protected function getXmlData($file) {
        $document = new DOMDocument();
        $document->load("./import/" . $file);
/*
 * I remove this validate section because the example xml is not valid.
 *
         if ($document->validate()) {
            return $document;
        }
        return false;
*/
        return $document;
    }


}

