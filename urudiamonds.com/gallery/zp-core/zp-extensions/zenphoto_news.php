<?php
/**
 * A backend plugin that displays the lastest news articles  from the RSS news feed from Zenphoto.org on Zenphoto's backend overview page.
 * An adaption of RSS Extractor and Displayer	(c) 2007-2009  Scriptol.com - License Mozilla 1.1. PHP 5 only.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 5;
$plugin_description = gettext("Places the latest 3 news articles from Zenphoto.org on the admin overview page.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_version = '1.3.1'; 
$plugin_URL = "http://www.zenphoto.org/documentation/plugins/_".PLUGIN_FOLDER."---zenphoto_news.php.html";
$plugin_disable = (version_compare(PHP_VERSION, '5.0.0') != 1) ? gettext('PHP version 5 or greater is required.') : false;
if (!$plugin_disable) {
	zp_register_filter('admin_overview_left', 'printNews');
}

function printNews($discard) {
	if ($connected = is_connected()) {
		require_once(dirname(__FILE__).'/zenphoto_news/rsslib.php'); 
	}
	?>
	<div class="box" id="overview-news">
		<h2 class="h2_bordered"><?php echo gettext("News from Zenphoto.org"); ?></h2>
		<?php
		if ($connected) {
			echo RSS_Display("http://www.zenphoto.org/category/News/feed", 3);
		} else {
			?>
			<ul>
				<li>
				<?php echo gettext('A connection to <em>Zenphoto.org</em> could not be established.'); ?>
				</li>
			</ul>
			<?php
		}
		?>
	</div>
<?php 
}
?>