<?php include ("header.php"); ?>

			<div id="image-page">
				<div id="headline" class="clearfix">
					<h4><?php printHomeLink('', ' | '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo getGalleryTitle();?></a> | <?php echo gettext('Archive View'); ?></h4>
				</div>

				<div class="post">
					<table id="archive">
						<tr>
							<td>
								<h4><?php echo gettext('Gallery Archive'); ?></h4>
								<?php printAllDates(); ?>
							</td>
							<?php if(function_exists("printNewsArchive")) { ?>
							<td id="newsarchive">
								<h4><?php echo gettext('News archive'); ?></h4>
								<?php printNewsArchive("archive"); ?>
							</td>
							<?php } ?>
						</tr>
					</table>
				</div>
				
			</div>		
			
					

<?php include("footer.php"); ?>
