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
                                                    ,'gains_in_a_row'   => 0
                                                    ,'losses_in_a_row'  => 0));
    }
    
    public function run($sStrategy){
        foreach($this->_aPrices as $oRealPrice){
            $oRealPrice->setTrade(NULL);
        }
        $sMethod = '_'.$sStrategy;
        $this->$sMethod();
        $this->_buildStats();
    }
    
    public function getStats(){
        return $this->_aStats;
    }
    
    private function _buildStats() {
        $aTrade = array('dir' => NULL, 'price' => NULL);
        $iConsecutive = 0;
        foreach($this->_aPrices as $iDateTime=>$oRealPrice){
            $iTrade = $oRealPrice->getTrade();
            if (is_null($iTrade)){
                //Do nothing
            }
            elseif (($iTrade==realPrice::TRADE_SELL) || ($iTrade==realPrice::TRADE_BUY)){
                $aTrade = array('dir'   => $iTrade
                              , 'price' => $oRealPrice->getClose());
            }
            elseif ($iTrade===realPrice::TRADE_CLOSE){
                $this->_aStats['sequence']['trades'][] = array('dir'  => $aTrade['dir']
                                                            ,'open' => $aTrade['price']
                                                            , 'close'=> $oRealPrice->getClose());
                $bTradeWon = (($aTrade['dir']==realPrice::TRADE_BUY) && ($oRealPrice->getClose() > $aTrade['price']))
                              || (($aTrade['dir']==realPrice::TRADE_SELL) && ($oRealPrice->getClose() < $aTrade['price']));
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

                $nDifference = abs($oRealPrice->getClose() - $aTrade['price']);
                if ($bTradeWon){
                    $this->_aStats['total']['gains'] += $nDifference;
                    $this->_aStats['max']['gains'] = max($this->_aStats['max']['gains'],$nDifference);
                }
                else {
                    $this->_aStats['total']['loss'] += $nDifference;
                    $this->_aStats['max']['loss'] = max($this->_aStats['max']['loss'],$nDifference);
                }

                $aTrade = array('dir'   => NULL
                              , 'price' => NULL);
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
                        $oRealPrice->setTrade(realPrice::TRADE_BUY);
                        $iTrading=realPrice::TRADE_BUY;
                    }
                    
                    if (($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down']) && ($oRealPrice->getClose()<$oPreviousRealPrice->getClose())){
                        $oRealPrice->setTrade(realPrice::TRADE_SELL);
                        $iTrading=realPrice::TRADE_SELL;
                    }
                }
                
                if ($iTrading!=realPrice::TRADE_CLOSE){
                    if (($iTrading==realPrice::TRADE_BUY) && (($oRealPrice->getMax()<$aIndicatorsData['MA'][20]['real']) || ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down']))){
                        $iTrading=realPrice::TRADE_CLOSE;
                        $oRealPrice->setTrade(realPrice::TRADE_CLOSE);
                    }
                    if (($iTrading==realPrice::TRADE_SELL) && (($oRealPrice->getMin()>$aIndicatorsData['MA'][20]['real']) || ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']))){
                        $iTrading=realPrice::TRADE_CLOSE;
                        $oRealPrice->setTrade(realPrice::TRADE_CLOSE);
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
                        $oRealPrice->setTrade(realPrice::TRADE_CLOSE);
                    }
                    if (($iTrading==realPrice::TRADE_SELL) && 
                            (($oRealPrice->getMax()>$nLimitPrice) 
                            || ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down'])
                            || ($oPreviousRealPrice->getMax()<$oRealPrice->getMax()))){
                        $iTrading=realPrice::TRADE_CLOSE;
                        $oRealPrice->setTrade(realPrice::TRADE_CLOSE);
                    }
                }
                    
                if (($iTrading==realPrice::TRADE_CLOSE) && ($nBody<$nTrail) && $bBollingerCrossed){
                    if ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']){
                        $iTrading=realPrice::TRADE_SELL;
                        $nLimitPrice = $oRealPrice->getMax();
                        $oRealPrice->setTrade(realPrice::TRADE_SELL);
                    }
                    elseif ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down']){
                        $iTrading=realPrice::TRADE_BUY;
                        $nLimitPrice = $oRealPrice->getMin();
                        $oRealPrice->setTrade(realPrice::TRADE_BUY);
                    }
                }
            }
            
            $oPreviousRealPrice = $oRealPrice;
        }
    }
    
    /**
     * Follow parabolic SAR 
     */
    private function _strategy3(){
        $oPreviousRealPrice = NULL;
        $iTrading = realPrice::TRADE_CLOSE;

        foreach($this->_aPrices as $iDateTime=>$oRealPrice){
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            
            if (!is_null($aIndicatorsData['SAR']) && !is_null($oPreviousRealPrice)){
                $bDown = ($aIndicatorsData['SAR']['real']['trend']=='down');
//                if (($iTrading!=realPrice::TRADE_CLOSE) && ($nBody>$nTrail)){
//                    if (($iTrading==realPrice::TRADE_BUY) && 
//                            (($oRealPrice->getMin()<$nLimitPrice) // Below limit and Small trail - LOSS
//                            || ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']) // Close when touches the opposite bollinger
//                            || ($oPreviousRealPrice->getMin()>$oRealPrice->getMin()))){ // Close when min goes below previous min
//                        $iTrading=realPrice::TRADE_CLOSE;
//                        $oRealPrice->setTrade(realPrice::TRADE_CLOSE);
//                    }
//                    if (($iTrading==realPrice::TRADE_SELL) && 
//                            (($oRealPrice->getMax()>$nLimitPrice) 
//                            || ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down'])
//                            || ($oPreviousRealPrice->getMax()<$oRealPrice->getMax()))){
//                        $iTrading=realPrice::TRADE_CLOSE;
//                        $oRealPrice->setTrade(realPrice::TRADE_CLOSE);
//                    }
//                }
//                    
//                if (($iTrading==realPrice::TRADE_CLOSE) && ($nBody<$nTrail) && $bBollingerCrossed){
//                    if ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up']){
//                        $iTrading=realPrice::TRADE_SELL;
//                        $nLimitPrice = $oRealPrice->getMax();
//                        $oRealPrice->setTrade(realPrice::TRADE_SELL);
//                    }
//                    elseif ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down']){
//                        $iTrading=realPrice::TRADE_BUY;
//                        $nLimitPrice = $oRealPrice->getMin();
//                        $oRealPrice->setTrade(realPrice::TRADE_BUY);
//                    }
//                }
            }
            
            $oPreviousRealPrice = $oRealPrice;
        }
    }
}
?>
