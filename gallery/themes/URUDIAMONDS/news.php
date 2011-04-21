<?php include ("header.php"); ?>
	
		<div id="headline" class="clearfix">
			
			<?php if(is_NewsArticle()) { ?>
			<table id="navbar" class="clr">
				<tr>
					<td width="50%">
						<div id="navbar-prev">
							<?php if(getPrevNewsURL()) { ?><span class="singlenews_prev"><?php printPrevNewsLink('&lsaquo;'); ?></span><?php } ?>
						</div>
					</td>
					<td width="50%">
						<div id="navbar-next">
								<?php if(getNextNewsURL()) { ?><span class="singlenews_next"><?php printNextNewsLink('&rsaquo;'); ?></span><?php } ?>
						</div>
					</td>
				</tr>
			</table>
			<?php } ?>
		</div>
			
		<?php
		// single news article
		if(is_NewsArticle() AND !checkforPassword()) { ?>  	
		<div id="post" class="clearfix">
			<h2><?php printNewsTitle(); ?></h2>
			<div class="newsarticlecredit">
				<span></span>
			</div>
			<div class="extra-content">
				
			</div>
			<?php 
			printNewsContent(); 
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
			
		<?php } else { // news article loop ?>
		
		<div id="post" class="clearfix">
			
			
				<?php while (next_news()): ;?> 
				<div class="news-truncate"> 
				<div class="newsarticlecredit">
					<span></span>
				</div>	
				<?php printNewsContent(); ?>
				<?php printCodeblock(1); ?>
			</div>	
			<?php endwhile; ?>
		</div>
		
		<div id="pagination" class="clearfix">
			<?php printNewsPageListWithNav( gettext('Next &rsaquo;'),gettext('&lsaquo; Previous'),true,'clearfix' ); ?>
		</div>
		<?php } ?> 
		
		
<?php include("footer.php"); ?>

