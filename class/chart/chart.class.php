<?php
class chart{
    private $_aCharts;
    private $_aImageSize;
    private $_iMargin;
    private $_iPriceWidth;
    
    public function chart($iMaxX,$iMaxY,$iMargin){
        $this->_aImageSize = array('x' => $iMaxX, 'y' => $iMaxY);
        $this->_iMargin     = $iMargin;
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
    
    public function addChartPrices($sCaption, $aRealExtremes, $aRealYMarks, $aRealTimeFrame, $iPriceWidth){
        $this->addChart('prices', $sCaption, $aRealExtremes, $aRealYMarks, array());
        $this->_aCharts['prices']['real']['timeframe'] = $aRealTimeFrame;
        
        $this->_iPriceWidth = $iPriceWidth;
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
        
        
        $i=0;
        $this->_aCharts['prices']['graph']['x_marks'] = array();
        foreach($this->_aCharts['prices']['real']['timeframe'] as $sDay=>$aTimes){
            $this->_aCharts['prices']['graph']['x_marks'][$sDay] = $i;
            $i += count($aTimes);
        }
        
        
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
    
    public function drawCharts($oImageChart){
        foreach($this->_aCharts as $sChart=>$aChartParameters){
            $oImageChart->drawFrame($this->_aCharts[$sChart]['graph']['corners']['x']
                                    , $this->_aCharts[$sChart]['graph']['corners']['y']
                                    , $this->_aCharts[$sChart]['graph']['corners']['x']+$this->_aCharts[$sChart]['graph']['corners']['w']
                                    , $this->_aCharts[$sChart]['graph']['corners']['y']+$this->_aCharts[$sChart]['graph']['corners']['h']);
            foreach($aChartParameters['graph']['y_marks'] as $iGraphYMark){
                $oImageChart->drawLine($this->_aCharts[$sChart]['graph']['corners']['x']
                                     , $iGraphYMark
                                     , $this->_aCharts[$sChart]['graph']['corners']['x']+$this->_aCharts[$sChart]['graph']['corners']['w']
                                     , $iGraphYMark
                                     , array('r' => 180, 'g' => 180, 'b' => 180));
//                imagestring($this->_rImage, 1, $this->_iWidth-$this->_iFrame+1, $iHeight-4, $nRealYIntervalMarks, $this->_getColor(50, 50, 50));
            }
        }
        
        $bFirstAbscissa = true;
        foreach($this->_aCharts['prices']['graph']['x_marks'] as $sDay=>$i){
            if (!$bFirstAbscissa){
                $oImageChart->drawAbscissa(($this->_aCharts['prices']['graph']['corners']['x']+$this->_aCharts['prices']['graph']['corners']['w']-($i*$this->_iPriceWidth))
                                          , $this->_aCharts['prices']['graph']['corners']['x']
                                          , ($this->_aCharts['prices']['graph']['corners']['y']+$this->_aCharts['prices']['graph']['corners']['h']+2));
            }
            $bFirstAbscissa=false;
        }
    }
}
?>
