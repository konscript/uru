<?php include ("header.php"); ?>

			<div id="headline" class="clearfix">
				<h4><span></h4>
				<div class="headline-text"></div>
				<div class="headline-tags"></div>
				<table id="navbar" class="clr">
					<tr>
						<td width="35%">
							<?php if ($_zp_current_album->getParent()) { $linklabel=gettext('Subalbum'); } else { $linklabel=gettext(''); } ?>
							<div id="navbar-prev">
								<?php $albumnav = getPrevAlbum();
								if (!is_null($albumnav)) { ?>
								<a href="<?php echo getPrevAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo '&lsaquo; '.$linklabel.': '.$albumnav->getTitle(); ?></a>
								<?php } ?>
							</div>
						</td>
						<td width="30%">
							<div id="navbar-center">
								<span>
									<?php if ((getNumAlbums()) > 0) { echo getNumAlbums().gettext(' Subalbums, '); } ?>
									<?php echo getTotalImagesIn($_zp_current_album).gettext(' Total Images'); ?>
								</span>
							</div>
						</td>
						<td width="35%">
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
			
			<?php if (isAlbumPage()) { ?>
			<div id="album-wrap" class="clearfix">
				<ul>
					<?php $x=1; while (next_album(true)): $lastcol=""; 
					if ($x==3) {$lastcol=" class='lastcol'"; $x=0;} ?>
					<li<?php echo $lastcol; ?>>						
						<a class="album-thumb" href="<?php echo htmlspecialchars(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle();?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(),NULL,267,100,267,100); ?></a>
						<h4><a href="<?php echo htmlspecialchars(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle();?>"><?php printAlbumTitle(); ?></a></h4>
					</li>
					<?php $x++; endwhile; ?>					
				</ul>
			</div>
			<?php } ?>
		
			<?php if (getNumImages() > 0){ ?>
			<?php if (getOption('nogal')) { ?>
			<div id="galleriffic-wrap" class="clearfix">
				<div id="gallery" class="content">	
					<div id="caption" class="caption-container"></div>
					<div class="slideshow-container">
						<div id="loading" class="loader"></div>
						<div id="slideshow" class="slideshow"></div>	
					</div>
					<div id="controls" class="controls"></div>
				</div>
				<div id="thumbs" class="navigation">
					<ul class="thumbs">
						<?php while (next_image(true)): ?>
						<li>
							<?php if (isImageVideo()) { ?>
							<a class="thumb" href="<?php echo $_zp_themeroot; ?>/images/video-placeholder.jpg" title="<?php echo getBareImageTitle();?>">
							<?php } else { ?>
							<a class="thumb" href="<?php echo getDefaultSizedImage(); ?>" title="<?php echo getBareImageTitle();?>">
							<?php } ?>
								<?php printImageThumb(getAnnotatedImageTitle()); ?>
							</a>
							<div class="caption">	
								<div class="detail-download">
									<?php if (isImageVideo()) { $downLoadText=gettext('Video'); } else { $downLoadText=gettext('Image'); } ?>
									<a href="<?php echo htmlspecialchars(getImageLinkURL());?>" title="<?php echo gettext('Detail Page: '); ?><?php echo getImageTitle(); ?>"><?php echo $downLoadText.gettext(' Details'); ?></a>
									<?php if (getOption('download_link')) { ?><a target="_blank" href="<?php echo htmlspecialchars(getFullImageURL());?>" title="<?php echo gettext('Download: '); ?> <?php echo getImageTitle(); ?>"><?php echo gettext('Download ').$downLoadText; ?></a><?php } ?>				
								</div>	
								<div class="image-title"><?php printImageTitle(false); ?></div>
							</div>
						</li>
						<?php endwhile; ?>
					</ul>
				</div>
			</div>
			<!-- If javascript is disabled in the users browser, the following version of the album page will display  -->
			<noscript>
				<?php include("album_alt.php"); ?>
			</noscript>
			<!-- End of noscript display -->
			<?php } else { ?>
			<?php include("album_alt.php"); ?>
			<?php } ?>
			
			<?php } ?>

			<?php if (function_exists('printCommentForm')) { ?>
			<a class="fadetoggler"><?php echo gettext('Comments'); ?> (<?php echo getCommentCount(); ?>)</a>
			<?php } ?>
			<?php if (function_exists('printCommentForm')) { ?>
			<div id="comment-wrap" class="fader clearfix">
				<?php printCommentForm(); ?>
			</div>
			<?php } ?>
			
<?php include("footer.php"); ?>