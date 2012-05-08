<?php
require_once('class/chart/realprice.class.php');

class dot  extends realPrice {
    private $_iGraphWidth=1;
    private $_iGraphClose;
    
    public function setGraphWidth($iGraphWidth){
    }
    
    public function calculateGraphParameters($fGetGraphicalY){
        $this->_iGraphClose = call_user_func_array($fGetGraphicalY, array($this->getClose()));
    }
    
    public function drawPrice($oImageChart, $x){
        $oImageChart->drawPoint($x, $this->_iGraphClose);
    }
    
    public function getGraphWidth(){
        return $this->_iGraphWidth;
    }
    
    public function getGraphClose(){
        return $this->_iGraphClose;
    }
    
    public function setGraphClose($iGraphClose){
        $this->_iGraphClose = $iGraphClose;
    }
}
?>
