<?php
require_once('class/data_interface.class.php');

$oDataInterface = new data_interface();
        
$oPage->control_update = new StdClass();

if ($aDukascopyQuotes = $oDataInterface->get_dukascopy_quotes()){
    $oPage->control_update->aDukascopyQuotes = $aDukascopyQuotes;
}

if ($aTelegraphQuotes = $oDataInterface->get_telegraph_quotes()){
    $oPage->control_update->aTelegraphQuotes = $aTelegraphQuotes;
}
?>