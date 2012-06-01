<script>
    
$(function() {
    $( "#datestart" ).datepicker();
    $( "#datestart" ).datepicker( "option", "dateFormat", "mm.dd.yy" );
    
    $( "#dateend" ).datepicker();
    $( "#dateend" ).datepicker( "option", "dateFormat", "mm.dd.yy" );
    var date = new Date();
    $( "#dateend" ).val(("0"+(date.getMonth()+1)).slice(-2)+'.'+("0"+date.getDate()).slice(-2)+'.'+date.getFullYear());
});
</script>
<form method="POST">
    <fieldset>
        <h3>Update data</h3>
        <label for="quote_select">Quote</label>
        <select id="quote_select" name="quote_id">
            <!--{foreach from=$control_update->aQuotes item=aQuotes}-->
                <option value="<!--{$aQuotes.source_id}-->"><!--{$aQuotes.quote_id}--></option>
            <!--{/foreach}-->
        </select>
        <label  for="interval">Interval (sec)</label>
        <select id="interval_select" name="interval">
                <option value="60">1 min</option>
                <option value="600">10 min</option>
                <option value="1D">1 day</option>
        </select>
        <label  for="datestart">Date Start</label>
        <input type="text" name="datestart" id="datestart" />
        <label  for="dateend">Date End</label>
        <input type="text" name="dateend" id="dateend" />
        <input name="update_data" type="submit" value="update"/>
    </fieldset>
</form>
        