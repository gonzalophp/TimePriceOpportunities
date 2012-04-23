<?php
class data_interface {
    
    private $_data_interface;
    private $_data_interface_class;
    
    function data_interface(){
        require_once('class/data_interface/data.'.DATA_INTERFACE.'.class.php');
        
        $this->_data_interface_class = 'data_'.DATA_INTERFACE;
        $this->_data_interface = new $this->_data_interface_class();
    }
    
    public function __CALL($sFunctionName,$aParameters){
        return call_user_func_array(array($this->_data_interface,$sFunctionName), $aParameters);
    }
    
    public function __GET($aParams){
        echo "ESTO ES GET EN CLASS/DATA_INTERFACE.CLASS.PHP";
        var_dump($aParams);
    }
}
?>
