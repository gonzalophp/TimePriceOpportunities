<html>
    <head>
        <link rel="stylesheet" type="text/css" href="css/main.css"/>
        <link rel="stylesheet" type="text/css" href="css/ui-lightness/jquery-ui-1.8.20.custom.css"/>
        <script src="js/jquery-1.7.2.min.js"></script>
        <script src="js/jquery-ui-1.8.20.custom.min.js"></script>
        
        <script language="javascript" type="text/javascript" src="js/niceforms.js"></script>
        <link rel="stylesheet" type="text/css" media="all" href="css/niceforms-default.css" />
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
            <!--{include file='day_frame_TPO.tpl' display_day_frame_tpo=$oPage->day_frame_tpo}-->
            </div>
        <!--{/if}-->
        <!--{if isset($oPage->chart_dukascopy)}-->
            <!--{include file='chart_dukascopy.tpl' analysis_stats=$oPage->analysis_stats}-->
        <!--{/if}-->
    </body>
</html>       
