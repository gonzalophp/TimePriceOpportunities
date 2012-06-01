<form method="POST">
    <fieldset>
        <h3>Display TPOs</h3>
        <label for="quote_select">Quote Dukascopy</label>
        <select id="quote_select" name="quote_id">
            <!--{foreach from=$control_display_tpo->aQuotes item=aQuotes}-->
                <option value="<!--{$aQuotes.source_id}-->"><!--{$aQuotes.quote_id}--></option>
            <!--{/foreach}-->
        </select>
        <label  for="interval">Interval</label>
        <select id="interval_select" name="interval">
                <option value="5">5 min</option>
                <option value="10">10 min</option>
                <option value="15">15 min</option>
                <option value="30">30 min</option>
                <option selected="1" value="60">60 min</option>
                <option value="1D">1 day</option>
                <option value="1W">1 week</option>
        </select>
        <label  for="days">Days</label>
        <input type="text" name="days" value="10"/>
        <label  for="days">Price Interval</label>
        <input type="text" name="price_interval" value="10"/>
        <label  for="days">Graph Width</label>
        <input type="text" name="graph_width" value="100"/>
        <input name="display_day_frame_tpo" type="submit" value="Display TPOs"/>
        <input name="chart_dukascopy" type="submit" value="Chart Dukascopy"/>
    </fieldset>
</form>