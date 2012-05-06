<?php
class realPrice {
    private $_nMin;
    private $_nMax;
    private $_nOpen;
    private $_nClose;
    private $_iVolume;
    
    public function realPrice($nMin, $nMax, $nOpen, $nClose, $iVolume){
        $this->_nMin        = $nMin;
        $this->_nMax        = $nMax;
        $this->_nOpen       = $nOpen;
        $this->_nClose      = $nClose;
        $this->_iVolume     = $iVolume;
    }
    
    public function getMin(){
        return $this->_nMin;
    }
    
    public function getMax(){
        return $this->_nMax;
    }
    
    public function getOpen(){
        return $this->_nOpen;
    }
    
    public function getClose(){
        return $this->_nClose;
    }
    
    public function getVolume(){
        return $this->_iVolume;
    }
    
    public function getDateTime(){
        return $this->_dateTime;
    }
}
?>