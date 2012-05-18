<?php
require_once('class/chart/realchart.class.php');
require_once('class/chart/imagechart.class.php');

class graphicalChart {
    private $_aCharts;
    private $_aImageSize;
    private $_oRealChart;
    private $_aAvailableIndicatorsCharts = array('rsi','sto');
    
    const GRAPH_MARGIN=20;
    
    public function graphicalChart($iMaxX,$iMaxY){
        $this->_oImageChart = new imageChart($iMaxX, $iMaxY, self::GRAPH_MARGIN);
        $this->_aImageSize  = array('x' => $iMaxX, 'y' => $iMaxY);
    }
    
    public function buildGraphicalChart(realChart $oRealChart){
        $this->_oRealChart = $oRealChart;
        
        $oPlottableSize = $this->_getPlottableSize();
        $aRealChartParameters = $this->_oRealChart->getChartParameters($oPlottableSize['x']);

        $this->_addChartPrices($this->_oRealChart->getQuote(), $aRealChartParameters['extremes'], $aRealChartParameters['Yinterval_marks'], $aRealChartParameters['Xinterval_marks']);
        
        $aIndicatorsSettings = $this->_oRealChart->getIndicatorsSettings();
        foreach($this->_aAvailableIndicatorsCharts as $sChart){
            if (array_key_exists($sChart, $aIndicatorsSettings)){
                $this->_addChart($sChart, $sChart.' '.implode(' ', $aIndicatorsSettings[$sChart]), array('minY'=>0,'maxY'=>100), array(30,70), array( 0 => array('r'=>0, 'g'=>0, 'b'=>0)));
            }
        }
        $this->_buildGraphicalChartParameters();
    }
    
    public function getGraphicalY($sChart,$nRealY){
        return (int) (string) ($this->_aCharts[$sChart]['graph']['eq']['part1']-$this->_aCharts[$sChart]['graph']['eq']['part2']*$nRealY);
    }
    
    public function draw(){
        $this->_drawCharts();
        $this->_drawPrices();
        $this->_oImageChart->drawImage();
    }
    
    private function _getPlottableSize($sChart=NULL){
        if(is_null($sChart)){
            return array('x' => ($this->_aImageSize['x']-2*self::GRAPH_MARGIN)
                        ,'y' => ($this->_aImageSize['y']-2*self::GRAPH_MARGIN));
        }
        else {
            return array('x' => $this->_aCharts[$sChart]['graph']['corners']['w']
                        ,'y' => $this->_aCharts[$sChart]['graph']['corners']['h']);
        }
    }
    
    private function _addChartPrices($sCaption, $aRealExtremes, $aRealYMarks, $aRealTimeFrame){
        $this->_addChart('prices', $sCaption, $aRealExtremes, $aRealYMarks, array());
        $this->_aCharts['prices']['real']['timeframe'] = $aRealTimeFrame;
        $this->_aCharts['prices']['real']['prices'] = $this->_oRealChart->getPrices();
    }
    
    private function _addChart($sChart, $sCaption, $aRealExtremes, $aRealYMarks, $aColors){
        $this->_aCharts[$sChart] = array('caption'  => $sCaption
                                        ,'real'     => array('extremes' => $aRealExtremes
                                                            ,'y_marks'  => $aRealYMarks)
                                        ,'graph'    => array('corners'  => array()
                                                            ,'y_marks'  => array()
                                                            ,'eq'       => array('part1'=>0
                                                                                ,'part2'=>0)
                                                            ,'colors'   => $aColors));
    }
    
    private function _buildGraphicalChartParameters(){
        // Prices
        $i=0;
        foreach($this->_aCharts['prices']['real']['timeframe'] as $sDay=>$aTimes){
            foreach($aTimes as $iDateTime){
                $this->_aCharts['prices']['graph']['prices'][++$i] = (array_key_exists($iDateTime, $this->_aCharts['prices']['real']['prices'])) ? $this->_aCharts['prices']['real']['prices'][$iDateTime]:NULL;
            }
        }
        
        // X Marks
        $i=0;
        $this->_aCharts['prices']['graph']['x_marks'] = array();
        foreach($this->_aCharts['prices']['real']['timeframe'] as $sDay=>$aTimes){
            $this->_aCharts['prices']['graph']['x_marks'][$sDay] = $i;
            $i += count($aTimes);
        }
        
        // Indicators
        $aIndicatorsCharts = array_intersect(array_keys($this->_aCharts), $this->_aAvailableIndicatorsCharts);
        
        $iChartDistribution = 10+2*count($aIndicatorsCharts);
        
        $this->_aCharts['prices']['graph']['corners'] = array('x' => self::GRAPH_MARGIN/2
                                                            ,'y' => (1/2)*self::GRAPH_MARGIN
                                                            ,'w' => $this->_aImageSize['x']-2*self::GRAPH_MARGIN
                                                            ,'h' => (int)(string)(($this->_aImageSize['y']-(2*self::GRAPH_MARGIN))*(10/$iChartDistribution)-5));
        $nChartIndicator = 0;
        foreach(array_keys($this->_aCharts) as $sChart){
            if ($sChart!='prices'){
                $this->_aCharts[$sChart]['graph']['corners'] = array('x' => self::GRAPH_MARGIN/2
                                                                    ,'y' => (int)(string)((3/2)*self::GRAPH_MARGIN+(($this->_aImageSize['y']-(2*self::GRAPH_MARGIN))*(10/$iChartDistribution))
                                                                            +($nChartIndicator*(($this->_aImageSize['y']-(2*self::GRAPH_MARGIN))*(2/$iChartDistribution))))
                                                                    ,'w' => $this->_aImageSize['x']-2*self::GRAPH_MARGIN
                                                                    ,'h' => (int)(string)(($this->_aImageSize['y']-(2*self::GRAPH_MARGIN))*(2/$iChartDistribution)-1));
                $nChartIndicator++;        
            }
            $this->_aCharts[$sChart]['graph']['eq']['part1'] = $this->_aCharts[$sChart]['graph']['corners']['y']-1+($this->_aCharts[$sChart]['graph']['corners']['h']*($this->_aCharts[$sChart]['real']['extremes']['maxY']/($this->_aCharts[$sChart]['real']['extremes']['maxY']-$this->_aCharts[$sChart]['real']['extremes']['minY'])));
            $this->_aCharts[$sChart]['graph']['eq']['part2'] = $this->_aCharts[$sChart]['graph']['corners']['h']/($this->_aCharts[$sChart]['real']['extremes']['maxY']-$this->_aCharts[$sChart]['real']['extremes']['minY']);
            
            foreach($this->_aCharts[$sChart]['real']['y_marks'] as $i=>$nRealYMarks){
                $this->_aCharts[$sChart]['graph']['y_marks'][$i] = (int) (string) ($this->_aCharts[$sChart]['graph']['eq']['part1']-$this->_aCharts[$sChart]['graph']['eq']['part2']*$nRealYMarks);
            }
        }
    }
    
    private function _drawCharts(){
        foreach($this->_aCharts as $sChart=>$aChartParameters){
            $this->_oImageChart->drawFrame($this->_aCharts[$sChart]['graph']['corners']['x']
                                        , $this->_aCharts[$sChart]['graph']['corners']['y']
                                        , $this->_aCharts[$sChart]['graph']['corners']['x']+$this->_aCharts[$sChart]['graph']['corners']['w']
                                        , $this->_aCharts[$sChart]['graph']['corners']['y']+$this->_aCharts[$sChart]['graph']['corners']['h']);
            foreach($aChartParameters['graph']['y_marks'] as $i=>$iGraphYMark){
                $this->_oImageChart->drawLine($this->_aCharts[$sChart]['graph']['corners']['x']
                                            , $iGraphYMark
                                            , $this->_aCharts[$sChart]['graph']['corners']['x']+$this->_aCharts[$sChart]['graph']['corners']['w']
                                            , $iGraphYMark
                                            , array('r' => 180, 'g' => 180, 'b' => 180));
                if ($sChart == 'prices'){
                    $this->_oImageChart->drawLabel(1
                                                , $this->_aCharts['prices']['graph']['corners']['x']+$this->_aCharts['prices']['graph']['corners']['w']+3
                                                , $iGraphYMark-3
                                                , $this->_aCharts['prices']['real']['y_marks'][$i]
                                                , array('r'=>50,'g'=>50,'b'=>50));
                }
            }
            $this->_oImageChart->drawLabel(3, $this->_aCharts[$sChart]['graph']['corners']['x']+5
                                            , $this->_aCharts[$sChart]['graph']['corners']['y']+5
                                            , $this->_aCharts[$sChart]['caption']
                                            , array('r'=>0,'g'=>0,'b'=>0));
        }
        
        $iPriceWidth = $this->_oRealChart->getPriceWidth();
        
        $bFirstAbscissa = true;
        $iPreviousWeek = NULL;
        foreach($this->_aCharts['prices']['graph']['x_marks'] as $sDate=>$i){
            $iWeek = date('W',strtotime($sDate));
            if (!$bFirstAbscissa){
                $aAbscissaColor = ((!is_null($iPreviousWeek)) && ($iWeek != $iPreviousWeek)) ? array('r'=>255, 'g'=>0, 'b'=>0) : array('r'=>180, 'g'=>180, 'b'=>180);
                $this->_oImageChart->drawAbscissa(($this->_aCharts['prices']['graph']['corners']['x']+$this->_aCharts['prices']['graph']['corners']['w']-($i*$iPriceWidth))
                                                , $this->_aCharts['prices']['graph']['corners']['y']
                                                , ($this->_aCharts['prices']['graph']['corners']['y']+$this->_aCharts['prices']['graph']['corners']['h']+2)
                                                , $aAbscissaColor);
                $this->_oImageChart->drawLabel(1
                                        , $this->_aCharts['prices']['graph']['corners']['x']+$this->_aCharts['prices']['graph']['corners']['w']-($i*$iPriceWidth)+4
                                        , $this->_aCharts['prices']['graph']['corners']['y']+$this->_aCharts['prices']['graph']['corners']['h']+4
                                        , $sDate
                                        , array('r'=>50,'g'=>50,'b'=>50));
            }
            
            
            $bFirstAbscissa=false;
            $iPreviousWeek = $iWeek;
        }
    }
    
    private function _drawPrices(){
        
        foreach($this->_aCharts['prices']['graph']['prices'] as $i=>$oRealPrice){
            if (!is_null($oRealPrice)){
                $oRealPrice->calculateGraphParameters($this);
                $oRealPrice->calculateGraphIndicators($this);
            }
        }
        
        $iPriceWidth = $this->_oRealChart->getPriceWidth();
        $iRightExtreme = $this->_aCharts['prices']['graph']['corners']['x']+$this->_aCharts['prices']['graph']['corners']['w'];
        foreach($this->_aCharts['prices']['graph']['prices'] as $i=>$oRealPrice){
            if (!is_null($oRealPrice)){
                $x = (int)(string)($iRightExtreme-($i*$iPriceWidth));
                $oRealPrice->getIndicators()->drawIndicators($this->_oImageChart, $x);
                $oRealPrice->drawPrice($this->_oImageChart, $x);
            }
        }
//        exit;
    }
}
?>
