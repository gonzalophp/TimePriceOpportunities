<form method="POST">
    <fieldset>
        <h3>Update Dukascopy data</h3>
        
        <label for="quote_select">Quote</label>
        <select id="quote_select" name="quote_id">
            <!--{foreach from=$control_update_dukascopy->aOptions item=aOption}-->
                <option value="<!--{$aOption.DQI_dukascopy_id}-->"><!--{$aOption.DQI_quote_id}--></option>
            <!--{/foreach}-->
        </select>
        <label  for="interval">Interval</label>
        <input type="text" name="interval" value="60"/>
        <input name="update_dukascopy" type="submit" value="update dukascopy"/>
    </fieldset>
</form>