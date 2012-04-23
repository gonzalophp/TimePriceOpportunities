<?php
require_once('class/data_interface.class.php');

$oDataInterface = new data_interface();
        
if ($aDukascopyQuotes = $oDataInterface->get_dukascopy_quotes()){
    $oPage->control_display_tpo = new StdClass();
    $oPage->control_display_tpo->aOptions = $aDukascopyQuotes;
}
?>
