// MEC LIST VIEW PLUGIN
(function ($) {
    $.fn.mecListView = function (options) {
        // Default Options
        var settings = $.extend({
            // These are the defaults.
            id: 0,
            atts: '',
            ajax_url: '',
            sf: {},
            current_month_divider: '',
            end_date: '',
            offset: 0,
            limit: 0,
            pagination: '0',
            infinite_locked: false,
        }, options);

        // Set onclick Listeners
        setListeners();

        function setListeners() {
            // Load More
            $("#mec_skin_" + settings.id + " .mec-load-more-button").on("click", function () {
                loadMore();
            });
        }

        function loadMore(callback) {
            // Add loading Class
            $("#mec_skin_" + settings.id + " .mec-load-more-button").addClass("mec-load-more-loading");

            $.ajax({
                url: settings.ajax_url,
                data: "action=mec_list_load_more&mec_start_date=" + settings.end_date + "&mec_offset=" + settings.offset + "&" + settings.atts + "&current_month_divider=" + settings.current_month_divider + "&apply_sf_date=0",
                dataType: "json",
                type: "post",
                success: function (response) {
                    if (response.count === 0) {
                        // Remove loading Class
                        $("#mec_skin_" + settings.id + " .mec-load-more-button").removeClass("mec-load-more-loading");

                        // Release Lock of Infinite Scroll
                        settings.infinite_locked = false;
                        $("#mec_skin_" + settings.id + " .mec-load-more-wrap").removeClass('mec-load-more-scroll-loading');

                        // Hide Pagination
                        jQuery("#mec_skin_" + settings.id + " .mec-load-more-button").addClass("mec-util-hidden");

                        // Run Callback
                        if(typeof callback === 'function') callback(response);
                    } else {
                        // Show load more button
                        if (typeof response.has_more_event === 'undefined' || (typeof response.has_more_event !== 'undefined' && response.has_more_event)) jQuery("#mec_skin_" + settings.id + " .mec-load-more-button").removeClass("mec-util-hidden");
                        else jQuery("#mec_skin_" + settings.id + " .mec-load-more-button").addClass("mec-util-hidden");

                        // Append Items
                        $("#mec_skin_events_" + settings.id).append(response.html);

                        // Remove loading Class
                        $("#mec_skin_" + settings.id + " .mec-load-more-button").removeClass("mec-load-more-loading");

                        // Update the variables
                        settings.end_date = response.end_date;
                        settings.offset = response.offset;
                        settings.current_month_divider = response.current_month_divider;
                    }
                },
                error: function() {}
            });
        }
    };
}(jQuery));