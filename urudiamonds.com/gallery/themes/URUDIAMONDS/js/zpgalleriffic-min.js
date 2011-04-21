
			jQuery(document).ready(function($) {
				
				$('#comment-wrap').css('display', 'none');
				$('#exif-wrap').css('display', 'none');
				$('#comment-wrap').css('opacity', '0');
				$('#exif-wrap').css('opacity', '0');
				
				//For Comment slide
				$(".fadetoggler").click(function(){   
				$(this).next("#comment-wrap").fadeSliderToggle();
				});
				
				//For MetaData slide
				$(".fadetoggler-exif").click(function(){   
				$(this).next("#exif-wrap").fadeSliderToggle();
				});
				
				// Initially set opacity on thumbs and add
				// additional styling for hover effect on thumbs
				var onMouseOutOpacity = 0.67;
				$('ul.thumbs li').opacityrollover({
					mouseOutOpacity:   onMouseOutOpacity,
					mouseOverOpacity:  1.0,
					fadeSpeed:         'fast',
					exemptionSelector: '.selected'
				});
				$('li.no-gal-li').opacityrollover({
					mouseOutOpacity:   onMouseOutOpacity,
					mouseOverOpacity:  1.0,
					fadeSpeed:         'fast',
					exemptionSelector: '.selected'
				});
				$('#image-stat li').opacityrollover({
					mouseOutOpacity:   onMouseOutOpacity,
					mouseOverOpacity:  1.0,
					fadeSpeed:         'fast',
					exemptionSelector: '.selected'
				});
				var onMouseOutOpacityAlbums = 0.57;
				$('div#album-wrap ul li').opacityrollover({
					mouseOutOpacity:   onMouseOutOpacityAlbums,
					mouseOverOpacity:  1.0,
					fadeSpeed:         'fast'
				});
				$('div.opac').opacityrollover({
					mouseOutOpacity:   onMouseOutOpacityAlbums,
					mouseOverOpacity:  1.0,
					fadeSpeed:         'fast'
				});

				/****************************************************************************************/
			});
