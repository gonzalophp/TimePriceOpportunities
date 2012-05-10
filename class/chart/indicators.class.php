<?php
require_once('class/chart/realprice.class.php');

class indicators {
    private $_aIndicators;
    private $_aData = array('prices_so_far' => array()
                           ,'MA'            => array() );
    
    public function indicators($aIndicators){
        $this->_aIndicators = $aIndicators;
    }
    
    public function buildIndicators($oPreviousRealPrice, realprice $oRealPrice){
        if (!is_null($oPreviousRealPrice)){
            $aPreviousRealPriceIndicatorsData = $oPreviousRealPrice->getIndicators()->getData();
            $this->_aData['prices_so_far'] = $aPreviousRealPriceIndicatorsData['prices_so_far'];
            
            if (array_key_exists('RSI', $aPreviousRealPriceIndicatorsData)){
                foreach($aPreviousRealPriceIndicatorsData['RSI'] as $n=>$aRSIData){
                    $this->_aData['RSI'][$n] = array('real'    => NULL
                                                    ,'graph'    => NULL
                                                    ,'RS_Gain'  => NULL
                                                    ,'RS_Loss'  => NULL
                                                    ,'previous' => array('RS_Gain' => $aRSIData['RS_Gain']
                                                                        ,'RS_Loss' => $aRSIData['RS_Loss']));
                }
            }
        }
        $this->_aData['prices_so_far'][] = $oRealPrice->getClose();
        
        if (array_key_exists('ma', $this->_aIndicators)) $this->_aData['MA'] = $this->_getMA();
        if (array_key_exists('bol', $this->_aIndicators)) $this->_aData['BOL'] = $this->_getBollinger();
        if (array_key_exists('rsi', $this->_aIndicators)) $this->_aData['RSI'] = $this->_getRSI();
    }
    
    public function getData(){
        return $this->_aData;
    }
    
    public function calculateGraphIndicators($oGraphicalChart){
        foreach($this->_aIndicators['ma'] as $iMAPrices){
            if (count($this->_aData['prices_so_far'])>=$iMAPrices){
                $this->_aData['MA'][$iMAPrices]['graph'] = $oGraphicalChart->getGraphicalY($this->_aData['MA'][$iMAPrices]['real']);
            }
        }
        
        if (array_key_exists('bol', $this->_aIndicators) && !is_null($this->_aData['BOL'])){
            $this->_aData['BOL']['graph']['up'] = $oGraphicalChart->getGraphicalY($this->_aData['BOL']['real']['up']);
            $this->_aData['BOL']['graph']['down'] = $oGraphicalChart->getGraphicalY($this->_aData['BOL']['real']['down']);
        }
    }
    
    public function drawIndicators($oImageChart, $x){
        static $aPreviousIndicatorsData = NULL;
        static $iPreviousX = NULL;
        
        if (array_key_exists('ma', $this->_aIndicators)) {
            foreach($this->_aIndicators['ma'] as $iMAPrices){
                if (!is_null($aPreviousIndicatorsData) && !(is_null($this->_aData['MA'][$iMAPrices]['graph']))){
                    $oImageChart->drawPoint($x, $this->_aData['MA'][$iMAPrices]['graph']);
                    $oImageChart->drawLine($x, $this->_aData['MA'][$iMAPrices]['graph'], $iPreviousX, $aPreviousIndicatorsData['MA'][$iMAPrices]['graph'], $this->_aData['MA'][$iMAPrices]['color']);
                }
            }
        }
        
        if (array_key_exists('bol', $this->_aIndicators) && (!is_null($this->_aData['BOL']))){
            if (!is_null($aPreviousIndicatorsData) && !is_null($this->_aData['BOL'])){
                $oImageChart->drawLine($x, $this->_aData['BOL']['graph']['up']
                                        , $iPreviousX
                                        , $aPreviousIndicatorsData['BOL']['graph']['up']
                                        , array('r' => 0, 'g' => 25, 'b' => 255));
                $oImageChart->drawLine($x, $this->_aData['BOL']['graph']['down']
                                        , $iPreviousX
                                        , $aPreviousIndicatorsData['BOL']['graph']['down']
                                        , array('r' => 0, 'g' => 25, 'b' => 255));
            }
        }
        
        $aPreviousIndicatorsData = $this->_aData;
        $iPreviousX = $x;
    }
    
    private function _getRSI($iPrices=NULL){
        $aRSIParameters = (is_null($iPrices)) ? $this->_aIndicators['rsi'] : array($iPrices);
        
        foreach($aRSIParameters as $n){
            if (!array_key_exists('RSI', $this->_aData)){
                $this->_aData['RSI'] = array();
            }
            
            if (!array_key_exists($n, $this->_aData['RSI'])){
                $this->_aData['RSI'][$n] =array( 'real'   => NULL
                                                , 'RS_Gain' => NULL
                                                , 'RS_Loss' => NULL
                                                , 'previous'=> array('RS_Gain'  => NULL
                                                                    ,'RS_Los'   => NULL));
            }
            
            if (is_null($this->_aData['RSI'][$n]['real'])){
                if(is_null($this->_aData['RSI'][$n]['previous']['RS_Gain'])){
                    if(count($this->_aData['prices_so_far']) == $n){
                        $aNPrices = array_slice($this->_aData['prices_so_far'], (count($this->_aData['prices_so_far'])-$n), $n);
                        $nPreviousPrice = NULL;
                        $nLoss = 0;
                        $nGain = 0;
                        foreach($aNPrices as $nPrice){
                            if (!is_null($nPreviousPrice)){
                                $nDifference = $nPreviousPrice-$nPrice;
                                if ($nDifference>0){
                                    $nGain += $nDifference;
                                }
                                else {
                                    $nLoss += -$nDifference;
                                }
                            }
                            $nPreviousPrice = $nPrice;
                        }
                        $this->_aData['RSI'][$n]['RS_Gain'] = $nGain/$n;
                        $this->_aData['RSI'][$n]['RS_Loss'] = $nLoss/$n;
                        $this->_aData['RSI'][$n]['real']   = 100 - (100/(1+($this->_aData['RSI'][$n]['RS_Gain']/$this->_aData['RSI'][$n]['RS_Loss'])));
                    }
                    elseif(count($this->_aData['prices_so_far']) > $n){
                        echo 'RSI error: previous rs not found';
                    }
                }
                else {
                    $aNPrices = array_slice($this->_aData['prices_so_far'], (count($this->_aData['prices_so_far'])-2), 2);
                    $nDifference = $aNPrices[1]-$aNPrices[0];
                    $nGainLastPrice = ($nDifference>0) ? $nDifference : 0;
                    $nLossLastPrice = ($nDifference<0) ? -$nDifference : 0;
                    $this->_aData['RSI'][$n]['RS_Gain'] = ((($n-1)*$this->_aData['RSI'][$n]['previous']['RS_Gain'])+$nGainLastPrice)/$n;
                    $this->_aData['RSI'][$n]['RS_Loss'] = ((($n-1)*$this->_aData['RSI'][$n]['previous']['RS_Loss'])+$nLossLastPrice)/$n;
                    $this->_aData['RSI'][$n]['real']   = 100 - (100/(1+($this->_aData['RSI'][$n]['RS_Gain']/$this->_aData['RSI'][$n]['RS_Loss'])));
                }
            }
        }
        
        if (is_null($iPrices)){
            return $this->_aData['RSI'];
        }
        else {
            return $this->_aData['RSI'][$n]['real'];
        }
    }
    
    private function _getStandardDeviation($n){
        if (!array_key_exists('STD_DEV', $this->_aData)){
            $this->_aData['STD_DEV'] = array();
        }
        if (!array_key_exists($n, $this->_aData['STD_DEV'])){
            if(count($this->_aData['prices_so_far']) >= $n){
                $aNPrices = array_slice($this->_aData['prices_so_far'], (count($this->_aData['prices_so_far'])-$n), $n);
                $nSum = 0;
                $nMA = $this->_getMA($n);
                foreach($aNPrices as $nPrice){
                    $nSum += pow(($nPrice - $nMA),2);
                }

                $this->_aData['STD_DEV'][$n] = sqrt($nSum/$n);
            }
            else {
                $this->_aData['STD_DEV'][$n] = NULL;
            }
        }
        
        return $this->_aData['STD_DEV'][$n];
    }
    
    
    private function _getBollinger(){
        $nMA = $this->_getMA($this->_aIndicators['bol']['n']);
        $nStdDeviation = $this->_getStandardDeviation($this->_aIndicators['bol']['n']);
        
        if (!is_null($nMA) && !is_null($nStdDeviation)){
            return array('real' => array( 'up'     => ($nMA+($this->_aIndicators['bol']['std_dev']*$nStdDeviation))
                                        , 'down'    => ($nMA-($this->_aIndicators['bol']['std_dev']*$nStdDeviation)))
                        ,'graph' => array('up'      => NULL
                                        , 'down'    => NULL));
        }
        else {
            return NULL;
        }
    }
    
    private function _getMA($n=NULL){
        if (is_null($n)){
            $aMAColors = array(  array('r'=>255, 'g'=>0, 'b'=>0)
                                ,array('r'=>0, 'g'=>255, 'b'=>0)
                                ,array('r'=>0, 'g'=>0, 'b'=>255)
                                ,array('r'=>255, 'g'=>255, 'b'=>0));
            reset($aMAColors);
            foreach($this->_aIndicators['ma'] as $iMAPrices){
                if (!array_key_exists($iMAPrices, $this->_aData['MA'])){
                    list($iKey,$aColor) = each($aMAColors);
                    $this->_aData['MA'][$iMAPrices] = array('real' => NULL, 'graph' => NULL, 'color' => $aColor);
                }

                $this->_aData['MA'][$iMAPrices]['real'] = (count($this->_aData['prices_so_far'])>=$iMAPrices) 
                                                            ? (array_sum(array_slice($this->_aData['prices_so_far'], (count($this->_aData['prices_so_far'])-$iMAPrices), $iMAPrices))/$iMAPrices) : NULL;
            }
            
            return $this->_aData['MA'];
        }
        else {
            if (!array_key_exists($n, $this->_aData['MA'])){
                $this->_aData['MA'][$n]['real'] = (count($this->_aData['prices_so_far']) >= $n) 
                                                    ? (array_sum(array_slice($this->_aData['prices_so_far'], (count($this->_aData['prices_so_far'])-$n), $n))/$n) : NULL;
            }
            return $this->_aData['MA'][$n]['real'];
        }
    }
}
?>
