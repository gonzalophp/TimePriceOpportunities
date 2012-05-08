<?php
require_once('class/chart/realchart.class.php');
require_once('class/chart/imagechart.class.php');

class graphicalChart {
    private $_iMaxX;
    private $_iMaxY;
    private $_oRealChart;
    private $_oImageChart;
    private $_aChartParameters;
    private $_aRealChartParameters;
    private $_aGraphicalPrices;
    private $_iPlottableSpaceX;
    private $_iPlottableSpaceY;
    
    const FRAME_MARGIN=20;
    
    function graphicalChart($iMaxX, $iMaxY){
        $this->_iMaxX                   = $iMaxX;
        $this->_iMaxY                   = $iMaxY;
        $this->_iPlottableSpaceX        = $this->_iMaxX-(2*self::FRAME_MARGIN);
        $this->_iPlottableSpaceY        = $this->_iMaxY-(2*self::FRAME_MARGIN);
        $this->_aChartParameters        = array( 'Xinterval_marks'  => array()
                                                ,'Yinterval_marks'  => array()
                                                ,'extremes'         => array());
        $this->_aRealChartParameters    = array('Xinterval_marks'   => array()
                                                ,'Yinterval_marks'  => array()
                                                ,'extremes'         => array());
        $this->_aGraphicalPrices        = array();
    }
    
    public function buildGraphicalChart(realChart $oRealChart){
        $this->_oRealChart  = $oRealChart;
        
        $this->_aRealChartParameters = $this->_oRealChart->getChartParameters($this->_iPlottableSpaceX);
        
        // Frame extremes
        $this->_aChartParameters['extremes']['minX'] = $this->_getGraphicalX($this->_aRealChartParameters['extremes']['minX']);
        $this->_aChartParameters['extremes']['maxX'] = $this->_getGraphicalX($this->_aRealChartParameters['extremes']['maxX']);
        $this->_aChartParameters['extremes']['minY'] = $this->getGraphicalY($this->_aRealChartParameters['extremes']['minY']);
        $this->_aChartParameters['extremes']['maxY'] = $this->getGraphicalY($this->_aRealChartParameters['extremes']['maxY']);
        
        // Y axis
        foreach ($this->_aRealChartParameters['Yinterval_marks'] as $nRealYIntervalMarks){
            $this->_aChartParameters['Yinterval_marks'][$nRealYIntervalMarks] = $this->getGraphicalY($nRealYIntervalMarks);
        }
        
        // Prices
        $aRealPrices = $this->_oRealChart->getPrices();
        $i=0;
        foreach($this->_aRealChartParameters['Xinterval_marks'] as $sDay=>$aTimes){
            $this->_aChartParameters['Xinterval_marks'][$sDay] = $i;
            foreach($aTimes as $iDateTime){
                $this->_aGraphicalPrices[++$i] = (array_key_exists($iDateTime, $aRealPrices)) ? $aRealPrices[$iDateTime]:NULL;
            }
        }
//        var_dump($aRealPrices,$this->_aRealChartParameters,$this->_aChartParameters,$this->_aGraphicalPrices);exit;
    }
    
    public function getGraphicalY($nRealY){
        static $equationPart1 = NULL;
        static $equationPart2 = NULL;
        
        if (is_null($equationPart1)){
            $equationPart1 = self::FRAME_MARGIN-1+($this->_iPlottableSpaceY*($this->_aRealChartParameters['extremes']['maxY']/($this->_aRealChartParameters['extremes']['maxY']-$this->_aRealChartParameters['extremes']['minY'])));
            $equationPart2 = $this->_iPlottableSpaceY/($this->_aRealChartParameters['extremes']['maxY']-$this->_aRealChartParameters['extremes']['minY']);
        }
        
        return (int) (string) ($equationPart1-$equationPart2*$nRealY);
    }
    
    private function _getGraphicalX($nRealX){
        static $equationPart1 = NULL;
        static $equationPart2 = NULL;
        
        if (is_null($equationPart1)){
            $equationPart1 = ((self::FRAME_MARGIN-1+$this->_iPlottableSpaceX)
                            -($this->_aRealChartParameters['extremes']['maxX']*$this->_iPlottableSpaceX/($this->_aRealChartParameters['extremes']['maxX']-$this->_aRealChartParameters['extremes']['minX'])));
            $equationPart2 = ($this->_iPlottableSpaceX/($this->_aRealChartParameters['extremes']['maxX']-$this->_aRealChartParameters['extremes']['minX']));
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
            $xCenter = (($this->_iMaxX-self::FRAME_MARGIN)-($i*$this->_oRealChart->getPriceWidth()));
            $this->_oImageChart->drawAbscissa($xCenter);
        }
        
//        var_dump($this->_aGraphicalPrices);exit;
        foreach($this->_aGraphicalPrices as $i=>$oRealPrice){
            if (!is_null($oRealPrice)){
                $x = (($this->_iMaxX-self::FRAME_MARGIN)-($i*$this->_oRealChart->getPriceWidth()));
                $oRealPrice->calculateGraphParameters(array($this, 'getGraphicalY'));
                $oRealPrice->drawPrice($this->_oImageChart, $x);
            }
        }
        
        $this->_oImageChart->dumpImage();
    }
}
?>
