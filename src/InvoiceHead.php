<?php

require_once("Import.php");

class InvoiceHead {

    private static $invoiceHead = [];

    protected $dataBase;

    function __construct($dataBase) {
        $this->dataBase = $dataBase;
    }

    public function import(DomNode $domDocument) {
        $this->getRecursiveDom($domDocument->firstChild);

        if ($this->initInvoiceHeadPropertyDatabase() == false) {
            echo mysqli_error($this->dataBase);
        }
        if($this->setDataToInvoiceHeadDatabase($domDocument->getElementsByTagName("invoiceNumber")->item(0)->nodeValue) == false) {
            echo mysqli_error($this->dataBase);
        }
    }

    protected function setDataToInvoiceHeadDatabase($invoiceNumber) {
        $columns = "(invoiceNumber";
        $values = "('" .$invoiceNumber."'" ;
        foreach (self::$invoiceHead as $columnName=>$value) {
            $columns.=", " . $columnName;
            $values.=", '" . $value."'";
        }
        $columns .=")";
        $values .=")";
        $sqlQuery = "INSERT INTO InvoiceHeadProperty" . $columns . "VALUES" . $values;
        if(mysqli_query($this->dataBase, $sqlQuery)) {
            return true;
        }
        return false;
    }


    private function initInvoiceHeadPropertyDatabase() {
        $sqlQuery = "CREATE TABLE IF NOT EXISTS InvoiceHeadProperty(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                invoiceNumber VARCHAR(70) NOT NULL UNIQUE,
                taxpayerId VARCHAR (30),
                vatCode VARCHAR (2),
                countyCode VARCHAR (2),
                supplierName VARCHAR (70),
                countryCode VARCHAR (3),
                postalCode VARCHAR (6),
                city VARCHAR (70),
                streetName VARCHAR (70),
                publicPlaceCategory VARCHAR (30),
                number INT (6),
                floor INT (4),
                door INT (4),
                supplierBankAccountNumber VARCHAR (30),
                customerName VARCHAR (60),
                invoiceCategory VARCHAR (12),
                invoiceDeliveryDate VARCHAR (12),
                smallBusinessIndicator VARCHAR (6),
                currencyCode VARCHAR (3),
                exchangeRate VARCHAR (3),
                paymentMethod VARCHAR (12),
                paymentDate VARCHAR (12),
                invoiceAppearance VARCHAR (20) 
                )";
        if(mysqli_query($this->dataBase, $sqlQuery)) {
            return true;
        }
         return false;
    }

    protected function getRecursiveDom(DomNode $node, $level=0){
        $this->checkNode($node);
        if ($node->hasChildNodes()) {
            $children = $node->childNodes;
            foreach($children as $kid) {
                if ($kid->nodeType == XML_ELEMENT_NODE) {
                    $this->getRecursiveDom( $kid, $level++ );
                }
            }
        }
    }

    protected function checkNode(DomNode $node) {
        if ($node->nodeType == XML_ELEMENT_NODE && $node->childNodes->length == 1 && $this->isInvoiceHeadProperty($node)) {
            self::$invoiceHead[$node->tagName] = $node->nodeValue;
        }
    }

    //TODO require more sophisticated solution.
    protected function isInvoiceHeadProperty($node) {
        if (!is_object($node->parentNode)) {
            return false;
        }
        if (property_exists($node->parentNode, 'tagName') && $node->parentNode->tagName == "invoiceHead") {
            return true;
        }
        return property_exists($node->parentNode, 'parentNode') ? $this->isInvoiceHeadProperty($node->parentNode) : false;
    }
}