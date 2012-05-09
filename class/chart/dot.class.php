<?php
require_once('class/chart/realprice.class.php');

class dot extends realPrice {
    private $_iGraphClose;
    
    public function setZoom($iZoom){
        $this->setGraphWidth(2*$iZoom);
    }
    
    public function calculateGraphParameters($oGraphicalChart){
        $this->_iGraphClose = $oGraphicalChart->getGraphicalY($this->getClose());
    }
    
    public function drawPrice($oImageChart, $x){
        static $previousGraphPoint=NULL;
        
        $aGraphPoint = array('x'=>$x, 'y'=>$this->_iGraphClose);

        $oImageChart->drawPoint($aGraphPoint['x'], $aGraphPoint['y']);
        
        if (!is_null($previousGraphPoint)){
            $oImageChart->drawLine($previousGraphPoint['x'], $previousGraphPoint['y'], $aGraphPoint['x'], $aGraphPoint['y'], array('r'=>0, 'g'=>0, 'b'=>0));
        }
        
        $previousGraphPoint=$aGraphPoint;
    }
}
?>
