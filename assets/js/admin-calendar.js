// MEC MONTHLY VIEW PLUGIN
(function($)
{
    $.fn.mecMonthlyView = function(options)
    {
        var active_month;
        var active_year;

        // Default Options
        var settings = $.extend(
        {
            // These are the defaults.
            today: null,
            id: 0,
            month_navigator: 0,
            atts: '',
            active_month: {},
            next_month: {},
            sf: {},
            ajax_url: '',
        }, options);

        // Initialize Month Navigator
        if(settings.month_navigator)
        {
            // Add Loading Wrapper
            jQuery('.mec-wrap').append('<div class="mec-modal-result"></div>');

            active_month = settings.active_month.month;
            active_year = settings.active_month.year;

            setTimeout(initMonthNavigator, 1000);
        }

        function initMonthNavigator()
        {
            // Remove the onclick event
            $("#mec_skin_" + settings.id + ' .mec-load-month').off('click');

            // Add onclick event
            $("#mec_skin_" + settings.id + ' .mec-load-month').on('click', function()
            {
                var year = $(this).data('mec-year');
                var month = $(this).data('mec-month');

                setMonth(year, month);
            });
        }

        function setMonth(year, month)
        {
            var month_id = year + "" + month;
            active_month = month;
            active_year = year;

            // Month exists so we just show it
            if($("#mec_monthly_view_month_" + settings.id + "_" + month_id).length)
            {
                // Toggle Month
                toggleMonth(month_id);
            }
            else
            {
                var $modalResult = $('.mec-modal-result');

                // Add Loading Class
                $modalResult.addClass('mec-month-navigator-loading');

                $.ajax(
                {
                    url: settings.ajax_url,
                    data: "action=mec_monthly_view_load_month&mec_year=" + year + "&mec_month=" + month + "&" + settings.atts,
                    dataType: "json",
                    type: "post",
                    success: function(response)
                    {
                        // Append Month
                        $("#mec_skin_events_" + settings.id).append('<div class="mec-month-container" id="mec_monthly_view_month_' + settings.id + '_' + response.current_month.id + '" data-month-id="' + response.current_month.id + '">' + response.month + '</div>');

                        // Append Month Navigator
                        $("#mec_skin_" + settings.id + " .mec-skin-monthly-view-month-navigator-container").append('<div class="mec-month-navigator" id="mec_month_navigator_' + settings.id + '_' + response.current_month.id + '">' + response.navigator + '</div>');

                        // Re-initialize Month Navigator
                        initMonthNavigator();

                        // Toggle Month
                        toggleMonth(response.current_month.id);

                        // Remove loading Class
                        $modalResult.removeClass("mec-month-navigator-loading");

                        jQuery(document).trigger('load_calendar_data');
                    },
                    error: function(){}
                });
            }
        }

        function toggleMonth(month_id)
        {
            var $monthContainer = $("#mec_skin_" + settings.id + " .mec-month-container");
            var $currentMonth = $("#mec_monthly_view_month_" + settings.id + "_" + month_id);

            // Toggle Month Navigator
            $("#mec_skin_" + settings.id + " .mec-month-navigator").hide();
            $("#mec_month_navigator_" + settings.id + "_" + month_id).show();

            // Toggle Month
            $monthContainer.hide();
            $currentMonth.show();

            // Add selected class
            $monthContainer.removeClass("mec-month-container-selected");
            $currentMonth.addClass("mec-month-container-selected");

            // Toggle Events Side
            $("#mec_skin_" + settings.id + " .mec-month-side").hide();
            $("#mec_month_side_" + settings.id + "_" + month_id).show();

            jQuery(document).trigger('mec_toggle_month', settings, month_id);
        }
    };
}(jQuery));