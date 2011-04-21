				<div id="thumbs-nogal">
					<ul class="clearfix thumbs" id="no-gal-ul">
						<?php while (next_image(true)): ?>
						<li class="no-gal-li">
							<a class="thumb" href="<?php echo htmlspecialchars(getImageLinkURL());?>" title="<?php echo getBareImageTitle();?>">
								<?php printImageThumb(getAnnotatedImageTitle()); ?>
							</a>
						</li>
						<?php endwhile; ?>
					</ul>
				</div>