<?php
require_once('class/chart/realchart.class.php');
require_once('class/chart/imagechart.class.php');

class graphicalChart {
    private $_iMaxX;
    private $_iMaxY;
    private $_oRealChart;
    private $_oImageChart;
    private $_aChartParameters;
    private $_aGraphicalPrices;
    private $_iPlottableSpaceX;
    private $_iPlottableSpaceY;
    private $_iPriceWidth;
    
    const FRAME_MARGIN=20;
    
    function graphicalChart($iMaxX, $iMaxY){
        $this->_iMaxX                   = $iMaxX;
        $this->_iMaxY                   = $iMaxY;
        $this->_iPlottableSpaceX        = $this->_iMaxX-(2*self::FRAME_MARGIN);
        $this->_iPlottableSpaceY        = $this->_iMaxY-(2*self::FRAME_MARGIN);
        $this->_aChartParameters        = array( 'Xinterval_marks'  => array()
                                                ,'Yinterval_marks'  => array()
                                                ,'extremes'         => array());
        $this->_aGraphicalPrices        = array();
    }
    
    public function buildGraphicalChart(realChart $oRealChart){
        $this->_oRealChart  = $oRealChart;
        $aRealPrices = $this->_oRealChart->getPrices();
        $this->_iPriceWidth = current($aRealPrices)->getGraphWidth();
        
        $aRealChartParameters = $this->_oRealChart->getChartParameters($this->_iPlottableSpaceX);
        
        // Generate Equation Parameters
        $this->getGraphicalY(0, $aRealChartParameters);
        $this->_getGraphicalX(0, $aRealChartParameters);
        
        // Frame extremes
        $this->_aChartParameters['extremes']['minX'] = $this->_getGraphicalX($aRealChartParameters['extremes']['minX']);
        $this->_aChartParameters['extremes']['maxX'] = $this->_getGraphicalX($aRealChartParameters['extremes']['maxX']);
        $this->_aChartParameters['extremes']['minY'] = $this->getGraphicalY($aRealChartParameters['extremes']['minY']);
        $this->_aChartParameters['extremes']['maxY'] = $this->getGraphicalY($aRealChartParameters['extremes']['maxY']);
        
        // Y axis
        foreach ($aRealChartParameters['Yinterval_marks'] as $nRealYIntervalMarks){
            $this->_aChartParameters['Yinterval_marks'][$nRealYIntervalMarks] = $this->getGraphicalY($nRealYIntervalMarks);
        }
        
        // Prices
        $i=0;
        foreach($aRealChartParameters['Xinterval_marks'] as $sDay=>$aTimes){
            $this->_aChartParameters['Xinterval_marks'][$sDay] = $i;
            foreach($aTimes as $iDateTime){
                $this->_aGraphicalPrices[++$i] = (array_key_exists($iDateTime, $aRealPrices)) ? $aRealPrices[$iDateTime]:NULL;
            }
        }
    }
    
    public function getGraphicalY($nRealY, $aRealChartParameters=NULL){
        static $equationPart1;
        static $equationPart2;
        
        if (!is_null($aRealChartParameters)){
            $equationPart1 = self::FRAME_MARGIN-1+($this->_iPlottableSpaceY*($aRealChartParameters['extremes']['maxY']/($aRealChartParameters['extremes']['maxY']-$aRealChartParameters['extremes']['minY'])));
            $equationPart2 = $this->_iPlottableSpaceY/($aRealChartParameters['extremes']['maxY']-$aRealChartParameters['extremes']['minY']);
        }
        
        return (int) (string) ($equationPart1-$equationPart2*$nRealY);
    }
    
    private function _getGraphicalX($nRealX, $aRealChartParameters=NULL){
        static $equationPart1;
        static $equationPart2;
        
        if (!is_null($aRealChartParameters)){
            $equationPart1 = ((self::FRAME_MARGIN-1+$this->_iPlottableSpaceX)
                            -($aRealChartParameters['extremes']['maxX']*$this->_iPlottableSpaceX/($aRealChartParameters['extremes']['maxX']-$aRealChartParameters['extremes']['minX'])));
            $equationPart2 = ($this->_iPlottableSpaceX/($aRealChartParameters['extremes']['maxX']-$aRealChartParameters['extremes']['minX']));
        }
        
        return (int) (string) ($equationPart1+$equationPart2*$nRealX);
    }
    
    public function dump(){
        $this->_oImageChart = new imageChart($this->_iMaxX
                                           , $this->_iMaxY
                                           , self::FRAME_MARGIN);
        foreach($this->_aChartParameters['Yinterval_marks'] as $nRealYIntervalMarks=>$iGraphYIntervalMarks){
            $this->_oImageChart->drawOrdinate($iGraphYIntervalMarks,$nRealYIntervalMarks);
        }
        
        foreach($this->_aChartParameters['Xinterval_marks'] as $i){
            $xCenter = (($this->_iMaxX-self::FRAME_MARGIN)-($i*$this->_iPriceWidth));
            $this->_oImageChart->drawAbscissa($xCenter);
        }
        
        foreach($this->_aGraphicalPrices as $i=>$oRealPrice){
            if (!is_null($oRealPrice)){
                $x = (($this->_iMaxX-self::FRAME_MARGIN)-($i*$this->_iPriceWidth));
                $oRealPrice->calculateGraphParameters($this);
                $oRealPrice->calculateGraphIndicators($this);
                $oRealPrice->getIndicators()->drawIndicators($this->_oImageChart, $x);
                
//                var_dump($oRealPrice->getClose(),$oRealPrice->getIndicators()->getData());
                
                $oRealPrice->drawPrice($this->_oImageChart, $x);
            }
        }
//        exit;
        $this->_oImageChart->dumpImage();
    }
}
?>
