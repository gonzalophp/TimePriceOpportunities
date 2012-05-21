<div style="border:solid 1px red;
    display:block;
    width:900px;
    height:500px;
    overflow:scroll;
    overflow-y: hidden;
    padding:4px 4px 40px 4px">

    <div style="border:solid 1px green;
    display:inline-block;
    padding:4px 4px 4px 4px">
        <img style="position:relative;" src="img/chart.png?<!--{time()}-->"/>
    </div>
</div>

<style>
    div.stats ul {
        padding: 0px;
    }
    div.stats ul li {
        list-style: none;
    }
    
    div.stats table td {
        font-size:small;
    }
</style>
<div class="stats">
    <ul>
        <li>
            <span>Sequence </span><!--{$analysis_stats['sequence']['letters']}-->
        </li>
        <li>
            <ul>
                <span>TOTAL</span>
                <li><span>Gains </span><span><!--{$analysis_stats['total']['gains']|string_format:"%.2f"}--></span></li>
                <li><span>Loss </span><span><!--{$analysis_stats['total']['loss']|string_format:"%.2f"}--></span></li>
                <li><span>Profit </span><span><!--{$analysis_stats['total']['profit']|string_format:"%.2f"}--></span></li>
            </ul>
        </li>
        <li>
            <ul>
                <span>MAX</span>
                <li><span>Gains </span><!--{$analysis_stats['max']['gains']|string_format:"%.2f"}--></li>
                <li><span>Loss </span><!--{$analysis_stats['max']['loss']|string_format:"%.2f"}--></li>
                <li><span>Gains in a row </span><!--{$analysis_stats['max']['gains_in_a_row']|string_format:"%.2f"}--></li>
                <li><span>Losses in a row </span><!--{$analysis_stats['max']['losses_in_a_row']|string_format:"%.2f"}--></li>
            </ul>
        </li>
        <li>
            <ul>
                <span>AVG</span>
                <li><span>Gains </span><!--{$analysis_stats['avg']['gains']|string_format:"%.2f"}--></li>
                <li><span>Loss </span><!--{$analysis_stats['avg']['loss']|string_format:"%.2f"}--></li>
                <li><span>Failures </span><!--{$analysis_stats['avg']['failures']|string_format:"%.2f"}--></li>
                <li><span>Losses in a row </span><!--{$analysis_stats['avg']['losses_in_a_row']|string_format:"%.2f"}--></li>
                <li><span>Gains in a row </span><!--{$analysis_stats['avg']['gains_in_a_row']|string_format:"%.2f"}--></li>
            </ul>
        </li>
    </ul>
    <table>
        <!--{foreach from=$analysis_stats['sequence']['trades'] item=trade}-->
        <tr>
            <td><!--{if $trade['dir']>0}-->B<!--{else}-->S<!--{/if}--></td>
            <td><!--{$trade['open']}--></td>
            <td><!--{$trade['close']}--></td>
            <td><!--{if $trade['dir']>0}--><!--{($trade['close']-$trade['open'])|string_format:"%.2f"}--><!--{else}--><!--{($trade['open']-$trade['close'])|string_format:"%.2f"}--><!--{/if}--></td>
        </tr>
        <!--{/foreach}-->
    </table>
</div>