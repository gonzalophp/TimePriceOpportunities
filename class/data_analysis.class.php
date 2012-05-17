<?php
require_once('class/chart/realchart.class.php');

class data_analysis {
    private $_oRealChart;
    
    public function data_analysis(realChart $oRealChart){
        $this->_oRealChart = $oRealChart;
    }
    
    public function strategy1(){
        $aPrices = $this->_oRealChart->getPrices();
        
        foreach($aPrices as $oRealPrice){
//            var_dump($oRealPrice->getClose());
//            var_dump($oRealPrice->getIndicators());
        }
//        var_dump($this->_oRealChart);
    }
}
?>
