<?php
require_once ('ini.php');
require_once('class/data_interface.class.php');

require_once('class/chart/graphicalchart.class.php');
require_once('class/chart/realchart.class.php');
require_once('class/chart/realprice.class.php');
require_once('class/chart/candlestick.class.php');
require_once('class/chart/dot.class.php');

require_once('class/data_analysis.class.php');

class display_char {
    private $_aIndicatorsSettings;
    
    public function display_char($aIndicatorsSettings=array()){
        $this->_aIndicatorsSettings = $aIndicatorsSettings;
        if (empty($this->_aIndicatorsSettings)){
            foreach($_GET as $sKey=>$aSettings){
                switch($sKey){
                    case 'ma':
                        $this->_aIndicatorsSettings['ma'] = explode(',', $aSettings);
                    break;
                    case 'bol':
                        $aBollinger = explode(',', $aSettings);
                        $this->_aIndicatorsSettings['bol']=array('n'=>$aBollinger[0],'std_dev'=>$aBollinger[1]);
                    break;
                    case 'rsi':
                        $this->_aIndicatorsSettings['rsi']=explode(',', $aSettings);
                    break;
                    case 'sto':
                        $aStochastic = explode(',', $aSettings);
                        $this->_aIndicatorsSettings['sto']=array('n'=>$aStochastic[0], 'k'=>$aStochastic[1], 'd'=> $aStochastic[2]);
                    break;
                    case 'sar':
                        $aSAR = explode(',', $aSettings);
                        $this->_aIndicatorsSettings['sar']=array('af0'=>$aSAR[0],'afX'=>$aSAR[1],'afMax'=>$aSAR[2]);
                    break;
                }
            }
        }
    }
    
    public function run($sQuoteId,$iInterval,$iDays){
        $oDataInterface = new data_interface();
        $aResultSet = $oDataInterface->getDukascopyTPOData($sQuoteId,$iInterval,$iDays);
        $aDukascopyQuotes = $oDataInterface->get_dukascopy_quotes();
        
        foreach($aDukascopyQuotes as $aQuote){
            if ($aQuote['DQI_dukascopy_id']==$sQuoteId){
                $sQuote = $aQuote['DQI_quote_id'];
            }
        }
        
        //$aIndicators = array('ma'   => array(10,20)
        //                    ,'bol'  => array('n'=>10,'std_dev'=>2)
        //                    ,'rsi'  => array(4)
        //                    ,'sto'  => array('n' => 7, 'k' => 3, 'd'=> 5)
        //                    ,'sar'  => array('af0' => 0.02, 'afX'=> 0.02, 'afMax'=> 0.2));

        //http://localhost/mom/chart.php?ma=10,20&bol=10,2&rsi=14&sar=0.02,0.02,0.2

        
        $iMinutesPerPrice = 30;
        $Zoom = 1;
        $oRealChart = new realChart($sQuote, $iMinutesPerPrice, $Zoom, $this->_aIndicatorsSettings);
        foreach($aResultSet as $aDataPrice){
            $oRealChart->addPrice($aDataPrice['datetime'],new candlestick($aDataPrice['datetime']
                                                                        ,$aDataPrice['min']
                                                                        ,$aDataPrice['max']
                                                                        ,$aDataPrice['open']
                                                                        ,$aDataPrice['close']
                                                                        ,$aDataPrice['volume']));
        }
        
        $oGraphicalChart = new graphicalChart(900,500);
        $oGraphicalChart->buildGraphicalChart($oRealChart);
        $oGraphicalChart->draw();
        
        return $oRealChart;
    }
}

if (array_key_exists('chart_dukascopy', $_POST)){
    $aIndicators = array('ma'   => array(10,20)
                        ,'bol'  => array('n'=>20,'std_dev'=>2)
                        ,'rsi'  => array(14)
                        ,'sto'  => array('n' => 14, 'k' => 3, 'd'=> 5)
                        ,'sar'  => array('af0' => 0.02, 'afX'=> 0.02, 'afMax'=> 0.2));
    $oDisplayChart = new display_char($aIndicators);
    $oRealChart = $oDisplayChart->run($_POST['quote_dukascopy_id'],$_POST['interval'],$_POST['days']);
    
    $oDataAnalysis = new data_analysis($oRealChart);
    $oDataAnalysis->strategy1();
    $oPage->chart_dukascopy = 2;
}

?>
