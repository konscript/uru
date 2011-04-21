<?php include ("header.php"); ?>
	
		<div id="headline" class="clearfix">
			<h4><span><?php printHomeLink('', ' | '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo getGalleryTitle();?></a><?php if(!isset($ishomepage)) { printParentPagesBreadcrumb(" | ",""); } ?><strong></strong></h4>
			<table id="navbar" class="clr">
					<tr>
						<td width="100%">
							<div id="navbar-center">
								<span>
								<?php printPageMenu('omit-top','nav-pages','page-active','nav-subpages','subpage-active'); ?>	
								</span>
							</div>
						</td>
					</tr>
			</table>
		</div>
		
		<div id="post" class="clearfix">		
			<h2><?php printPageTitle(); ?></h2>
			<?php if (getPageExtraContent()) { ?>
			<div class="extra-content">
				<?php printPageExtraContent(); ?>
			</div>
			<?php } ?>
			<?php 
			printPageContent(); 
			printCodeblock(1); 
			?>
		</div>
			
		<?php if ((function_exists('printCommentForm')) && (zenpageOpenedForComments()) ) { ?>
		<a class="fadetoggler"><?php echo gettext('Comments'); ?> (<?php echo getCommentCount(); ?>)</a>
		<?php } ?>
		<?php if ((function_exists('printCommentForm')) && (zenpageOpenedForComments()) ) { ?>
		<div id="comment-wrap" class="fader clearfix">
			<?php printCommentForm(); ?>
		</div>
		<?php } ?>
		
	
		
<?php include("footer.php"); ?>

