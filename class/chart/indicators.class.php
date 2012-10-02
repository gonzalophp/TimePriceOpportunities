<?php
require_once('class/chart/realprice.class.php');

class indicators {
    private $_aIndicators;
    private $_aData = array();
    private static $_aRealPrices;
    
    public function indicators($aIndicators){
        $this->_aIndicators = $aIndicators;
    }
    
    public function buildIndicators(realprice $oRealPrice){
        self::$_aRealPrices[] = $oRealPrice;
        if (array_key_exists('ma', $this->_aIndicators)) $this->_aData['MA'] = $this->_getMA();
        if (array_key_exists('bol', $this->_aIndicators)) $this->_aData['BOL'] = $this->_getBollinger();
        if (array_key_exists('rsi', $this->_aIndicators)) $this->_aData['RSI'] = $this->_getRSI();
        if (array_key_exists('sto', $this->_aIndicators)) $this->_aData['STO'] = $this->_getSTO();
        if (array_key_exists('sar', $this->_aIndicators)) $this->_aData['SAR'] = $this->_getSARParabolic();
    }
    
    public function getData(){
        return $this->_aData;
    }
    
    public function calculateGraphIndicators($oGraphicalChart){
        if (array_key_exists('ma', $this->_aIndicators)){
            foreach($this->_aIndicators['ma'] as $iMAPrices){
                if ((count(self::$_aRealPrices)>=$iMAPrices) && !is_null($this->_aData['MA'][$iMAPrices]['real'])){
                    $this->_aData['MA'][$iMAPrices]['graph'] = $oGraphicalChart->getGraphicalY('prices',$this->_aData['MA'][$iMAPrices]['real']);
                }
            }
        }
        
        if (array_key_exists('bol', $this->_aIndicators) && !is_null($this->_aData['BOL'])){
            $this->_aData['BOL']['graph']['up'] = $oGraphicalChart->getGraphicalY('prices',$this->_aData['BOL']['real']['up']);
            $this->_aData['BOL']['graph']['down'] = $oGraphicalChart->getGraphicalY('prices',$this->_aData['BOL']['real']['down']);
        }
        
        if (array_key_exists('rsi', $this->_aIndicators) && !is_null($this->_aData['RSI'])){
            foreach($this->_aData['RSI'] as $n=>$aRSIData){
                if (!is_null($this->_aData['RSI'][$n]['real'])){
                    $this->_aData['RSI'][$n]['graph'] = $oGraphicalChart->getGraphicalY('rsi',$this->_aData['RSI'][$n]['real']);
                }
            }
        }
        
        if (array_key_exists('sto', $this->_aIndicators) && !is_null($this->_aData['STO'])){
            if (!is_null($this->_aData['STO']['real']['k'])){
                $this->_aData['STO']['graph']['k'] = $oGraphicalChart->getGraphicalY('sto',$this->_aData['STO']['real']['k']);
                $this->_aData['STO']['graph']['d'] = $oGraphicalChart->getGraphicalY('sto',$this->_aData['STO']['real']['d']);
            }
        }
        
        if (array_key_exists('sar', $this->_aIndicators) && !is_null($this->_aData['SAR'])){
            if (!is_null($this->_aData['SAR']['real']['psar'])){
                $this->_aData['SAR']['graph'] = $oGraphicalChart->getGraphicalY('prices',$this->_aData['SAR']['real']['psar']);
            }
        }
    }
    
    public function drawIndicators($oImageChart, $x){
        static $aPreviousIndicatorsData = NULL;
        static $iPreviousX = NULL;
        static $bDrawRSIValueLabel=false;
        static $bDrawSTOValueLabel=false;
        
        if (array_key_exists('ma', $this->_aIndicators)) {
            foreach($this->_aIndicators['ma'] as $iMAPrices){
                if (!is_null($aPreviousIndicatorsData) && !(is_null($this->_aData['MA'][$iMAPrices]['graph']))){
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
        
        if (array_key_exists('rsi', $this->_aIndicators) && (!is_null($this->_aData['RSI']))){
            foreach(array_keys($this->_aData['RSI']) as $n){
                if (!is_null($aPreviousIndicatorsData) && !is_null($this->_aData['RSI']) && !is_null($this->_aData['RSI'][$n]['graph'])){ 
                    $oImageChart->drawLine($x, $this->_aData['RSI'][$n]['graph']
                                            , $iPreviousX
                                            , $aPreviousIndicatorsData['RSI'][$n]['graph']
                                            , array('r' => 0, 'g' => 25, 'b' => 255));
                    if (!$bDrawRSIValueLabel){
                        $oImageChart->drawValueLabel(1, $aPreviousIndicatorsData['RSI'][$n]['graph'], $aPreviousIndicatorsData['RSI'][$n]['real']);
                        $bDrawRSIValueLabel=true;
                    }
                }
            }
        }
        
        if (array_key_exists('sto', $this->_aIndicators) && (!is_null($this->_aData['STO']))){
            if (!is_null($aPreviousIndicatorsData) && !is_null($this->_aData['STO']) && !is_null($this->_aData['STO']['graph']['k'])){ 
                $oImageChart->drawLine($x, $this->_aData['STO']['graph']['k']
                                        , $iPreviousX
                                        , $aPreviousIndicatorsData['STO']['graph']['k']
                                        , array('r' => 0, 'g' => 25, 'b' => 255));
                $oImageChart->drawLine($x, $this->_aData['STO']['graph']['d']
                                        , $iPreviousX
                                        , $aPreviousIndicatorsData['STO']['graph']['d']
                                        , array('r' => 255, 'g' => 25, 'b' => 255));
                if (!$bDrawSTOValueLabel){
                    $oImageChart->drawValueLabel(1, $aPreviousIndicatorsData['STO']['graph']['k'], $aPreviousIndicatorsData['STO']['real']['k']);
                    $bDrawSTOValueLabel=true;
                }
            }
        }
        
        if (array_key_exists('sar', $this->_aIndicators) && (!is_null($this->_aData['SAR']))){
            if (!is_null($this->_aData['SAR']['graph'])){
                $aColor = ($this->_aData['SAR']['real']['trend']=='up') ? array('r'=>0, 'g'=>150, 'b'=>0):array('r'=>200, 'g'=>0, 'b'=>0);
                $oImageChart->drawPoint($x, $this->_aData['SAR']['graph'],$aColor);
            }
        }
        
        $aPreviousIndicatorsData = $this->_aData;
        $iPreviousX = $x;
    }
    
    private function _getSTO(){
        $this->_aData['STO'] = array('real'     => array('k' => NULL
                                                        ,'d' => NULL)
                                    ,'graph'    => array('k' => NULL
                                                        ,'d' => NULL));
        
        $iMinPricesRequired = max($this->_aIndicators['sto']);
        
        if (count(self::$_aRealPrices) >= $iMinPricesRequired){
            $oRealPrice = self::$_aRealPrices[count(self::$_aRealPrices)-1];
            $aNPrices = array_slice(self::$_aRealPrices, (count(self::$_aRealPrices)-$iMinPricesRequired), $iMinPricesRequired);
            
            $nMax = NULL;
            $nMin = NULL;
            
            for($i=$iMinPricesRequired-1; $this->_aIndicators['sto']['n'] >= ($iMinPricesRequired-$i) ; $i--){
                if (is_null($nMax)){
                    $nMax = $aNPrices[$i]->getMax();
                    $nMin = $aNPrices[$i]->getMin();
                }
                else {
                    $nMax = max($nMax, $aNPrices[$i]->getMax());
                    $nMin = min($nMin, $aNPrices[$i]->getMin());
                }
            }
            
            $nFastK = 100*(($oRealPrice->getClose()-$nMin)/($nMax-$nMin));
            
            $nPreviousKSum = $nFastK;
            for($i=$iMinPricesRequired-2; $this->_aIndicators['sto']['k'] >= ($iMinPricesRequired-$i) ; $i--){
                $aPreviousIndicatorsData = $aNPrices[$i]->getIndicators()->getData();
                $nPreviousKSum += $aPreviousIndicatorsData['STO']['real']['k'];
            }
            
            $nFinalK = $nPreviousKSum/$this->_aIndicators['sto']['k'];
            
            $nPreviousKSum = $nFinalK;
            for($i=$iMinPricesRequired-2; $this->_aIndicators['sto']['d'] >= ($iMinPricesRequired-$i) ; $i--){
                $aPreviousIndicatorsData = $aNPrices[$i]->getIndicators()->getData();
                $nPreviousKSum += $aPreviousIndicatorsData['STO']['real']['k'];
            }
            
            $nFinalD = $nPreviousKSum/$this->_aIndicators['sto']['d'];
            
            $this->_aData['STO']['real']['k'] = $nFinalK;
            $this->_aData['STO']['real']['d'] = $nFinalD;
            
            return $this->_aData['STO'];
        }
    }
    
    private function _getRSI($iPrices=NULL){
        $aRSIParameters = (is_null($iPrices)) ? $this->_aIndicators['rsi'] : array($iPrices);
        
        foreach($aRSIParameters as $n){
            if (!array_key_exists('RSI', $this->_aData)){
                $this->_aData['RSI'] = array();
            }
            
            if (!array_key_exists($n, $this->_aData['RSI'])){
                $this->_aData['RSI'][$n] =array( 'real'   => NULL
                                                , 'graph' => NULL
                                                , 'RS_Gain' => NULL
                                                , 'RS_Loss' => NULL);
            }
            
            if (is_null($this->_aData['RSI'][$n]['real'])){
                if (count(self::$_aRealPrices) == $n){
                    $aNPrices = array_slice(self::$_aRealPrices, (count(self::$_aRealPrices)-$n), $n);
                    $nPreviousPrice = NULL;
                    $nLoss = 0;
                    $nGain = 0;
                    foreach($aNPrices as $oRealPrice){
                        if (!is_null($nPreviousPrice)){
                            $nDifference = $nPreviousPrice-$oRealPrice->getClose();
                            if ($nDifference>0){
                                $nGain += $nDifference;
                            }
                            else {
                                $nLoss += -$nDifference;
                            }
                        }
                        $nPreviousPrice = $oRealPrice->getClose();
                    }
                    $this->_aData['RSI'][$n]['RS_Gain'] = $nGain/$n;
                    $this->_aData['RSI'][$n]['RS_Loss'] = $nLoss/$n;
                    if ($this->_aData['RSI'][$n]['RS_Loss'] == 0){
                        $this->_aData['RSI'][$n]['real'] = 100;
                    }
                    else {
                        $this->_aData['RSI'][$n]['real']   = 100 - (100/(1+($this->_aData['RSI'][$n]['RS_Gain']/$this->_aData['RSI'][$n]['RS_Loss'])));
                    }
                }
                elseif(count(self::$_aRealPrices) > $n){
                    $aPreviousRealPriceIndicatorsData = self::$_aRealPrices[count(self::$_aRealPrices)-2]->getIndicators()->getData();
                    $aNPrices = array_slice(self::$_aRealPrices, (count(self::$_aRealPrices)-2), 2);
                    $nDifference = $aNPrices[1]->getClose()-$aNPrices[0]->getClose();
                    $nGainLastPrice = ($nDifference>0) ? $nDifference : 0;
                    $nLossLastPrice = ($nDifference<0) ? -$nDifference : 0;
                    $this->_aData['RSI'][$n]['RS_Gain'] = ((($n-1)*$aPreviousRealPriceIndicatorsData['RSI'][$n]['RS_Gain'])+$nGainLastPrice)/$n;
                    $this->_aData['RSI'][$n]['RS_Loss'] = ((($n-1)*$aPreviousRealPriceIndicatorsData['RSI'][$n]['RS_Loss'])+$nLossLastPrice)/$n;
                    if ($this->_aData['RSI'][$n]['RS_Loss'] == 0){
                        $this->_aData['RSI'][$n]['real'] = 100;
                    }
                    else {
                        $this->_aData['RSI'][$n]['real']   = 100 - (100/(1+($this->_aData['RSI'][$n]['RS_Gain']/$this->_aData['RSI'][$n]['RS_Loss'])));
                    }
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
            if (count(self::$_aRealPrices)>=$n){
                $aNPrices = array_slice(self::$_aRealPrices, (count(self::$_aRealPrices)-$n), $n);
                $nSum = 0;
                $nMA = $this->_getMA($n);
                foreach($aNPrices as $oRealPrice){
                    $nSum += pow(($oRealPrice->getClose() - $nMA),2);
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
        if (!array_key_exists('MA', $this->_aData)){
            $this->_aData['MA'] = array();
        }
        if (is_null($n)){
            $aMAColors = array(  array('r'=>230, 'g'=>120, 'b'=>50)
                                ,array('r'=>200, 'g'=>70, 'b'=>200)
                                ,array('r'=>120, 'g'=>140, 'b'=>160)
                                ,array('r'=>255, 'g'=>255, 'b'=>0));
            reset($aMAColors);
            foreach($this->_aIndicators['ma'] as $iMAPrices){
                if (!array_key_exists($iMAPrices, $this->_aData['MA'])){
                    list($iKey,$aColor) = each($aMAColors);
                    $this->_aData['MA'][$iMAPrices] = array('real' => NULL, 'graph' => NULL, 'color' => $aColor);
                }
                
                if (count(self::$_aRealPrices)>=$iMAPrices){
                    $aRealPrices = array_slice(self::$_aRealPrices, (count(self::$_aRealPrices)-$iMAPrices), $iMAPrices);
                    $nSum = 0;
                    foreach($aRealPrices as $oRealPrice){
                        $nSum += $oRealPrice->getClose();
                    }
                    $this->_aData['MA'][$iMAPrices]['real'] = $nSum/$iMAPrices;
                }
                else {
                    $this->_aData['MA'][$iMAPrices]['real'] = NULL;
                }
            }
            
            return $this->_aData['MA'];
        }
        else {
            if (!array_key_exists($n, $this->_aData['MA'])){
                if (count(self::$_aRealPrices)>=$n){
                    $aRealPrices = array_slice(self::$_aRealPrices, (count(self::$_aRealPrices)-$n), $n);
                    $nSum = 0;
                    foreach($aRealPrices as $oRealPrice){
                        $nSum += $oRealPrice->getClose();
                    }
                    $this->_aData['MA'][$n]['real'] = $nSum/$n;
                }
                else {
                    $this->_aData['MA'][$n]['real'] = NULL;
                }
            }
            return $this->_aData['MA'][$n]['real'];
        }
    }
    
    private function _getSARParabolic(){
        if (count(self::$_aRealPrices) > 1){
            $oRealPrice = self::$_aRealPrices[count(self::$_aRealPrices)-1];
            $oPreviousRealPrice = self::$_aRealPrices[count(self::$_aRealPrices)-2];
            $aPreviousRealPriceIndicatorsData = $oPreviousRealPrice->getIndicators()->getData();

            if (is_null($aPreviousRealPriceIndicatorsData['SAR'])){
                $bTredUp = ($oRealPrice->getClose() > $oPreviousRealPrice->getClose());
                $nAf = $this->_aIndicators['sar']['af0'];
                
                if ($bTredUp){ // New New UP
                    $nSAR = min($oPreviousRealPrice->getMin(),$oRealPrice->getMin());
                    $nEP = max($oPreviousRealPrice->getMax(),$oRealPrice->getMax());
                }
                else {         // New New Down
                    $nSAR = max($oPreviousRealPrice->getMax(),$oRealPrice->getMax());
                    $nEP = min($oPreviousRealPrice->getMin(),$oRealPrice->getMin());
                }
            }
            else {
                $bTredUp = ($aPreviousRealPriceIndicatorsData['SAR']['real']['trend'] == 'up');
                
                $bNewTrend = ($bTredUp && ($oRealPrice->getMin() < $aPreviousRealPriceIndicatorsData['SAR']['real']['psar']))
                          || (!$bTredUp && ($oRealPrice->getMax() > $aPreviousRealPriceIndicatorsData['SAR']['real']['psar']));
                
                if ($bNewTrend){
                    if (!$bTredUp){ // New UP
                        $nEP = max($aPreviousRealPriceIndicatorsData['SAR']['real']['ep'], $oRealPrice->getMax());
                        $nSAR = min($aPreviousRealPriceIndicatorsData['SAR']['real']['ep'], $oRealPrice->getMin());
                    }
                    else {         // New Down
                        $nEP = min($aPreviousRealPriceIndicatorsData['SAR']['real']['ep'], $oRealPrice->getMin());
                        $nSAR = max($aPreviousRealPriceIndicatorsData['SAR']['real']['ep'], $oRealPrice->getMax());
                    }
                    $nAf = $this->_aIndicators['sar']['af0'];
                    $bTredUp = !$bTredUp;
                }
                else { // No new trend
                    $nAf = $aPreviousRealPriceIndicatorsData['SAR']['real']['af'];
                    if ($bTredUp){
                        $nEP = max($aPreviousRealPriceIndicatorsData['SAR']['real']['ep'], $oRealPrice->getMax());
                        $nAf += ($nEP > $aPreviousRealPriceIndicatorsData['SAR']['real']['ep']) ? $this->_aIndicators['sar']['afX']:0;
                    }
                    else {
                        $nEP = min($aPreviousRealPriceIndicatorsData['SAR']['real']['ep'], $oRealPrice->getMin());
                        $nAf += ($nEP < $aPreviousRealPriceIndicatorsData['SAR']['real']['ep']) ? $this->_aIndicators['sar']['afX']:0;
                    }
                    
                    if ($nAf > $this->_aIndicators['sar']['afMax']){
                        $nAf = $this->_aIndicators['sar']['afMax'];
                    }
                    
                    $nSAR = $aPreviousRealPriceIndicatorsData['SAR']['real']['psar'] + $nAf*($nEP - $aPreviousRealPriceIndicatorsData['SAR']['real']['psar']);
                    
                    if ($bTredUp){
                        $nMinLast2Prices = min($oPreviousRealPrice->getMin(),$oRealPrice->getMin());
                        if ($nSAR > $nMinLast2Prices){
                            $nSAR = $nMinLast2Prices;
                        }
                    }
                    else {
                        $nMaxLast2Prices = max($oPreviousRealPrice->getMax(),$oRealPrice->getMax());
                        if ($nSAR < $nMaxLast2Prices){
                            $nSAR = $nMaxLast2Prices;
                        }
                    }
                }
            }
            
            $this->_aData['SAR'] = array('real'     => array('trend'    => ($bTredUp ? 'up' : 'down')
                                                            ,'af'       => $nAf
                                                            ,'ep'       => $nEP
                                                            ,'psar'     => $nSAR)
                                        ,'graph'    => NULL);
            
            return $this->_aData['SAR'];
        }
        else {
            return NULL;
        }
    }
}
?>