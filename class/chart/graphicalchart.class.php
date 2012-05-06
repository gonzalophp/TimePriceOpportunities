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
        $this->_aChartParameters['extremes']['minY'] = $this->_getGraphicalY($this->_aRealChartParameters['extremes']['minY']);
        $this->_aChartParameters['extremes']['maxY'] = $this->_getGraphicalY($this->_aRealChartParameters['extremes']['maxY']);
        
        // Y axis
        foreach ($this->_aRealChartParameters['Yinterval_marks'] as $nRealYIntervalMarks){
            $this->_aChartParameters['Yinterval_marks'][$nRealYIntervalMarks] = $this->_getGraphicalY($nRealYIntervalMarks);
        }
        
        // X Axis
        foreach ($this->_aRealChartParameters['Xinterval_marks'] as $sDays=>$aTimes){
            $iDateTime = strtotime($sDays);
            $this->_aChartParameters['Xinterval_marks'][$iDateTime] = $this->_getGraphicalX($iDateTime);
        }
        
        // Prices
        $aRealPrices = $this->_oRealChart->getPrices();
        foreach($aRealPrices as $iDateTime => $oRealPrice){
            switch($this->_oRealChart->getChartStyle()){
                case realChart::STYLE_CANDLESTICK:
                    $iGraphMin = $this->_getGraphicalY($aRealPrices[$iDateTime]->getMin());
                    $iGraphMax = $this->_getGraphicalY($aRealPrices[$iDateTime]->getMax());
                    $iGraphOpen = $this->_getGraphicalY($aRealPrices[$iDateTime]->getOpen());
                    $iGraphClose = $this->_getGraphicalY($aRealPrices[$iDateTime]->getClose());
                    
                    $aRealPrices[$iDateTime]->setGraphMin($iGraphMin);
                    $aRealPrices[$iDateTime]->setGraphMax($iGraphMax);
                    $aRealPrices[$iDateTime]->setGraphOpen($iGraphOpen);
                    $aRealPrices[$iDateTime]->setGraphClose($iGraphClose);
                    
                    $this->_aGraphicalPrices[$this->_getGraphicalX($iDateTime)] = $aRealPrices[$iDateTime];
                break;
            }
        }
    }
    
    private function _getGraphicalY($nRealY){
        $iDifferenceExtremes = ($this->_aRealChartParameters['extremes']['maxY']-$this->_aRealChartParameters['extremes']['minY']);
        $iDifferencePrice = ($this->_aRealChartParameters['extremes']['maxY']-$nRealY);
        
        return (int) (string) ((self::FRAME_MARGIN-1)+($this->_iPlottableSpaceY*(($iDifferencePrice/$iDifferenceExtremes))));
    }
    
    private function _getGraphicalX($nRealX){
        $iDifferenceExtremes = ($this->_aRealChartParameters['extremes']['maxX']-$this->_aRealChartParameters['extremes']['minX']);
        $iDifferencePrice = ($this->_aRealChartParameters['extremes']['maxX']-$nRealX);

        return (int) (string) ((self::FRAME_MARGIN-1)+($this->_iPlottableSpaceX*(1-($iDifferencePrice/$iDifferenceExtremes))));
    }
    
    public function dump(){
        $this->_oImageChart = new imageChart($this->_iMaxX
                                           , $this->_iMaxY
                                           , self::FRAME_MARGIN);
        foreach($this->_aChartParameters['Yinterval_marks'] as $nRealYIntervalMarks=>$iGraphYIntervalMarks){
            $this->_oImageChart->drawOrdinate($iGraphYIntervalMarks,$nRealYIntervalMarks);
        }
        
        foreach($this->_aChartParameters['Xinterval_marks'] as $iGraphXIntervalMarks){
            $this->_oImageChart->drawAbscissa($iGraphXIntervalMarks);
        }
        
        foreach($this->_aGraphicalPrices as $x=>$oRealPrice){
            
            switch($this->_oRealChart->getChartStyle()){
                case realChart::STYLE_CANDLESTICK:
//                    var_dump(($x-((int)(string)($oRealPrice->getGraphWidth()/2)))
//                                                        , ($x+((int)(string)($oRealPrice->getGraphWidth()/2)))
//                                                        , $oRealPrice->getGraphMin()
//                                                        , $oRealPrice->getGraphMax()
//                                                        , $oRealPrice->getGraphOpen()
//                                                        , $oRealPrice->getGraphClose());exit;
                        
                    $this->_oImageChart->drawCandlestick(($x-((int)(string)($oRealPrice->getGraphWidth()/2)))
                                                        , ($x+((int)(string)($oRealPrice->getGraphWidth()/2)))
                                                        , $oRealPrice->getGraphMin()
                                                        , $oRealPrice->getGraphMax()
                                                        , $oRealPrice->getGraphOpen()
                                                        , $oRealPrice->getGraphClose());
                break;
                case realChart::STYLE_CLOSE:
                    $this->_oImageChart->drawPoint($x,$y);
                break;
            }
        }
        
        $this->_oImageChart->dumpImage();
    }
}
?>
