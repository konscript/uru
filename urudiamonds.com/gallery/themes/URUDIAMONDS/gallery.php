<?php include ("header.php"); ?>
	
		<div id="headline" class="clearfix">	
			<div class="headline-text"></div>
			<table id="navbar" class="clr">
					<tr>
						<td width="100%">
							<div id="navbar-center">
								<span>
									<?php echo gettext('Gallery Stats: ').$_zp_gallery->getNumImages().gettext(' Images in ').$_zp_gallery->getNumAlbums().gettext(''); ?>
								</span>
							</div>
						</td>
					</tr>
			</table>
		</div>
		
		<div id="album-wrap" class="clearfix">
			<ul>
				<?php $x=1; while (next_album()): $lastcol=""; 
				if ($x==3) {$lastcol=" class='lastcol'"; $x=0;} ?>
				<li<?php echo $lastcol; ?>>		
					<a class="album-thumb" href="<?php echo htmlspecialchars(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle();?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(),NULL,150,100,150,100); ?></a>
					<h4><a href="<?php echo htmlspecialchars(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle();?>"><?php printAlbumTitle(); ?></a></h4>
				</li>
				<?php $x++; endwhile; ?>					
			</ul>
		</div>
		
		<div id="pagination">
			<?php printPageListWithNav( '&lsaquo; Previous','Next &rsaquo;',false,'true','clearfix','',true,'5' ); ?>
		</div>
		<div class="headline-text"><?php printGalleryDesc(true); ?></div>
		<?php if ( (function_exists('printLatestNews')) && ((getNumNews()) > 0) ) {
		if ( (getOption('zp_latestnews') > 0) && (is_numeric(getOption('zp_latestnews'))) ) { ?>
		
		<?php } 
		} ?>
		

		
		
<?php include("footer.php"); ?>