<?php
require_once('test/test.php');

class imageChart {
    private $_iWidth;
    private $_iHeight;
    private $_iFrame;
    private $_rImage;
    private $_aChartArea = array();
    
    public function imageChart($iWidth, $iHeight, $iFrame) {
        $this->_iWidth = $iWidth;
        $this->_iHeight = $iHeight;
        $this->_iFrame = $iFrame;
        $this->_aChartArea = array( 'x1' => ($this->_iFrame-1)
                                   ,'y1' => ($this->_iFrame-1)
                                   ,'x2' => $this->_iWidth-($this->_iFrame+1)
                                   ,'y2' => $this->_iHeight-($this->_iFrame+1));
        $this->_rImage = imagecreate($this->_iWidth, $this->_iHeight);
        imagefill($this->_rImage, 0, 0 , $this->_getColor(230, 230, 230));
    }
    
    public function drawFrame($x1,$y1,$x2,$y2){
        imagerectangle($this->_rImage, $x1, $y1, $x2, $y2, $this->_getColor(128, 128, 128));
    }
    
    public function drawOrdinate($iHeight,$nRealYIntervalMarks){
        imageline($this->_rImage, $this->_aChartArea['x1']-2, $iHeight, $this->_aChartArea['x2']+2, $iHeight, $this->_getColor(180,180,180));
        imagestring($this->_rImage, 1, $this->_iWidth-$this->_iFrame+1, $iHeight-4, $nRealYIntervalMarks, $this->_getColor(50, 50, 50));
    }
    
    public function drawLabel($iFontSize, $x, $y, $sLabel, $aColor){
        imagestring($this->_rImage, $iFontSize, $x, $y, $sLabel, $this->_getColor($aColor['r'], $aColor['g'], $aColor['b']));
    }
    
    public function drawValueLabel($iFontSize, $y, $sLabel){
        imagerectangle($this->_rImage, $this->_aChartArea['x2']-($this->_iFrame/2)+1, $y-6, $this->_iWidth-1, $y+6, $this->_getColor(0, 0, 0));
        imagefilledrectangle($this->_rImage, $this->_aChartArea['x2']-($this->_iFrame/2)+2, $y-5, $this->_iWidth-2, $y+5, $this->_getColor(210, 255, 0));
        imagestring($this->_rImage, $iFontSize, $this->_aChartArea['x2']-($this->_iFrame/2)+3, $y-4, $sLabel, $this->_getColor(0,0,0));
    }
    
    public function drawAbscissa($iLeft, $y1, $y2,$aAbscissaColor){
        imageline($this->_rImage, $iLeft, $y1, $iLeft, $y2, $this->_getColor($aAbscissaColor['r'],$aAbscissaColor['g'],$aAbscissaColor['b']));
    }
    
    public function drawPoint($x,$y, $aColor) {
        imagefilledrectangle($this->_rImage, $x, $y, $x+3, $y+3, $this->_getColor($aColor['r'],$aColor['g'],$aColor['b']));
    }
    
    public function drawLine($x1,$y1,$x2,$y2, $aColor){
        imageline($this->_rImage, $x1, $y1, $x2, $y2, $this->_getColor($aColor['r'],$aColor['g'],$aColor['b']));
    }
    
    public function drawCandlestick($iMinX, $iMaxX, $iMinY, $iMaxY, $iYOpen, $iYClose){
        $iXLine = (int)(string)($iMinX+(($iMaxX-$iMinX)/2));
        imageline($this->_rImage, $iXLine, $iMinY, $iXLine, $iMaxY, $this->_getColor(0,0,0));
        
        $oColor = ($iYOpen > $iYClose) ? $this->_getColor(0, 255, 0) : $this->_getColor(255, 0, 0);
        imagefilledrectangle($this->_rImage, $iMinX, $iYOpen, $iMaxX, $iYClose, $oColor);
        imagerectangle($this->_rImage, $iMinX, $iYOpen, $iMaxX, $iYClose, $this->_getColor(0, 0, 0));
    }
    
    public function drawBalloon($x,$y, $aColor){
        imagefilledellipse($this->_rImage, $x, $y, 15, 10, $this->_getColor($aColor['r'], $aColor['g'], $aColor['b']));
    }
    
    public function drawImage() {
//        header("Content-type: image/png");
        imagepng($this->_rImage,'img/chart.png');
//        imagepng($this->_rImage);
        imagedestroy($this->_rImage);
    }
    
    private function _getColor($r, $g, $b){
        static $aSavedColors=array();
        
        $sColor = serialize(array($r,$g,$b));
        
        if (!array_key_exists($sColor, $aSavedColors)){
            $aSavedColors[$sColor] = imagecolorallocate($this->_rImage, $r, $g, $b);
            return $aSavedColors[$sColor];
        }
        else {
            return $aSavedColors[$sColor];
        }
        
    }
}
?>
