		<div id="image-stat" class="clearfix">
			<h2 id="image-stat-title">
				<?php switch (getOption('image_statistic')) {
					case "random":
					echo gettext('Random Images');
				break;
					case "popular":
					echo gettext('Popular Images');
				break;
					case "latest":
					echo gettext('Latest Images');
				break;
					case "latest-date":
					echo gettext('Latest Images');
				break;
					case "latest-mtime":
					echo gettext('Latest Images');
				break;
					case "mostrated":
					echo gettext('Most Rated Images');
				break;
					case "toprated":
					echo gettext('Top Rated Images');
				break;
				} ?>
			</h2>
			<?php if (getOption('image_statistic')=='random') { 
			printRandomImages(9,null,'all','',75,75,true); 
			} else {
			printImageStatistic(9,getOption('image_statistic'),'',false,false,false,'','',75,75,true,false);
			} ?>
		</div>