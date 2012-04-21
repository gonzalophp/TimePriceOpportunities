<?php
require_once ('ini.php');

require_once('Smarty.class.php');


$oSmarty = new Smarty();
$oSmarty->setTemplateDir('templates');
$oSmarty->left_delimiter='<!--{';
$oSmarty->right_delimiter='}-->';
$oSmarty->caching = 0;
$oSmarty->clearAllCache();


$oPage = new StdClass();

require_once('class/control.update_dukascopy.class.php');
require_once('class/control.display_TPO.class.php');

$oSmarty->assign('oPage',$oPage);
echo $oSmarty->fetch('index.tpl');


if (count($_POST)>0){
    if (array_key_exists('update_dukascopy', $_POST)){
        require_once('class/update_dukascopy.class.php');
        //$oUpdateDukascopy = new update_dukascopy("25","60"); // DAX, 60 segundos
        $oUpdateDukascopy = new update_dukascopy("778","60"); // IBEX, 60 segundos
        $oUpdateDukascopy->run();
    }
    
    if (array_key_exists('display_5day_tpo', $_POST)){
        require_once('class/display_TPO.class.php');
        $oDisplayTPO = new display_TPO(  $_POST['quote_id']
                                        ,$_POST['interval']
                                        ,$_POST['days']
                                        ,$_POST['price_interval']); 
        echo $oDisplayTPO->run();
    }
}


 ?>