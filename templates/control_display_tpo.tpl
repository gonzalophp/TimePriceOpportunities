<form method="POST">
    <fieldset>
        <h3>Display TPOs</h3>
        <label for="quote_select">Quote</label>
        <select id="quote_select" name="quote_id">
            <!--{foreach from=$control_display_tpo->aOptions item=aOption}-->
                <option value="<!--{$aOption.DQI_dukascopy_id}-->"><!--{$aOption.DQI_quote_id}--></option>
            <!--{/foreach}-->
        </select>
        <label  for="interval">Interval</label>
        <input type="text" name="interval" value="60"/>
        <label  for="days">Days</label>
        <input type="text" name="days" value="5"/>
        <label  for="days">Price Interval</label>
        <input type="text" name="price_interval" value="5"/>
        <input name="display_5day_tpo" type="submit" value="Display TPOs"/>
    </fieldset>
</form>