<?php
date_default_timezone_set('Europe/London');

$sProjectFolder = dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
$sSmartyPath = 'smarty/Smarty-3.1.8/libs';
set_include_path(get_include_path().';'.$sProjectFolder.';'.$sSmartyPath);

define('DATA_INTERFACE', 'postgresql');
define('DB_HOST','localhost');
define('DB_PORT','5432');
define('DB_NAME','postgres');
define('DB_USER','postgres');
define('DB_PASSWORD','postgres');

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