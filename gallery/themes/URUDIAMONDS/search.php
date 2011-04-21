<?php include ("header.php"); ?>

			<div id="headline" class="clearfix">
				<h4><span><?php printHomeLink('', ' | '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo getGalleryTitle();?></a> | <?php echo "<em>".gettext("Search")."</em>"; ?></h4>
				<table id="navbar" class="clr">
					<tr>
						<td width="100%">
							<div id="navbar-center">
								<span>
									<?php if (($total = getNumImages() + getNumAlbums()) > 0) {
									if (isset($_REQUEST['date'])){
										$searchwords = getSearchDate();
									} else { $searchwords = getSearchWords(); }
									echo '<p>'.sprintf(gettext('Total matches for <em>%1$s</em>: %2$u'), $searchwords, $total).'</p>'; }
									$c = 0; ?>
								</span>
							</div>
						</td>
					</tr>
				</table>
			</div>
			
			<?php if (isAlbumPage()) { ?>
			<div id="album-wrap" class="clearfix">
				<ul>
					<?php $x=1; while (next_album(true)): $c++; $lastcol=""; 
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
						<?php while (next_image(true)): $c++; ?>
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
			
			<?php if ($c == 0) { echo "<p>".gettext("Sorry, no image matches. Try refining your search.")."</p>"; } ?>

<?php include("footer.php"); ?>