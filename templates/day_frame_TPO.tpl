<style>div.price {width:<!--{$display_day_frame_tpo['graph_width']}-->px;}</style>
<!--{foreach from=$display_day_frame_tpo['time_frame_data'] key=sDayKey item=aDayData}-->
<div class="timeframedays">
    <div style="font-weight:bold;color:blue;"><!--{$sDayKey}--></div>
    <div>Total Vol:<!--{$aDayData['total_volume']}--></div>
    <div style="font-weight:bold;color:blue;">Rot. Factor: <!--{$aDayData['rotation_factor']['top']}-->/<!--{$aDayData['rotation_factor']['bottom']}--></div>
    <!--{section name=price step=-1 max=$display_day_frame_tpo['max_value']-$display_day_frame_tpo['min_value']+1  loop=$display_day_frame_tpo['max_value'] }-->
        <!--{if isset($aDayData['prices'][price])}-->
        <div class="<!--{if $aDayData['prices'][price]['value_area']==true}-->price_value<!--{else}-->price<!--{/if}-->" style="background-size: <!--{($display_day_frame_tpo['graph_width']*($aDayData['prices'][price]['volume']/$display_day_frame_tpo['max_volume']))|string_format:"%d"}-->px;"><!--{$smarty.section.price.index*$display_day_frame_tpo['price_interval']}--> <!--{$aDayData['prices'][price]['letters']}--></div>
        <!--{else}-->
        <div class="price" style="background-size: 0px;"><!--{$smarty.section.price.index*$display_day_frame_tpo['price_interval']}--></div>
        <!--{/if}-->
    <!--{/section}-->
    </div>
</div>
<!--{/foreach}-->
