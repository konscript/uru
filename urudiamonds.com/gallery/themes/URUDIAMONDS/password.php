<?php include ("header.php"); ?>

		<div id="headline" class="clearfix">
			<h4><?php printHomeLink('', ' | '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo getGalleryTitle();?></a></span> | <?php echo gettext("Password Required..."); ?></h4>
		</div>
			
		<div class="error"><?php echo gettext("A password is required for the page you requested."); ?></div>	
		<?php printPasswordForm(NULL, false); ?>

		<?php if ( ((getOption('image_statistic')!='none')) && (((function_exists('printImageStatistic')) || (getOption('image_statistic')=='random'))) ) { ?>
		<?php include("image_statistic.php"); ?>
		<?php } ?>
		
<?php include("footer.php"); ?>

