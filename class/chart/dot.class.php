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
        static $previousGraphPoint=NULL;
        
        $aGraphPoint = array('x'=>$x, 'y'=>$this->_iGraphClose);

        $oImageChart->drawPoint($aGraphPoint['x'], $aGraphPoint['y']);
        
        if (!is_null($previousGraphPoint)){
            $oImageChart->drawLine($previousGraphPoint['x'], $previousGraphPoint['y'], $aGraphPoint['x'], $aGraphPoint['y']);
        }
        
        $previousGraphPoint=$aGraphPoint;
    }
}
?>
