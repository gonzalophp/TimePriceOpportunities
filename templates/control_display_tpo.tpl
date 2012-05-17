<form method="POST">
    <fieldset>
        <h3>Display TPOs</h3>
        <label for="quote_select">Quote Dukascopy</label>
        <select id="quote_select" name="quote_dukascopy_id">
            <!--{foreach from=$control_display_tpo->aDukascopyQuotes item=aQuotes}-->
                <option value="<!--{$aQuotes.DQI_dukascopy_id}-->"><!--{$aQuotes.DQI_quote_id}--></option>
            <!--{/foreach}-->>
        </select>
        <label for="quote_select">Quote Telegraph</label>
        <select id="quote_select" name="quote_telegraph_id">
            <!--{foreach from=$control_display_tpo->aTelegraphQuotes item=aQuotes}-->
                <option value="<!--{$aQuotes.TQI_telegraph_id}-->"><!--{$aQuotes.TQI_quote_id}--></option>
            <!--{/foreach}-->
        </select>
        <label  for="interval">Interval</label>
        <input type="text" name="interval" value="60"/>
        <label  for="days">Days</label>
        <input type="text" name="days" value="5"/>
        <label  for="days">Price Interval</label>
        <input type="text" name="price_interval" value="5"/>
        <label  for="days">Graph Width</label>
        <input type="text" name="graph_width" value="100"/>
        <input name="display_day_frame_tpo_dukascopy" type="submit" value="Display Dukascopy TPOs"/>
        <input name="display_day_frame_tpo_telegraph" type="submit" value="Display Telegraph TPOs"/>
        <input name="chart_dukascopy" type="submit" value="Chart Dukascopy"/>
    </fieldset>
</form>