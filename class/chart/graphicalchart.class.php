<?php
require_once('class/chart/realchart.class.php');
require_once('class/chart/imagechart.class.php');

class graphicalChart {
    private $_aCharts;
    private $_aImageSize;
    private $_iMargin;
    private $_oRealChart;
    
    public function graphicalChart($iMaxX,$iMaxY,$iMargin){
        $this->_oImageChart = new imageChart($iMaxX, $iMaxY, $iMargin);
        $this->_aImageSize  = array('x' => $iMaxX, 'y' => $iMaxY);
        $this->_iMargin     = $iMargin;
    }
    
    public function buildGraphicalChart(realChart $oRealChart){
        $this->_oRealChart = $oRealChart;
        
        $oPlottableSize = $this->getPlottableSize();
        $aRealChartParameters = $this->_oRealChart->getChartParameters($oPlottableSize['x']);

        $this->addChartPrices('DAX', $aRealChartParameters['extremes'], $aRealChartParameters['Yinterval_marks'], $aRealChartParameters['Xinterval_marks']);
        $this->addChart('rsi', 'RSI 14', array('minY'=>0,'maxY'=>100), array(30,70), array( 0 => array('r'=>0, 'g'=>0, 'b'=>0)));
        $this->buildGraphicalChartParameters();
    }
    
    public function getPlottableSize($sChart=NULL){
        if(is_null($sChart)){
            return array('x' => ($this->_aImageSize['x']-2*$this->_iMargin)
                        ,'y' => ($this->_aImageSize['y']-2*$this->_iMargin));
        }
        else {
            return array('x' => $this->_aCharts[$sChart]['graph']['corners']['w']
                        ,'y' => $this->_aCharts[$sChart]['graph']['corners']['h']);
        }
    }
    
    public function addChartPrices($sCaption, $aRealExtremes, $aRealYMarks, $aRealTimeFrame){
        $this->addChart('prices', $sCaption, $aRealExtremes, $aRealYMarks, array());
        $this->_aCharts['prices']['real']['timeframe'] = $aRealTimeFrame;
        $this->_aCharts['prices']['real']['prices'] = $this->_oRealChart->getPrices();
    }
    
    public function addChart($sChart, $sCaption, $aRealExtremes, $aRealYMarks, $aColors){
        $this->_aCharts[$sChart] = array('caption'  => $sCaption
                                        ,'real'     => array('extremes' => $aRealExtremes
                                                            ,'y_marks'  => $aRealYMarks)
                                        ,'graph'    => array('corners'  => array()
                                                            ,'y_marks'  => array()
                                                            ,'eq'       => array('part1'=>0
                                                                                ,'part2'=>0)
                                                            ,'colors'   => $aColors));
    }
    
    public function getTimeFrame(){
        return $this->_aCharts['prices']['real']['timeframe'];
    }
    
    public function buildGraphicalChartParameters(){
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
        $aAvailableIndicatorsCharts = array('rsi','sto');
        $aIndicatorsCharts = array_intersect(array_keys($this->_aCharts), $aAvailableIndicatorsCharts);
        $iChartDistribution = 10+2*count($aIndicatorsCharts);
        
        $this->_aCharts['prices']['graph']['corners'] = array('x' => $this->_iMargin
                                                            ,'y' => $this->_iMargin
                                                            ,'w' => $this->_aImageSize['x']-2*$this->_iMargin
                                                            ,'h' => (int)(string)(($this->_aImageSize['y']-(2*$this->_iMargin))*(10/$iChartDistribution)));
        $nChartIndicator = 0;
        foreach(array_keys($this->_aCharts) as $sChart){
            if ($sChart!='prices'){
                $this->_aCharts[$sChart]['graph']['corners'] = array('x' => $this->_iMargin
                                                                    ,'y' => (int)(string)($this->_iMargin+(($this->_aImageSize['y']-(2*$this->_iMargin))*(10/$iChartDistribution))
                                                                            +($nChartIndicator*(($this->_aImageSize['y']-(2*$this->_iMargin))*(2/$iChartDistribution))))
                                                                    ,'w' => $this->_aImageSize['x']-2*$this->_iMargin
                                                                    ,'h' => (int)(string)(($this->_aImageSize['y']-(2*$this->_iMargin))*(2/$iChartDistribution)));
                $nChartIndicator++;        
            }
            $this->_aCharts[$sChart]['graph']['eq']['part1'] = $this->_aCharts[$sChart]['graph']['corners']['y']-1+($this->_aCharts[$sChart]['graph']['corners']['h']*($this->_aCharts[$sChart]['real']['extremes']['maxY']/($this->_aCharts[$sChart]['real']['extremes']['maxY']-$this->_aCharts[$sChart]['real']['extremes']['minY'])));
            $this->_aCharts[$sChart]['graph']['eq']['part2'] = $this->_aCharts[$sChart]['graph']['corners']['h']/($this->_aCharts[$sChart]['real']['extremes']['maxY']-$this->_aCharts[$sChart]['real']['extremes']['minY']);
            
            foreach($this->_aCharts[$sChart]['real']['y_marks'] as $i=>$nRealYMarks){
                $this->_aCharts[$sChart]['graph']['y_marks'][$i] = (int) (string) ($this->_aCharts[$sChart]['graph']['eq']['part1']-$this->_aCharts[$sChart]['graph']['eq']['part2']*$nRealYMarks);
            }
        }
    }
    
    public function getGraphicalY($sChart,$nRealY){
        return (int) (string) ($this->_aCharts[$sChart]['graph']['eq']['part1']-$this->_aCharts[$sChart]['graph']['eq']['part2']*$nRealY);
    }
    
    public function getChartParameters($sChart){
        return $this->_aCharts[$sChart];
    }
    
    public function drawCharts(){
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
                                                , $this->_aCharts['prices']['graph']['corners']['x']+$this->_aCharts['prices']['graph']['corners']['w']+2
                                                , $iGraphYMark-3
                                                , $this->_aCharts['prices']['real']['y_marks'][$i]
                                                , array('r'=>50,'g'=>50,'b'=>50));
                }
                
            }
        }
        
        $iPriceWidth = $this->_oRealChart->getPriceWidth();
        $bFirstAbscissa = true;
        foreach($this->_aCharts['prices']['graph']['x_marks'] as $sDay=>$i){
            if (!$bFirstAbscissa){
                $this->_oImageChart->drawAbscissa(($this->_aCharts['prices']['graph']['corners']['x']+$this->_aCharts['prices']['graph']['corners']['w']-($i*$iPriceWidth))
                                                , $this->_aCharts['prices']['graph']['corners']['x']
                                                , ($this->_aCharts['prices']['graph']['corners']['y']+$this->_aCharts['prices']['graph']['corners']['h']+2));
            }
            $bFirstAbscissa=false;
        }
    }
    
    public function draw(){
        $this->drawCharts();
        $iPriceWidth = $this->_oRealChart->getPriceWidth();
        
        foreach($this->_aCharts['prices']['graph']['prices'] as $i=>$oRealPrice){
            if (!is_null($oRealPrice)){
                $x = (($this->_aCharts['prices']['graph']['corners']['x']+$this->_aCharts['prices']['graph']['corners']['w'])-($i*$iPriceWidth));
                $oRealPrice->calculateGraphParameters($this);
                $oRealPrice->calculateGraphIndicators($this);
                $oRealPrice->getIndicators()->drawIndicators($this->_oImageChart, $x);
                $oRealPrice->drawPrice($this->_oImageChart, $x);
            }
        }
        $this->_oImageChart->drawImage();
    }
}
?>
