<?php
class file{
    public $fileName;
    private $_handle;
    
    function file($fileName){
        $this->fileName = $fileName;
    }
    
    private function _isLocal(){
        return (strpos($this->fileName,'://')===false);
    }
    
    function exists(){
        return ((!$this->_isLocal()) || (file_exists($this->fileName)));
    }
    
    function hasContent(){
        return ((!$this->_isLocal()) || (filesize($this->fileName) > 0));
    }
    
    function write($sContent){
        if ($this->_handle!==false){
            $this->_fclose();
        }
        $this->_handle = fopen($this->fileName,'w');
        $bSuccess = fwrite($this->_handle, $sContent);
        $this->_fclose();
        
        return $bSuccess;
    }
    
    function getContent(){
        $sContentString = "";
        if ($this->exists()){
            $this->_handle = fopen($this->fileName, "rb ");
            
            if ($this->_isLocal()){
                if ($this->hasContent()){
                    $sContentString = fread($this->_handle, filesize($this->fileName));
                }
            }
            else {
                $sContentString = stream_get_contents($this->_handle);
            }
            
            $this->_fclose();
        }
        
        return $sContentString;
    }
    
    function getLines(){
        $aContentArray = array();
        if ($sContentString = $this->getContent()){
            $aContentArray = explode("\n", $sContentString);
        }
        
        return $aContentArray;
    }
    
    private function _fclose(){
        if (($this->_isLocal()) && $this->exists() && ($this->_handle!==false) && ($this->_handle!==null)) fclose($this->_handle);
    }
}
?>
