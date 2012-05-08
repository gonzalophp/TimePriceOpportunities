<?php
require_once('class/chart/realprice.class.php');

class dot extends realPrice {
    private $_iGraphClose;
    
    public function setZoom($iZoom){
        $this->setGraphWidth(2*$iZoom);
    }
    
    public function calculateGraphParameters($fGetGraphicalY){
        $this->_iGraphClose = call_user_func_array($fGetGraphicalY, array($this->getClose()));
    }
    
    public function drawPrice($oImageChart, $x){
        $oImageChart->drawPoint($x, $this->_iGraphClose);
    }
}
?>
