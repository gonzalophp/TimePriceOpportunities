<?php
require_once('class/chart/realchart.class.php');

class data_analysis {
    private $_aPrices;
    
    public function data_analysis($aPrices){
        $this->_aPrices = $aPrices;
        $this->_aStats = array('sequence'   => array('letters'  => ''
                                                    ,'trades'   => array())
                              ,'total'      => array('gains'   => 0
                                                    ,'loss'    => 0
                                                    ,'profit'  => 0)
                              ,'max'        => array( 'gains'            => 0
                                                    ,'loss'             => 0
                                                    ,'gains_in_a_row'   => 0
                                                    ,'losses_in_a_row'  => 0)
                              ,'avg'        => array( 'gains'            => 0
                                                    ,'loss'             => 0
                                                    ,'failures'         => 0
                                                    ,'comp_gains'       => 0
                                                    ,'comp_losses'      => 0
                                                    ,'gains_in_a_row'   => 0
                                                    ,'losses_in_a_row'  => 0));
    }
    
    public function run($sStrategy){
        foreach($this->_aPrices as $oRealPrice){
            $oRealPrice->clearTrade();
        }
        $sMethod = '_'.$sStrategy;
        $this->$sMethod();
        $this->_buildStats();
    }
    
    public function getStats(){
        return $this->_aStats;
    }
    
    private function _buildStats() {
        $aCurrentTrade = array('dir' => NULL, 'price' => NULL);
        $iConsecutive = 0;
        foreach($this->_aPrices as $sDateTime=>$oRealPrice){
            $aTrades = $oRealPrice->getTrade();
            if (!empty($aTrades)){
                foreach($aTrades as $aTrade){
                    $iTrade = $aTrade['dir'];
                    
                    if (is_null($iTrade)){
                        //Do nothing
                    }
                    elseif (($iTrade==realPrice::TRADE_SELL) || ($iTrade==realPrice::TRADE_BUY)){
                        $aCurrentTrade = $aTrade;
                    }
                    elseif ($iTrade===realPrice::TRADE_CLOSE){
                        $this->_aStats['sequence']['trades'][] = array('datetime'=> $sDateTime
                                                                      ,'dir'    => $aCurrentTrade['dir']
                                                                      ,'open'   => $aCurrentTrade['price']
                                                                      ,'close'  => $aTrade['price']);
                        $bTradeWon = (($aCurrentTrade['dir']==realPrice::TRADE_BUY) && ($aTrade['price'] > $aCurrentTrade['price']))
                                    || (($aCurrentTrade['dir']==realPrice::TRADE_SELL) && ($aTrade['price'] < $aCurrentTrade['price']));
                        $this->_aStats['sequence']['letters'] .= $bTradeWon ? 'G' : 'L';
                        if ($bTradeWon && ($iConsecutive>0)){       // won
                            $iConsecutive++;
                            $this->_aStats['max']['gains_in_a_row'] = max($this->_aStats['max']['gains_in_a_row'],$iConsecutive);
                        }
                        elseif (!$bTradeWon && ($iConsecutive<0)){   //loss
                            $iConsecutive--;
                            $this->_aStats['max']['losses_in_a_row'] = max($this->_aStats['max']['losses_in_a_row'],abs($iConsecutive));
                        }
                        else {
                            $iConsecutive = $bTradeWon ? 1:-1;
                            if ($bTradeWon){
                                $this->_aStats['max']['gains_in_a_row'] = max($this->_aStats['max']['gains_in_a_row'],$iConsecutive);
                            }
                            else {
                                $this->_aStats['max']['losses_in_a_row'] = max($this->_aStats['max']['losses_in_a_row'],abs($iConsecutive));
                            }
                        }

                        $nDifference = abs($aTrade['price'] - $aCurrentTrade['price']);
                        if ($bTradeWon){
                            $this->_aStats['total']['gains'] += $nDifference;
                            $this->_aStats['max']['gains'] = max($this->_aStats['max']['gains'],$nDifference);
                        }
                        else {
                            $this->_aStats['total']['loss'] += $nDifference;
                            $this->_aStats['max']['loss'] = max($this->_aStats['max']['loss'],$nDifference);
                        }

                        $aCurrentTrade = array('dir'   => NULL
                                    , 'price' => NULL);
                    }
                }
            }
            else {
                $iTrade = NULL;
            }
        }
        
        $this->_aStats['total']['profit'] = $this->_aStats['total']['gains']-$this->_aStats['total']['loss'];
        
        $iTimesWon = substr_count($this->_aStats['sequence']['letters'], 'G');
        $iTimesLost = substr_count($this->_aStats['sequence']['letters'], 'L');
        if ($iTimesWon>0){
            $this->_aStats['avg']['gains'] = $this->_aStats['total']['gains']/$iTimesWon;
        }
        if ($iTimesLost>0){
            $this->_aStats['avg']['loss'] = $this->_aStats['total']['loss']/$iTimesLost;
        }
        
        if (strlen($this->_aStats['sequence']['letters'])>0){
            $this->_aStats['avg']['failures'] = $iTimesLost/($iTimesLost+$iTimesWon);
        }
        else {
            $this->_aStats['avg']['failures'] = 0;
        }
        
        $this->_aStats['avg']['ratio_gains_losses'] = 0;
        
        if ($this->_aStats['avg']['failures']>0){
            $this->_aStats['avg']['comp_gains'] = $this->_aStats['avg']['gains']*(1-$this->_aStats['avg']['failures']);
            $this->_aStats['avg']['comp_losses'] = $this->_aStats['avg']['loss']*$this->_aStats['avg']['failures'];
            
            if ($this->_aStats['avg']['comp_losses']>0){
                $this->_aStats['avg']['ratio_gains_losses'] = $this->_aStats['avg']['comp_gains']/$this->_aStats['avg']['comp_losses'];
            }
        }
        
        $aDiffLoss = array_diff(explode(' ', str_replace('G',' ',$this->_aStats['sequence']['letters'])), array(''));
        $sDiffLoss = implode('',$aDiffLoss);
        if (count($aDiffLoss)>0){
            $this->_aStats['avg']['losses_in_a_row'] = strlen($sDiffLoss)/count($aDiffLoss);
        }
        else {
            $this->_aStats['avg']['losses_in_a_row'] = 0;
        }
        
        $aDiffGain = array_diff(explode(' ', str_replace('L',' ',$this->_aStats['sequence']['letters'])), array(''));
        $sDiffGain = implode('',$aDiffGain);
        if (count($aDiffGain)>0){
            $this->_aStats['avg']['gains_in_a_row'] = strlen($sDiffGain)/count($aDiffGain);
        }
        else {
            $this->_aStats['avg']['gains_in_a_row'] = 0;
        }
    }
    
    /*
     * 1. Continue the trend once the price goes farther bollinger band
     * 2. Close when bar completely crosses MA20 or touches the opposite bollinger band
     * 
     *  FAILURE: 75%
     */
    private function _strategy1(){
        $oPreviousRealPrice = NULL;
        $iTrading = realPrice::TRADE_CLOSE;
        foreach($this->_aPrices as $iDateTime=>$oRealPrice){
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            if (!is_null($aIndicatorsData['BOL']['real']) && !is_null($oPreviousRealPrice)){
                if ($iTrading==realPrice::TRADE_CLOSE){
                    if (($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']) && ($oRealPrice->getClose()>$oPreviousRealPrice->getClose())){
                        $oRealPrice->addTrade(realPrice::TRADE_BUY,$oRealPrice->getClose());
                        $iTrading=realPrice::TRADE_BUY;
                    }
                    
                    if (($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down']) && ($oRealPrice->getClose()<$oPreviousRealPrice->getClose())){
                        $oRealPrice->addTrade(realPrice::TRADE_SELL,$oRealPrice->getClose());
                        $iTrading=realPrice::TRADE_SELL;
                    }
                }
                
                if ($iTrading!=realPrice::TRADE_CLOSE){
                    if (($iTrading==realPrice::TRADE_BUY) && (($oRealPrice->getMax()<$aIndicatorsData['MA'][20]['real']) || ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down']))){
                        $iTrading=realPrice::TRADE_CLOSE;
                        $oRealPrice->addTrade(realPrice::TRADE_CLOSE,$oRealPrice->getClose());
                    }
                    if (($iTrading==realPrice::TRADE_SELL) && (($oRealPrice->getMin()>$aIndicatorsData['MA'][20]['real']) || ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']))){
                        $iTrading=realPrice::TRADE_CLOSE;
                        $oRealPrice->addTrade(realPrice::TRADE_CLOSE,$oRealPrice->getClose());
                    }
                }
            }
            
            $oPreviousRealPrice = $oRealPrice;
        }
    }
    
    
    /**
     * 1. Open opposite when touches bollinger band and length of the trail is bigger than length of the body
     * 2. Close when touches the opposite bollinger band, price surpass limit price at the opening bar (losing money) or price goes below/above previous bar
     * 
     * FAILURE 52%
     */
    private function _strategy2(){
        $oPreviousRealPrice = NULL;
        $iTrading = realPrice::TRADE_CLOSE;
        $nLimitPrice = NULL;
        foreach($this->_aPrices as $iDateTime=>$oRealPrice){
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            if (!is_null($aIndicatorsData['BOL']['real']) && !is_null($oPreviousRealPrice)){
                $bGreenBar = ($oRealPrice->getClose()>$oRealPrice->getOpen());
                
                $nTrail = ($bGreenBar) ? $oRealPrice->getMax()-$oRealPrice->getClose():$oRealPrice->getClose()-$oRealPrice->getMin();
                $nBody = abs($oRealPrice->getOpen()-$oRealPrice->getClose());
                $bBollingerCrossed = ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down']) || ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']);
                
                if (($iTrading!=realPrice::TRADE_CLOSE) && ($nBody>$nTrail)){
                    if (($iTrading==realPrice::TRADE_BUY) && 
                            (($oRealPrice->getMin()<$nLimitPrice) // Below limit and Small trail - LOSS
                            || ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']) // Close when touches the opposite bollinger
                            || ($oPreviousRealPrice->getMin()>$oRealPrice->getMin()))){ // Close when min goes below previous min
                        $iTrading=realPrice::TRADE_CLOSE;
                        $oRealPrice->addTrade(realPrice::TRADE_CLOSE,$oRealPrice->getClose());
                    }
                    if (($iTrading==realPrice::TRADE_SELL) && 
                            (($oRealPrice->getMax()>$nLimitPrice) 
                            || ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down'])
                            || ($oPreviousRealPrice->getMax()<$oRealPrice->getMax()))){
                        $iTrading=realPrice::TRADE_CLOSE;
                        $oRealPrice->addTrade(realPrice::TRADE_CLOSE,$oRealPrice->getClose());
                    }
                }
                    
                if (($iTrading==realPrice::TRADE_CLOSE) && ($nBody<$nTrail) && $bBollingerCrossed){
                    if ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']){
                        $iTrading=realPrice::TRADE_SELL;
                        $nLimitPrice = $oRealPrice->getMax();
                        $oRealPrice->addTrade(realPrice::TRADE_SELL,$oRealPrice->getClose());
                    }
                    elseif ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down']){
                        $iTrading=realPrice::TRADE_BUY;
                        $nLimitPrice = $oRealPrice->getMin();
                        $oRealPrice->addTrade(realPrice::TRADE_BUY,$oRealPrice->getClose());
                    }
                }
            }
            
            $oPreviousRealPrice = $oRealPrice;
        }
    }
    
    /**
     * Follow parabolic SAR 
     * 
     * Failures 59%
     */
    private function _strategy3(){
        $oPreviousRealPrice = NULL;
        $aPreviousIndicatorsData = NULL;
        $bTrading = false;
        $iTrading = realPrice::TRADE_CLOSE;

        $aTrade = array('dir' => NULL, 'price'=>NULL);
        foreach($this->_aPrices as $iDateTime=>$oRealPrice){
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            
            if (!is_null($aIndicatorsData['SAR']) && !is_null($oPreviousRealPrice) && !is_null($aPreviousIndicatorsData) && !is_null($aPreviousIndicatorsData['SAR'])){
                $bCurrentTrendDown = ($aIndicatorsData['SAR']['real']['trend']=='down');
                $bPreviousTrendDown = ($aPreviousIndicatorsData['SAR']['real']['trend']=='down');
                if ($bCurrentTrendDown ^ $bPreviousTrendDown){
                    $bOutOfPrice = (($aPreviousIndicatorsData['SAR']['real']['psar']>$oRealPrice->getMax())
                                     || ($aPreviousIndicatorsData['SAR']['real']['psar']<$oRealPrice->getMin()));
                            
                    if ($bOutOfPrice) {
                        $nTradePrice = $oRealPrice->getClose();
                    }
                    else {
                        $nTradePrice = $aPreviousIndicatorsData['SAR']['real']['psar'];
                    }
                    
                    if ($bTrading) {
                        $oRealPrice->addTrade(realPrice::TRADE_CLOSE, $nTradePrice);
                    }
                    if ($bCurrentTrendDown){
                        $oRealPrice->addTrade(realPrice::TRADE_SELL, $nTradePrice);
                    }
                    else {
                        $oRealPrice->addTrade(realPrice::TRADE_BUY, $nTradePrice);
                    }
                    $bTrading = true;
                }
                else {
                    //Do nothing, no trend change
                }
            }
            $oPreviousRealPrice = $oRealPrice;
            $aPreviousIndicatorsData = $oPreviousRealPrice->getIndicators()->getData();
        }
    }
    
    /**
     * Open when MA10 and MA20 goes the same direction, and the same direction of the opening price
     * Close when price touches MA20 again
     */
    private function _strategy4(){
        $oPreviousRealPrice = NULL;
        $aPreviousPriceIndicatorsData = NULL;
        
        $iPreviousDay = NULL;
        
        $nPriceAtStartOfDay = NULL;
        $nPreviousDayClosePrice = NULL;
        $iTrading = NULL;
        foreach($this->_aPrices as $iDateTime=>$oRealPrice){
            $iCurrentDay=date('d',$iDateTime);
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            
            if (!is_null($iPreviousDay)){
                if ($iPreviousDay!=$iCurrentDay){
                    $nPriceAtStartOfDay = $oRealPrice->getOpen();
                    $nPreviousDayClosePrice = $oPreviousRealPrice->getClose();
                }
                if (!is_null($aPreviousPriceIndicatorsData) 
                        && !is_null($nPriceAtStartOfDay)
                        && !is_null($aIndicatorsData['MA'][20]['real'])
                        && !is_null($aPreviousPriceIndicatorsData['MA'][20]['real'])){
                    
                    if(is_null($iTrading)){
                        if (($aIndicatorsData['MA'][20]['real']>$aPreviousPriceIndicatorsData['MA'][20]['real'])
                        && ($aIndicatorsData['MA'][10]['real']>$aPreviousPriceIndicatorsData['MA'][10]['real'])
                                && ($nPriceAtStartOfDay>$nPreviousDayClosePrice)
//                                && ((($nPriceAtStartOfDay-$nPreviousDayClosePrice)/$nPriceAtStartOfDay)< 0.001) 
                                ){
                            $oRealPrice->addTrade(realPrice::TRADE_BUY, $oRealPrice->getClose());
                            $iTrading = realPrice::TRADE_BUY;
                        }    
                        elseif (($aIndicatorsData['MA'][20]['real']<$aPreviousPriceIndicatorsData['MA'][20]['real'])
                        && ($aIndicatorsData['MA'][10]['real']<$aPreviousPriceIndicatorsData['MA'][10]['real'])
                                && ($nPriceAtStartOfDay<$nPreviousDayClosePrice)
//                                && ((($nPreviousDayClosePrice-$nPriceAtStartOfDay)/$nPreviousDayClosePrice)<0.001)
                                ){
                            $oRealPrice->addTrade(realPrice::TRADE_SELL, $oRealPrice->getClose());
                            $iTrading = realPrice::TRADE_SELL;
                        }
                    }
                    else {
                            if ($iTrading == realPrice::TRADE_BUY){
                                if ($oRealPrice->getMin()<$aIndicatorsData['MA'][20]['real']){
                                    
                                    if ($iPreviousDay!=$iCurrentDay){
                                         $oPreviousRealPrice->addTrade(realPrice::TRADE_CLOSE, $oPreviousRealPrice->getClose());
                                    }
                                    else {
                                        if ($oRealPrice->getMax()<$aIndicatorsData['MA'][20]['real']){ 
                                            $nClosePrice = $oRealPrice->getMax();
                                        }
                                        else {
                                            $nClosePrice = $aIndicatorsData['MA'][20]['real'];
                                        }

                                        $oRealPrice->addTrade(realPrice::TRADE_CLOSE,$nClosePrice);
                                    }
                                    
                                    $iTrading = NULL;
                                }
                            }
                            else {
                                if ($oRealPrice->getMax()>$aIndicatorsData['MA'][20]['real']){
                                    
                                    if ($iPreviousDay!=$iCurrentDay){
                                         $oPreviousRealPrice->addTrade(realPrice::TRADE_CLOSE, $oPreviousRealPrice->getClose());
                                    }
                                    else {
                                        if ($oRealPrice->getMin()>$aIndicatorsData['MA'][20]['real']){ 
                                            $nClosePrice = $oRealPrice->getMin();
                                        }
                                        else {
                                            $nClosePrice = $aIndicatorsData['MA'][20]['real'];
                                        }

                                        $oRealPrice->addTrade(realPrice::TRADE_CLOSE,$nClosePrice);
                                    }
                                    
                                    $iTrading = NULL;
                                }
                            }
                    }
                }
            }
            
            $oPreviousRealPrice = $oRealPrice;
            $aPreviousPriceIndicatorsData = $aIndicatorsData;
            $iPreviousDay = $iCurrentDay;
        }
    }
    
    
    /**
     *RSI 14
     * 
     * AVG
        Gains 50.16
        Loss 101.16
        Failures 0.27
        Composite Gains 36.39
        Composite Losses 27.77
        Ratio Gains/Loss 1.31
        Losses in a row 1.27
        Gains in a row 3.36
     * 
     * sell  48-60
     * buy 40-52
     * AVG
Gains 45.43
Loss 102.84
Failures 0.23
Composite Gains 34.83
Composite Losses 24.00
Ratio Gains/Loss 1.45
Losses in a row 1.40
Gains in a row 4.18
     * 
     * 
     * sell     49-60
     * buy    40-51
     * AVG
Gains 44.48
Loss 106.46
Failures 0.23
Composite Gains 34.10
Composite Losses 24.84
Ratio Gains/Loss 1.37
Losses in a row 1.40
Gains in a row 4.18
     *  
     * 
     * sell   46-60
     * buy  40-54
     * AVG
Gains 48.60
Loss 91.10
Failures 0.28
Composite Gains 34.84
Composite Losses 25.78
Ratio Gains/Loss 1.35
Losses in a row 1.36
Gains in a row 3.45
     * 
     * 
     * Opening price improves 15% gains
     */
    private function _strategy5(){
        $oPreviousRealPrice = NULL;
        $aPreviousPriceIndicatorsData = NULL;
        
        $iPreviousDay = NULL;
        
        $nPriceAtStartOfDay = NULL;
        $nPreviousDayClosePrice = NULL;
        $iTrading = NULL;
        
        foreach($this->_aPrices as $iDateTime=>$oRealPrice){
            $iCurrentDay=date('d',$iDateTime);
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            
            if (!is_null($iPreviousDay)){
                if ($iPreviousDay!=$iCurrentDay){
                    $nPriceAtStartOfDay = $oRealPrice->getOpen();
                    $nPreviousDayClosePrice = $oPreviousRealPrice->getClose();
                }
                
                if (!is_null($aPreviousPriceIndicatorsData) && !is_null($aPreviousPriceIndicatorsData['RSI']) && !is_null($aPreviousPriceIndicatorsData['RSI'][14]['real'])){
                    if(is_null($iTrading)){
                        if (($aIndicatorsData['RSI'][14]['real']>60) && ($nPriceAtStartOfDay<$nPreviousDayClosePrice)){
                            $oRealPrice->addTrade(realPrice::TRADE_SELL, $oRealPrice->getClose());
                            $iTrading = realPrice::TRADE_SELL;    
                        }
                        elseif (($aIndicatorsData['RSI'][14]['real']<40) && ($nPriceAtStartOfDay>$nPreviousDayClosePrice)){
                            $oRealPrice->addTrade(realPrice::TRADE_BUY, $oRealPrice->getClose());
                            $iTrading = realPrice::TRADE_BUY;
                        }
                    }
                    else {
                        if ((($iTrading == realPrice::TRADE_SELL) && ($aIndicatorsData['RSI'][14]['real']<46))
                            || (($iTrading == realPrice::TRADE_BUY) && ($aIndicatorsData['RSI'][14]['real']>54))){
                            $oRealPrice->addTrade(realPrice::TRADE_CLOSE, $oRealPrice->getClose());
                            $iTrading = NULL;
                        }
                    }
                }
            }
            
            $oPreviousRealPrice = $oRealPrice;
            $aPreviousPriceIndicatorsData = $aIndicatorsData;
            $iPreviousDay = $iCurrentDay;
        }
    }
    
    /**
     *AVG
Gains 67.66
Loss 108.37
Failures 0.33
Composite Gains 45.11
Composite Losses 36.12
Ratio Gains/Loss 1.25
Losses in a row 1.33
Gains in a row 2.67 
     */
    private function _strategy6(){
        $oPreviousRealPrice = NULL;
        $aPreviousPriceIndicatorsData = NULL;
        
        $iPreviousDay = NULL;
        
        $nPriceAtStartOfDay = NULL;
        $nPreviousDayClosePrice = NULL;
        $iTrading = NULL;
        
        foreach($this->_aPrices as $iDateTime=>$oRealPrice){
            $iCurrentDay=date('d',$iDateTime);
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            
            if (!is_null($iPreviousDay)){
                if ($iPreviousDay!=$iCurrentDay){
                    $nPriceAtStartOfDay = $oRealPrice->getOpen();
                    $nPreviousDayClosePrice = $oPreviousRealPrice->getClose();
                }
                
                if (!is_null($aPreviousPriceIndicatorsData) && !is_null($aPreviousPriceIndicatorsData['STO'])){
                    if(is_null($iTrading)){
                        if (($aIndicatorsData['STO']['real']['d']>$aPreviousPriceIndicatorsData['STO']['real']['d'])
                                && ($aIndicatorsData['STO']['real']['d']<25)
//                                && ($nPriceAtStartOfDay<$nPreviousDayClosePrice)
                                ){
                            $oRealPrice->addTrade(realPrice::TRADE_BUY, $oRealPrice->getClose());
                            $iTrading = realPrice::TRADE_BUY;
                            
                        }
                        elseif (($aIndicatorsData['STO']['real']['d']<$aPreviousPriceIndicatorsData['STO']['real']['d'])
                                && ($aIndicatorsData['STO']['real']['d']>75)
//                                && ($nPriceAtStartOfDay>$nPreviousDayClosePrice)
                                ){
                            $oRealPrice->addTrade(realPrice::TRADE_SELL, $oRealPrice->getClose());
                            $iTrading = realPrice::TRADE_SELL;    
                        }
                    }
                    else {
                        if ((($iTrading == realPrice::TRADE_SELL) && ($aIndicatorsData['STO']['real']['d']<32))
//                                || (($iTrading == realPrice::TRADE_SELL) && ($oRealPrice->getMax()>$oPreviousRealPrice->getMax()))
                            || (($iTrading == realPrice::TRADE_BUY) && ($aIndicatorsData['STO']['real']['d']>68))
//                                || (($iTrading == realPrice::TRADE_BUY) && ($oRealPrice->getMin()<$oPreviousRealPrice->getMin()))
                                ){
                            $oRealPrice->addTrade(realPrice::TRADE_CLOSE, $oRealPrice->getClose());
                            $iTrading = NULL;
                        }
                    }
                }
            }
            
            $oPreviousRealPrice = $oRealPrice;
            $aPreviousPriceIndicatorsData = $aIndicatorsData;
            $iPreviousDay = $iCurrentDay;
        }
    }
    
    
    private function _strategy7(){
        $oPreviousRealPrice = NULL;
        $aPreviousPriceIndicatorsData = NULL;
        
        $iPreviousDay = NULL;
        
        $nPriceAtStartOfDay = NULL;
        $nPreviousDayClosePrice = NULL;
        $iTrading = NULL;
        
        foreach($this->_aPrices as $sDateTime=>$oRealPrice){
            $iCurrentDay=substr($sDateTime,11,2);
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            
            if (!is_null($iPreviousDay)){
                if ($iPreviousDay!=$iCurrentDay){
                    $nPriceAtStartOfDay = $oRealPrice->getOpen();
                    $nPreviousDayClosePrice = $oPreviousRealPrice->getClose();
                }
            }
            
            if (!is_null($nPreviousDayClosePrice)){
                if(is_null($iTrading)){
                    if ($iPreviousDay!=$iCurrentDay){
                        if ($nPriceAtStartOfDay>$nPreviousDayClosePrice){
                            $oRealPrice->addTrade(realPrice::TRADE_BUY, $oRealPrice->getClose());
                            $iTrading = realPrice::TRADE_BUY;
                        }
                        else {
                            $oRealPrice->addTrade(realPrice::TRADE_SELL, $oRealPrice->getClose());
                            $iTrading = realPrice::TRADE_SELL;
                        }
                    }
                }
                else {
                    if ($iPreviousDay!=$iCurrentDay){
                        $oPreviousRealPrice->addTrade(realPrice::TRADE_CLOSE, $oPreviousRealPrice->getClose());
                        $iTrading = NULL;
                    }
                }
            }
            
            $oPreviousRealPrice = $oRealPrice;
            $aPreviousPriceIndicatorsData = $aIndicatorsData;
            $iPreviousDay = $iCurrentDay;
        }
    }
    
    private function _strategy8(){
        $oPreviousRealPrice = NULL;
        $aPreviousPriceIndicatorsData = NULL;
        
        $iPreviousDay = NULL;
        
        $nPriceAtStartOfDay = NULL;
        $nPreviousDayClosePrice = NULL;
        $iTrading = NULL;
        
        foreach($this->_aPrices as $sDateTime=>$oRealPrice){
            $iCurrentDay=substr($sDateTime,11,2);
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            
            if (!is_null($iPreviousDay)){ echo "$iPreviousDay ---- $iCurrentDay <br>";
                if ($iPreviousDay!=$iCurrentDay){
                    $nPriceAtStartOfDay = $oRealPrice->getOpen();
                    $nPreviousDayClosePrice = $oPreviousRealPrice->getClose();
                }
            }
            
            
            
            $oPreviousRealPrice = $oRealPrice;
            $aPreviousPriceIndicatorsData = $aIndicatorsData;
            $iPreviousDay = $iCurrentDay;
        }
    }
}
?>
