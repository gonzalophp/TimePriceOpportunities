<script>
    
$(function() {
        $( "#datepicker" ).datepicker();
        $( "#datepicker" ).datepicker( "option", "dateFormat", "mm.dd.yy" );
        var date = new Date();
        $( "#datepicker" ).val(("0"+(date.getMonth()+1)).slice(-2)+'.'+("0"+date.getDate()).slice(-2)+'.'+date.getFullYear());
});
</script>
<form method="POST">
    <fieldset>
        <h3>Update Dukascopy data</h3>
        
        <label for="quote_select">Quote</label>
        <select id="quote_select" name="quote_id">
            <!--{foreach from=$control_update->aDukascopyQuotes item=aQuotes}-->
                <option value="<!--{$aQuotes.DQI_dukascopy_id}-->"><!--{$aQuotes.DQI_quote_id}--></option>
            <!--{/foreach}-->
        </select>
        <label  for="interval">Interval (sec)</label>
        <select id="interval_select" name="interval">
                <option value="60">1 min</option>
                <option value="600">10 min</option>
                <option value="1D">1 day</option>
        </select>
        <label  for="datepicker">date</label>
        <p><input type="text" name="datepicker" id="datepicker" /></p>
        <input name="update_dukascopy" type="submit" value="update dukascopy"/>
    </fieldset>
</form>
<form method="POST">
    <fieldset>
        <h3>Update Telegraph data</h3>
        
        <label for="quote_select">Quote</label>
        <select id="quote_select" name="quote_id">
            <!--{foreach from=$control_update->aTelegraphQuotes item=aQuotes}-->
                <option value="<!--{$aQuotes.TQI_telegraph_id}-->"><!--{$aQuotes.TQI_quote_id}--></option>
            <!--{/foreach}-->
        </select>
        <label  for="interval">Interval (sec)</label>
        <input type="text" name="interval" value="60"/>
        <input name="update_telegraph" type="submit" value="update telegraph"/>
    </fieldset>
</form>