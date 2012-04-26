<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style/style.css"/>
    </head>    
    <body>
        <div class="control">
            <!--{if isset($oPage->control_update)}-->
            <div>
            <!--{include file='control_update.tpl' control_update=$oPage->control_update}-->
            </div>    
            <!--{/if}-->
            <!--{if isset($oPage->control_display_tpo)}-->
            <div>
            <!--{include file='control_display_tpo.tpl' control_display_tpo=$oPage->control_display_tpo}-->
            </div>
            <!--{/if}-->
        </div>
        <!--{if isset($oPage->day_frame_tpo)}-->
            <!--{include file='day_frame_tpo.tpl' display_day_frame_tpo=$oPage->day_frame_tpo}-->
        <!--{/if}-->
    </body>
</html>       
