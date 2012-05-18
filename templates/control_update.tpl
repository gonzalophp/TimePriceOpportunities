<script>
$(function() {
        $( "#datepicker" ).datepicker();
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
        <input type="text" name="interval" value="60"/>
        <input type="text" id="datepicker">
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