<?php
date_default_timezone_set('Europe/London');



$sProjectFolder = dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
set_include_path(get_include_path().';'.$sProjectFolder);


/*
function customError($errno, $errstr) {
    echo "<h style='color:blue; font-weight:bold;'>CUSTON ERROR:</h><br/> [$errno] $errstr";
    
    echo '<br/><br/>';
    $aBackTrace = debug_backtrace();
    var_dump($aBackTrace);
    foreach ($aBackTrace as $sBackTrace) echo $sBackTrace.'<br/>';
}

set_error_handler("customError");
 */