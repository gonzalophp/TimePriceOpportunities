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
    private $_aCharts = array();
    
    const FRAME_MARGIN=20;
    
    function graphicalChart($iMaxX, $iMaxY){
        $this->_iMaxX                   = $iMaxX;
        $this->_iMaxY                   = $iMaxY;
        $this->_iPlottableSpaceX        = $this->_iMaxX-(2*self::FRAME_MARGIN);
        $this->_aChartParameters        = array( 'Xinterval_marks'  => array()
                                                ,'Yinterval_marks'  => array()
                                                ,'extremes'         => array());
        $this->_aGraphicalPrices        = array();
    }
    
    public function buildGraphicalChart(realChart $oRealChart){
        $this->_oRealChart = $oRealChart;
        $aRealPrices = $this->_oRealChart->getPrices();
        $this->_iPriceWidth = current($aRealPrices)->getGraphWidth();
        
        $aRealChartParameters = $this->_oRealChart->getChartParameters($this->_iPlottableSpaceX);
        
        $aIndicatorsSettings = $this->_oRealChart->getIndicators();
        $aIndicatorsCharts = array_intersect(array_keys($aIndicatorsSettings), array('rsi','abc'));
        $iGraphYDistribution = 10+2*count($aIndicatorsCharts);
        
        $this->_aCharts['prices'] = array('real_chart_parameters'  => $aRealChartParameters
                                          ,'graphY'                => array('top'         => self::FRAME_MARGIN
                                                                            ,'plottable_space'  => (int)(string)(($this->_iMaxY-(2*self::FRAME_MARGIN))*(10/$iGraphYDistribution))
                                                                            ,'eq_part1'   => NULL
                                                                            ,'eq_part2'   => NULL));
        $nCharts = 0;
        foreach($aIndicatorsCharts as $sIndicator){
            if (array_key_exists('rsi', $aIndicatorsSettings)){
                $this->_aCharts[$sIndicator] = array('real_chart_parameters' => $aRealChartParameters
                                                    ,'graphY'                => array('top'         => (int)(string)(self::FRAME_MARGIN
                                                                                                       +(($this->_iMaxY-(2*self::FRAME_MARGIN))*(10/$iGraphYDistribution))
                                                                                                       +(($nCharts)*(($this->_iMaxY-(2*self::FRAME_MARGIN))*(2/$iGraphYDistribution))))
                                                                                    ,'plottable_space'  => (int)(string)(($this->_iMaxY-(2*self::FRAME_MARGIN))*(2/$iGraphYDistribution))
                                                                                    ,'eq_part1'   => NULL
                                                                                    ,'eq_part2'   => NULL));
                $this->_aCharts[$sIndicator]['real_chart_parameters']['extremes']['minY'] = 0;
                $this->_aCharts[$sIndicator]['real_chart_parameters']['extremes']['maxY'] = 100;
                $this->_aCharts[$sIndicator]['graphY']['mark_top'] = $this->getGraphicalY(70,'rsi');
                $this->_aCharts[$sIndicator]['graphY']['mark_bottom'] = $this->getGraphicalY(30,'rsi');
                $this->_aCharts[$sIndicator]['graphY']['frame_top'] = $this->getGraphicalY(100,'rsi');
                $this->_aCharts[$sIndicator]['graphY']['frame_bottom'] = $this->getGraphicalY(0,'rsi');
                
            }
            $nCharts++;
        }
        
//        var_dump($this->_aCharts);exit;
            
        
//        var_dump($this->_aCharts);
        // Generate Equation Parameters
//        $this->getGraphicalY(0, $aRealChartParameters);
//        $this->_getGraphicalX(0, $aRealChartParameters);
        
        // Frame extremes
        $this->_aChartParameters['extremes']['minX'] = $this->_getGraphicalX($aRealChartParameters['extremes']['minX']);
        $this->_aChartParameters['extremes']['maxX'] = $this->_getGraphicalX($aRealChartParameters['extremes']['maxX']);
        $this->_aChartParameters['extremes']['minY'] = $this->getGraphicalY($aRealChartParameters['extremes']['minY'],'prices');
        $this->_aChartParameters['extremes']['maxY'] = $this->getGraphicalY($aRealChartParameters['extremes']['maxY'],'prices');
        
        // Y axis
        foreach ($aRealChartParameters['Yinterval_marks'] as $nRealYIntervalMarks){
            $this->_aChartParameters['Yinterval_marks'][$nRealYIntervalMarks] = $this->getGraphicalY($nRealYIntervalMarks,'prices');
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
    
    public function getGraphicalY($nRealY, $iChart){
        if (array_key_exists($iChart, $this->_aCharts)){
            if (is_null($this->_aCharts[$iChart]['graphY']['eq_part1'])){
                $this->_aCharts[$iChart]['graphY']['eq_part1'] = $this->_aCharts[$iChart]['graphY']['top']-1+($this->_aCharts[$iChart]['graphY']['plottable_space']*($this->_aCharts[$iChart]['real_chart_parameters']['extremes']['maxY']/($this->_aCharts[$iChart]['real_chart_parameters']['extremes']['maxY']-$this->_aCharts[$iChart]['real_chart_parameters']['extremes']['minY'])));
                $this->_aCharts[$iChart]['graphY']['eq_part2'] = $this->_aCharts[$iChart]['graphY']['plottable_space']/($this->_aCharts[$iChart]['real_chart_parameters']['extremes']['maxY']-$this->_aCharts[$iChart]['real_chart_parameters']['extremes']['minY']);
            }
            return (int) (string) ($this->_aCharts[$iChart]['graphY']['eq_part1']-$this->_aCharts[$iChart]['graphY']['eq_part2']*$nRealY);
        }
        else {
            echo "getGraphicalY error: iChart $iChart not found";
        }
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
        
        $this->_oImageChart->drawFrame(self::FRAME_MARGIN, self::FRAME_MARGIN, $this->_iMaxX-self::FRAME_MARGIN-1, self::FRAME_MARGIN+$this->_aCharts['prices']['graphY']['plottable_space']);
        
        foreach($this->_aChartParameters['Yinterval_marks'] as $nRealYIntervalMarks=>$iGraphYIntervalMarks){
            $this->_oImageChart->drawOrdinate($iGraphYIntervalMarks,$nRealYIntervalMarks);
        }
        
        $bFirstAbscissa = true;
        foreach($this->_aChartParameters['Xinterval_marks'] as $i){
            if (!$bFirstAbscissa){
                $xCenter = (($this->_iMaxX-self::FRAME_MARGIN)-($i*$this->_iPriceWidth));
                $this->_oImageChart->drawAbscissa($xCenter, self::FRAME_MARGIN, self::FRAME_MARGIN+2+$this->_aCharts['prices']['graphY']['plottable_space']);
            }
            $bFirstAbscissa=false;
        }
        
        $this->_oImageChart->drawFrame(self::FRAME_MARGIN
                , $this->_aCharts['rsi']['graphY']['frame_top']
                , $this->_iMaxX-self::FRAME_MARGIN-1
                , $this->_aCharts['rsi']['graphY']['frame_bottom']);
        
        
        
        $this->_oImageChart->drawLine(self::FRAME_MARGIN, $this->_aCharts['rsi']['graphY']['mark_bottom']
                                        , $this->_iMaxX-self::FRAME_MARGIN-1
                                        , $this->_aCharts['rsi']['graphY']['mark_bottom']
                                        , array('r' => 0, 'g' => 25, 'b' => 255));
        $this->_oImageChart->drawLine(self::FRAME_MARGIN, $this->_aCharts['rsi']['graphY']['mark_top']
                                        , $this->_iMaxX-self::FRAME_MARGIN-1
                                        , $this->_aCharts['rsi']['graphY']['mark_top']
                                        , array('r' => 0, 'g' => 25, 'b' => 255));
            
        
        foreach($this->_aGraphicalPrices as $i=>$oRealPrice){
            if (!is_null($oRealPrice)){
                $x = (($this->_iMaxX-self::FRAME_MARGIN)-($i*$this->_iPriceWidth));
                $oRealPrice->calculateGraphParameters($this);
                $oRealPrice->calculateGraphIndicators($this);
                $oRealPrice->getIndicators()->drawIndicators($this->_oImageChart, $x);
                
                $aIndicatorsData = $oRealPrice->getIndicators()->getData();
//                var_dump($oRealPrice->getClose(),$aIndicatorsData['RSI']);
                
                $oRealPrice->drawPrice($this->_oImageChart, $x);
            }
        }
//        exit;
        $this->_oImageChart->dumpImage();
    }
}
?>
