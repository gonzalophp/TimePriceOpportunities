<?php
require_once('class/postgres.class.php');

class control_display_TPO {
    public function get_dukascopy_quotes(){
        $oPostgres = new postgres();
        $oPostgres->connect();
        
        $sQuery = 'SELECT "DQI_dukascopy_id"'
                        .' ,"DQI_quote_id"'
                 .' FROM public."DUKASCOPY_QUOTES_ID";';
        
        $aResultSet = $oPostgres->query("dukascopy_quote_list", $sQuery);
        return $aResultSet;
    }
}

$oDukascopyQuotes = new control_display_TPO();
if ($aDukascopyQuotes = $oDukascopyQuotes->get_dukascopy_quotes()){
    $oPage->control_display_tpo = new StdClass();
    $oPage->control_display_tpo->aOptions = $aDukascopyQuotes;
}
?>
