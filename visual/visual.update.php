<?php
require_once('class/data_interface.class.php');

$oPage->control_update = new StdClass();

$oDataInterface = new data_interface();
if ($aQuotes = $oDataInterface->get_quotes()){
    $oPage->control_update->aQuotes = $aQuotes;
}
?>