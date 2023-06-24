<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * The Template for displaying all single events
 * 
 * @author Webnus <info@webnus.net>
 * @package MEC/Templates
 * @version 1.0.0
 * 	relocate ical/gcal, display event passed msg
 */
get_header('mec'); ?>

    <section id="<?php echo apply_filters('mec_single_page_html_id', 'main-content'); ?>" class="<?php echo apply_filters('mec_single_page_html_class', 'mec-container'); ?>">

<?php do_action('mec_before_main_content'); ?>

<?php while(have_posts()): the_post(); ?>

            <?php $MEC = MEC::instance(); echo MEC_Kses::full($MEC->single()); ?>

<?php endwhile; // end of the loop. ?>

<?php comments_template(); ?>

    </section>

<?php do_action('mec_after_main_content'); ?>

<script type="text/javascript" id="cghoa-reparent-export-div">
    var countdown = document.querySelector("#mec_countdown_details");
    if ( countdown === null )
    {
 	    var msg = document.createElement("h3");
	    msg.appendChild(document.createTextNode("This event has passed"));
	    var infobox = document.querySelector(".mec-event-meta");
	    infobox.insertBefore(msg, infobox.firstChild);
        countdown = document.querySelector(".mec-events-meta-group-countdown");
	    if (countdown !== null) 
	        countdown.remove();
    }
    var icals = document.querySelector(".mec-event-export-module");
    if ( icals !== null )
    {
	    var info = document.querySelector(".col-md-4");
        b4 = info.lastChild.nextSibling;
        info.insertBefore(icals,b4);
    }
	var article = document.querySelector("article.mec-single-event");
	var jprp = document.querySelector("#jp-relatedposts");
    if ( jprp !== null )
    {
        b4 = article.lastChild.nextSibling;
        article.insertBefore(jprp,b4);
	}
	var shdy = document.querySelector("div.sharedaddy");
    if ( shdy !== null )
    {
        b4 = article.lastChild.nextSibling;
        article.insertBefore(shdy,b4);
	}
</script>
<?php get_footer('mec');