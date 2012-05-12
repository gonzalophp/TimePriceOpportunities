<?php
require_once('class/chart/realchart.class.php');
require_once('class/chart/imagechart.class.php');
require_once('class/chart/chart.class.php');

class graphicalChart {
    private $_oRealChart;
    private $_oImageChart;
    private $_aChartParameters;
    private $_aGraphicalPrices;
    private $_iPriceWidth;
    private $_oChart;
    
    const FRAME_MARGIN=20;
    
    function graphicalChart($iMaxX, $iMaxY){
        $this->_oChart              = new chart($iMaxX,$iMaxY,self::FRAME_MARGIN);
        $this->_oImageChart         = new imageChart($iMaxX, $iMaxY, self::FRAME_MARGIN);
        $this->_aChartParameters    = array( 'Xinterval_marks'  => array());
        $this->_aGraphicalPrices    = array();
        
    }
    
    public function buildGraphicalChart(realChart $oRealChart){
        $this->_oRealChart = $oRealChart;
        $aRealPrices = $this->_oRealChart->getPrices();
        $this->_iPriceWidth = current($aRealPrices)->getGraphWidth();
        
        $oPlottableSize = $this->_oChart->getPlottableSize();
        $aRealChartParameters = $this->_oRealChart->getChartParameters($oPlottableSize['x']);

        $this->_oChart->addChartPrices('DAX', $aRealChartParameters['extremes'], $aRealChartParameters['Yinterval_marks'], $aRealChartParameters['Xinterval_marks'], $this->_iPriceWidth);
        $this->_oChart->addChart('rsi', 'RSI 14', array('minY'=>0,'maxY'=>100), array(30,70), array( 0 => array('r'=>0, 'g'=>0, 'b'=>0)));
        $this->_oChart->buildGraphicalChartParameters();
        
        // Prices
        $i=0;
        foreach($this->_oChart->getTimeFrame() as $sDay=>$aTimes){
            foreach($aTimes as $iDateTime){
                $this->_aGraphicalPrices[++$i] = (array_key_exists($iDateTime, $aRealPrices)) ? $aRealPrices[$iDateTime]:NULL;
            }
        }
    }
    
    public function getGraphicalY($nRealY, $sChart){
        return $this->_oChart->getGraphicalY($sChart,$nRealY);
    }
    
    public function dump(){
        $this->_oChart->drawCharts($this->_oImageChart);
        
        $aChartPricesPlottableSize = $this->_oChart->getPlottableSize('prices');
        
        foreach($this->_aGraphicalPrices as $i=>$oRealPrice){
            if (!is_null($oRealPrice)){
                $x = ((self::FRAME_MARGIN+$aChartPricesPlottableSize['x'])-($i*$this->_iPriceWidth));
                $oRealPrice->calculateGraphParameters($this);
                $oRealPrice->calculateGraphIndicators($this);
                $oRealPrice->getIndicators()->drawIndicators($this->_oImageChart, $x);
                $oRealPrice->drawPrice($this->_oImageChart, $x);
            }
        }
        $this->_oImageChart->dumpImage();
    }
}
?>
