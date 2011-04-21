<?php include ("header.php"); ?>

		<div id="headline" class="clearfix">
			<h4><?php printHomeLink('', ' | '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo getGalleryTitle();?></a></span> | <?php echo gettext("Page not found..."); ?></h4>
		</div>

		<h4>
			<?php echo gettext("The page you are requesting cannot be found.");
			if (isset($album)) {
				echo '<br />'.sprintf(gettext('Album: %s'),sanitize($album));
			}
			if (isset($image)) {
				echo '<br />'.sprintf(gettext('Image: %s'),sanitize($image));
			}
			if (isset($obj)) {
				echo '<br />'.sprintf(gettext('Page: %s'),substr(basename($obj),0,-4));
			}
			?>
		</h4>
		
		<?php if ( ((getOption('image_statistic')!='none')) && (((function_exists('printImageStatistic')) || (getOption('image_statistic')=='random'))) ) { ?>
		<?php include("image_statistic.php"); ?>
		<?php } ?>
		
<?php include("footer.php"); ?>
