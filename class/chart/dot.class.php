<?php
require_once('class/chart/realprice.class.php');

class dot  extends realPrice {
    private $_iGraphWidth=1;
    
    public function setGraphWidth($iGraphWidth){
    }
    
    public function getGraphWidth(){
        return $this->_iGraphWidth;
    }
}
?>
