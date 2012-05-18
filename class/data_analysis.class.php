<?php
require_once('class/chart/realchart.class.php');

class data_analysis {
    private $_aPrices;
    
    public function data_analysis($aPrices){
        $this->_aPrices = $aPrices;
    }
    
    public function strategy1(){
        $oPreviousPrice = NULL;
        $bTrading=false;
        $aBuy = array();
        foreach($this->_aPrices as $oRealPrice){
            $aIndicatorsData = $oRealPrice->getIndicators()->getData();
            if (!is_null($aIndicatorsData['BOL']['real']) && !is_null($oPreviousPrice)){
                if (!$bTrading && ($oRealPrice->getMax()>$aIndicatorsData['BOL']['real']['up'])){
                    if ($oRealPrice->getClose()>$oPreviousPrice->getClose()){
                        $oRealPrice->setTrade(realPrice::TRADE_BUY);
                        $bTrading=true;
                        $aBuy[]=$oRealPrice->getClose();
                    }
                }
                
                if ($bTrading){
                    if (($oRealPrice->getMax()<$aIndicatorsData['MA'][20]['real']) || ($oRealPrice->getMin()<$aIndicatorsData['BOL']['real']['down'])){
                        $bTrading     = false;
                        $aBuy[]=$oRealPrice->getClose();
                        $oRealPrice->setTrade(realPrice::TRADE_CLOSE);
                    }
                }
            }
            
            $oPreviousPrice = $oRealPrice;
//            $iRand = rand(1,20);
//            if ($iRand==20){
//                $oRealPrice->setTrade(realPrice::TRADE_BUY);
//            }
//            elseif ($iRand==1){
//                $oRealPrice->setTrade(realPrice::TRADE_SELL);
//            }
//            elseif ($iRand==10){
//                $oRealPrice->setTrade(realPrice::TRADE_CLOSE);
//            }
//            $a = $oRealPrice->getIndicators()->getData();
//            var_dump($a['MA'][10]['real']);
        }
        for($i=0;$i<count($aBuy);$i++){
            if (($i%2)==1) echo $aBuy[$i].' '.$aBuy[$i-1].' '.$aBuy[$i]-$aBuy[$i-1].'<br/>';
        }
    }
}
?>
