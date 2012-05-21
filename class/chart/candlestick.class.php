<?php
require_once('class/chart/realprice.class.php');

class candlestick extends realPrice {
    private $_iGraphMin;
    private $_iGraphMax;
    private $_iGraphOpen;
    private $_iGraphClose;
    
    public function setZoom($iZoom){
        $this->setGraphWidth(3 + (2*$iZoom));
    }
    
    public function calculateGraphParameters($oGraphicalChart){
        $this->setGraphicalChart($oGraphicalChart);
        
        $this->_iGraphMin = $this->getGraphicalChart()->getGraphicalY('prices',$this->getMin());
        $this->_iGraphMax = $this->getGraphicalChart()->getGraphicalY('prices',$this->getMax());
        $this->_iGraphOpen = $this->getGraphicalChart()->getGraphicalY('prices',$this->getOpen());
        $this->_iGraphClose = $this->getGraphicalChart()->getGraphicalY('prices',$this->getClose());
    }
    
    public function drawPrice($oImageChart, $x){
        
        $oImageChart->drawCandlestick(($x-((int)($this->getGraphWidth()/2)))
                                    , ($x+((int)($this->getGraphWidth()/2)))
                                    , $this->_iGraphMin
                                    , $this->_iGraphMax
                                    , $this->_iGraphOpen
                                    , $this->_iGraphClose);
        $aTrades = $this->getTrade();
        if (!empty($aTrades)){
            foreach($aTrades as $i=>$aTrade){
                switch($aTrade['dir']){
                    case realPrice::TRADE_SELL: 
                        $aColor = array('r'=>200,'g'=>50,'b'=>50);
                    break;
                    case realPrice::TRADE_CLOSE: 
                        $aColor = array('r'=>70,'g'=>70,'b'=>70);
                    break;
                    case realPrice::TRADE_BUY: 
                        $aColor = array('r'=>50,'g'=>210,'b'=>50);
                    break;
                }
                
                $oImageChart->drawBalloon((($x-((int)($this->getGraphWidth()/2)))+(4*$i))
                                        , $this->getGraphicalChart()->getGraphicalY('prices',$aTrade['price'])
                                        , $aColor);
            }
        }
    }
}
?>