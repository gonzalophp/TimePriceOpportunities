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
        }
        $this->_aData['prices_so_far'][] = $oRealPrice->getClose();
        
        if (array_key_exists('ma', $this->_aIndicators)) $this->_buildMA($oPreviousRealPrice,$oRealPrice);
        if (array_key_exists('bol', $this->_aIndicators)) $this->_buildBollinger($oPreviousRealPrice,$oRealPrice);
    }
    
    public function getData(){
        return $this->_aData;
    }
    
    public function calculateGraphIndicators($oGraphicalChart){
        foreach($this->_aIndicators['ma'] as $iMAPrices){
            if (count($this->_aData['prices_so_far'])>=$iMAPrices){
                $this->_aData['MA'][$iMAPrices]['graph'] = $oGraphicalChart->getGraphicalY($this->_aData['MA'][$iMAPrices]['value']);
            }
        }
    }
    
    public function drawIndicators($oImageChart, $x){
        static $aPreviousIndicatorsData = NULL;
        static $iPreviousX = NULL;
        
        if (array_key_exists('ma', $this->_aIndicators)) {
            foreach($this->_aIndicators['ma'] as $iMAPrices){
                $oImageChart->drawPoint($x, $this->_aData['MA'][$iMAPrices]['graph']);
                if (!is_null($aPreviousIndicatorsData) && !(is_null($this->_aData['MA'][$iMAPrices]['graph']))){
                    $oImageChart->drawLine($x, $this->_aData['MA'][$iMAPrices]['graph'], $iPreviousX, $aPreviousIndicatorsData['MA'][$iMAPrices]['graph'], $this->_aData['MA'][$iMAPrices]['color']);
                }
            }
        }
        
        $aPreviousIndicatorsData = $this->_aData;
        $iPreviousX = $x;
    }
    
    private function _buildBollinger($oPreviousRealPrice, realprice $oRealPrice){
        
    }
    
    private function _buildMA($oPreviousRealPrice, realprice $oRealPrice){
 
        $aMAColors = array(  array('r'=>255, 'g'=>0, 'b'=>0)
                            ,array('r'=>0, 'g'=>255, 'b'=>0)
                            ,array('r'=>0, 'g'=>0, 'b'=>255)
                            ,array('r'=>255, 'g'=>255, 'b'=>0));
        reset($aMAColors);
        foreach($this->_aIndicators['ma'] as $iMAPrices){
            if (!array_key_exists($iMAPrices, $this->_aData['MA'])){
                list($iKey,$aColor) = each($aMAColors);
                $this->_aData['MA'][$iMAPrices] = array('value' => NULL, 'graph' => NULL, 'color' => $aColor);
            }
            
            $this->_aData['MA'][$iMAPrices]['value'] = (count($this->_aData['prices_so_far'])>=$iMAPrices) 
                                                        ? (array_sum(array_slice($this->_aData['prices_so_far'], (count($this->_aData['prices_so_far'])-$iMAPrices), $iMAPrices))/$iMAPrices) : NULL;
        }
    }
}
?>
