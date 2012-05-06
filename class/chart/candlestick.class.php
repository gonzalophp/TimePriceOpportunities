<?php
require_once('class/chart/realprice.class.php');

class candlestick extends realPrice {
    
    private $_iGraphMin;
    private $_iGraphMax;
    private $_iGraphOpen;
    private $_iGraphClose;
    private $_iGraphWidth;
    
    public function getGraphMin(){
        return $this->_iGraphMin;
    }
    
    public function getGraphMax(){
        return $this->_iGraphMax;
    }
    
    public function getGraphOpen(){
        return $this->_iGraphOpen;
    }
    
    public function getGraphClose(){
        return $this->_iGraphClose;
    }
    
    public function setGraphMin($iGraphMin){
        $this->_iGraphMin = $iGraphMin;
    }
    
    public function setGraphMax($iGraphMax){
        $this->_iGraphMax = $iGraphMax;
    }
    
    public function setGraphOpen($iGraphOpen){
        $this->_iGraphOpen = $iGraphOpen;
    }
    
    public function setGraphClose($iGraphClose){
        $this->_iGraphClose = $iGraphClose;
    }
    
    public function setGraphWidth($iGraphWidth){
        $this->_iGraphWidth = $iGraphWidth;
    }
    
    public function getGraphWidth(){
        return $this->_iGraphWidth;
    }
    
    public function getColor(){
        return (($this->getClose()-$this->getOpen())>0) ? array('r'=>0, 'g'=>255, 'b'=>0):array('r'=>255, 'g'=>0, 'b'=>0);
    }
}
?>
