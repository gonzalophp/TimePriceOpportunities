<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style/style.css"/>
    </head>    
    <body>
        <form method="POST">
            <fieldset>
                <h3>Update Dukascopy data</h3>
                <input name="update_dukascopy" type="submit" value="update dukascopy"/>
            </fieldset>
        </form>
        <form method="POST">
            <fieldset>
                <h3>Display TPOs</h3>
                <label for="quoteid">Quote</label>
                <input type="text" id="quoteid" name="quoteid" value="25"/>
                <label  for="interval">Interval</label>
                <input type="text" name="interval" value="60"/>
                <label  for="days">Days</label>
                <input type="text" name="days" value="5"/>
                <label  for="days">Price Interval</label>
                <input type="text" name="price_interval" value="5"/>
                <input name="display_5day_tpo" type="submit" value="Display TPOs"/>
            </fieldset>
        </form>   
 <?php
 
include('ini.php');

if (count($_POST)>0){
    if (array_key_exists('update_dukascopy', $_POST)){
        include('class/update_dukascopy.class.php');
        $oUpdateDukascopy = new update_dukascopy("25","60"); // DAX, 600 segundos
        $oUpdateDukascopy->run();
    }
    
    if (array_key_exists('display_5day_tpo', $_POST)){
        include('class/display_TPO.class.php');
        $oDisplayTPO = new display_TPO(  $_POST['quoteid']
                                        ,$_POST['interval']
                                        ,$_POST['days']
                                        ,$_POST['price_interval']); 
        echo $oDisplayTPO->run();
    }
}
      /*  <div class="timeframedays">
            <div class="price" style="background-size: 150px;">2200 Esto es</div>
            <div class="price" style="background-size: 24px;"><span>2201 Esto es 2201</span></div>
            <div class="price" style="background-size: 100px;">2202 Esto es 2202</div>
            <div class="price">jjj</div>
            <div class="price">bbbbbbb</div>
            <div class="price">jjj</div>
            <div class="price">bbbbbbb</div>
            <div class="price">jjj</div>
            <div class="price">bbbbbbb</div>
        </div>
        <div class="timeframedays">
            <div class="price" style="background-size: 150px;">2200 Esto es</div>
            <div class="price" style="background-size: 24px;"><span>2201 Esto es 2201</span></div>
            <div class="price" style="background-size: 100px;">2202 Esto es 2202</div>
            <div class="price">jjj</div>
            <div class="price">bbbbbbb</div>
        </div>*/
 ?>
    </body>
</html>       

