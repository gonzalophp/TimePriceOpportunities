<?php
class chart{
    
    private $_aCharts;
    
    public function chart(){
        
        
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
    
   
    
    public function addChart($sChart){
        switch($sChart){
            case 'prices':
                $this->_aCharts[$sChart] = array('caption'  => 'DAX 30'
                                                ,'real'     => array('extremes' => array('minX'=>0
                                                                                        ,'minY'=>0
                                                                                        ,'maxX'=>0
                                                                                        ,'maxY'=>0)
                                                                    ,'y_marks'  => array(6200,6300,6400,6500)
                                                                    ,'timeframe'=> array('2012-05-10' => array(100,200,300000,40000)
                                                                                        ,'2012-05-11' => array(100,200,300000,40000)))
                                                ,'graph'    => array('extremes' => array('minX'=>0
                                                                                        ,'minY'=>0
                                                                                        ,'maxX'=>0
                                                                                        ,'maxY'=>0)
                                                                    ,'y_marks'  => array(100,200,300,400)
                                                                    ,'eq'       => array('part1'=>0
                                                                                        ,'part2'=>0)));
            break;
            case 'rsi':
                $this->_aCharts[$sChart] = array('caption'  => 'RSI 14'
                                                ,'real'     => array('extremes' => array('minY'=>0
                                                                                        ,'maxY'=>100)
                                                                    ,'y_marks'  => array(30,70))
                                                ,'graph'    => array('extremes' => array('minX'=>0
                                                                                        ,'minY'=>0
                                                                                        ,'maxX'=>0
                                                                                        ,'maxY'=>0))
                                                                    ,'y_marks'  => array(0,0)
                                                                    ,'eq'       => array('part1'=>0
                                                                                        ,'part2'=>0));
            break;
            case 'sto':
                $this->_aCharts[$sChart] = array('caption'  => 'Stochastic 14 3 5'
                                                ,'real'     => array('extremes' => array('minY'=>0
                                                                                        ,'maxY'=>100)
                                                                    ,'y_marks'  => array(30,70))
                                                ,'graph'    => array('extremes' => array('minX'=>0
                                                                                        ,'minY'=>0
                                                                                        ,'maxX'=>0
                                                                                        ,'maxY'=>0))
                                                                    ,'y_marks'  => array(0,0)
                                                                    ,'eq'       => array('part1'=>0
                                                                                        ,'part2'=>0));
            break;
        }
    }
    
}
?>
