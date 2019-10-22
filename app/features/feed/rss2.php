<?php
/** no direct access **/
defined('MECEXEC') or die();

header('Content-Type: '.feed_content_type('rss2').'; charset='.get_option('blog_charset'), true);
$more = 1;

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
do_action('rss_tag_pre', 'rss2');
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action('rss2_ns'); ?>>
<channel>
	<title><?php wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url'); ?></link>
	<description><?php bloginfo_rss("description"); ?></description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<language><?php bloginfo_rss('language'); ?></language>
	<sy:updatePeriod><?php echo apply_filters('rss_update_period', 'hourly'); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters('rss_update_frequency', 1); ?></sy:updateFrequency>
	<?php do_action('rss2_head'); ?>
    
    <?php foreach($this->events as $date): foreach($date as $event): ?>
    <item>
		<title><?php echo $this->feed->title($event->ID); ?></title>
		<link><?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?></link>
        
        <?php if(get_comments_number($event->ID) or comments_open($event->ID)): ?>
		<comments><?php $this->feed->comments_link_feed($event->ID); ?></comments>
        <?php endif; ?>

		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $event->date['start']['date'].' '.$event->data->time['start'], false); ?></pubDate>
		<dc:creator><![CDATA[<?php $this->feed->author($event->data->post->post_author); ?>]]></dc:creator>

		<guid isPermaLink="false"><?php the_guid($event->ID); ?></guid>

        <description><![CDATA[<?php echo $this->feed->excerpt($event->ID); ?>]]></description>

        <?php if(!get_option('rss_use_excerpt')): $content = $this->feed->content($event->ID, 'rss2'); ?>
        <content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
        <?php endif; ?>

        <?php if(get_comments_number($event->ID) or comments_open($event->ID)): ?>
		<wfw:commentRss><?php echo esc_url(get_post_comments_feed_link($event->ID, 'rss2')); ?></wfw:commentRss>
		<slash:comments><?php echo get_comments_number($event->ID); ?></slash:comments>
        <?php endif; ?>
        
        <?php $this->feed->enclosure_rss($event->ID); ?>
        <?php do_action('rss2_item'); ?>
	</item>
    <?php endforeach; endforeach; ?>
</channel>
</rss>