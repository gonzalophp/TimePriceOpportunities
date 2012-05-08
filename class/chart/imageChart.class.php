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
        imagerectangle($this->_rImage, $this->_aChartArea['x1'], $this->_aChartArea['y1'], $this->_aChartArea['x2'], $this->_aChartArea['y2'], $this->_getColor(128, 128, 128));
    }
    
    public function drawOrdinate($iHeight,$nRealYIntervalMarks){
        imageline($this->_rImage, $this->_aChartArea['x1']-2, $iHeight, $this->_aChartArea['x2']+2, $iHeight, $this->_getColor(180,180,180));
        imagestring($this->_rImage, 1, $this->_iWidth-$this->_iFrame+1, $iHeight-4, $nRealYIntervalMarks, $this->_getColor(50, 50, 50));
    }
    
    public function drawAbscissa($iLeft){
        imageline($this->_rImage, $iLeft, $this->_aChartArea['y1']-2, $iLeft, $this->_aChartArea['y2']+2, $this->_getColor(180,180,180));
    }
    
    public function drawPoint($x,$y) {
        imageellipse($this->_rImage, $x, $y,5,5, $this->_getColor(255,0,255));
    }
    
    public function drawCandlestick($iMinX, $iMaxX, $iMinY, $iMaxY, $iYOpen, $iYClose){
        $iXLine = (int)(string)($iMinX+(($iMaxX-$iMinX)/2));
        imageline($this->_rImage, $iXLine, $iMinY, $iXLine, $iMaxY, $this->_getColor(0,0,0));
        
        $oColor = ($iYOpen > $iYClose) ? $this->_getColor(0, 255, 0) : $this->_getColor(255, 0, 0);
        imagefilledrectangle($this->_rImage, $iMinX, $iYOpen, $iMaxX, $iYClose, $oColor);
        imagerectangle($this->_rImage, $iMinX, $iYOpen, $iMaxX, $iYClose, $this->_getColor(0, 0, 0));
    }
    
    public function dumpImage() {
        header("Content-type: image/png");
        imagepng($this->_rImage);
        imagedestroy($this->_rImage);
    }
    
    private function _getColor($r, $g, $b){
        return imagecolorallocate($this->_rImage, $r, $g, $b);
    }
}
?>
