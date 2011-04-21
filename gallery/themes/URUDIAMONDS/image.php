<?php include ("header.php"); ?>

		<div id="image-page" class="clearfix">
			<div id="headline" class="clearfix">
				<h4><?php printHomeLink('', ' | '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo getGalleryTitle();?></a> | <?php printParentBreadcrumb("", " | ", " | "); printAlbumBreadcrumb("", " | "); ?></span> <?php printImageTitle(true); ?></h4>
				<table id="navbar" class="clr">
					<tr>
						<td width="40%">
							<?php if ($_zp_current_album->getParent()) { $linklabel=gettext('Subalbum'); } else { $linklabel=gettext('Album'); } ?>
							<div id="navbar-prev">
								<?php $albumnav = getPrevAlbum();
								if (!is_null($albumnav)) { ?>
								<a href="<?php echo getPrevAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo '&lsaquo; '.$linklabel.': '.$albumnav->getTitle(); ?></a>
								<?php } ?>
							</div>
						</td>
						<td width="20%">
							<div id="navbar-center">
								<span><?php echo gettext('Image ').imageNumber().gettext(' of ').getNumImages(); ?></span>
							</div>
						</td>
						<td width="40%">
							<div id="navbar-next">
								<?php $albumnav = getNextAlbum();
								if (!is_null($albumnav)) { ?>
								<a href="<?php echo getNextAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo $linklabel.': '.$albumnav->getTitle().' &rsaquo;'; ?></a>
								<?php } ?>
							</div>
						</td>
					</tr>
				</table>
			</div>
					
			<table id="images-three" class="clearfix">
				<tr>
					<td>
						<div id="left" class="opac">
							<?php if (hasPrevImage()) { ?>
							<a class="thumb" href="<?php echo getPrevImageURL(); ?>" title="<?php echo gettext('Previous Image'); ?>"><img src="<?php echo getPrevImageThumb(); ?>" alt="<?php echo gettext('Previous Image'); ?>" /></a>
							<a href="<?php echo getPrevImageURL(); ?>" title="<?php echo gettext('Previous Image'); ?>">&lsaquo; <?php echo gettext('Prev'); ?></a>
							<?php } ?>	
						</div>
					</td>
					<td>
						<div id="center">
							<?php if ((getOption(final_link))=='colorbox') { ?><a rel="zoom" href="<?php echo htmlspecialchars(getFullImageURL());?>" title="<?php echo getBareImageTitle();?>"><?php printDefaultSizedImage(getImageTitle()); ?></a><?php } ?>
							<?php if ((getOption(final_link))=='nolink') { printDefaultSizedImage(getImageTitle()); } ?>
							<?php if ((getOption(final_link))=='standard') { ?><a href="<?php echo htmlspecialchars(getFullImageURL());?>" title="<?php echo getBareImageTitle();?>"><?php printDefaultSizedImage(getImageTitle()); ?></a><?php } ?>
							<?php if ((getOption(final_link))=='standard-new') { ?><a target="_blank" href="<?php echo htmlspecialchars(getFullImageURL());?>" title="<?php echo getBareImageTitle();?>"><?php printDefaultSizedImage(getImageTitle()); ?></a><?php } ?>
						</div>
					</td>
					<td>
						<div id="right" class="opac">
							<?php if (hasNextImage()) { ?>
							<a class="thumb" href="<?php echo getNextImageURL(); ?>" title="<?php echo gettext('Next Image'); ?>"><img src="<?php echo getNextImageThumb(); ?>" alt="<?php echo gettext('Next Image'); ?>" /></a>
							<a href="<?php echo getNextImageURL(); ?>" title="<?php echo gettext('Next Image'); ?>"><?php echo gettext('Next'); ?> &rsaquo;</a>
							<?php } ?>	
						</div>
					</td>
				</tr>
			</table>
				
			<div class="img-title"><?php printImageTitle(true); ?></div>
			<div><?php printImageDate('','',null,true); ?></div>
			<div class="headline-text"></div>
			<div class="headline-tags"></div>
			
						
			<a class="fadetoggler-exif"></a>
			<div id="exif-wrap" class="fader clearfix">
				<?php printImageMetadata( "",false,"exif","",true,"",true ); ?>
			</div>
			<?php } ?>
			
			<?php if (function_exists('printRating')) { ?>
			<div id="rating-wrap">
				<?php printRating(); ?>
				<noscript>Sorry, you must enable Javascript in your browser in order to vote...</noscript>
			</div>
			<?php } ?>
			
			
			<?php if ((function_exists('printCommentForm')) && (openedForComments()) ) { ?>
			<a class="fadetoggler"><?php echo gettext('Comments'); ?> (<?php echo getCommentCount(); ?>)</a>
			<?php } ?>
			<?php if ((function_exists('printCommentForm')) && (openedForComments()) ) { ?>
			<div id="comment-wrap" class="fader clearfix">
				<?php printCommentForm(); ?>
			</div>
			<?php } ?>

		</div>		

<?php include("footer.php"); ?>