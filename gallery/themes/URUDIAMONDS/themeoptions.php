<?php

/* Plug-in for theme option handling 
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 * 
 */

require_once(SERVERPATH . "/" . ZENFOLDER . "/admin-functions.php");

class ThemeOptions {
	
	function ThemeOptions() {
		setOptionDefault('tagline', 'Welcome to URUdiamonds - Change this in Theme Options');
		setOptionDefault('zenpage_homepage', 'none');
		setOptionDefault('allow_search', true);
		setOptionDefault('show_archive', true);	
		setOptionDefault('show_credit', false);
		setOptionDefault('color_style', 'default-black');
		setOptionDefault('contrast', 'dark');
		setOptionDefault('use_image_logo', false);
		setOptionDefault('use_image_logo_filename', 'logo.gif');
		setOptionDefault('zp_latestnews', '1');
		setOptionDefault('zp_latestnews_trunc', '400');
		setOptionDefault('show_meta', '1');
		setOptionDefault('final_link', 'colorbox');
		setOptionDefault('nogal', '1');
		setOptionDefault('image_statistic', 'none');
		setOptionDefault('show_cats', true);
		setOptionDefault('download_link', true);
	}
	
	function getOptionsSupported() {
		return array(	
						gettext('Tagline') => array('key' => 'tagline', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1, 'desc' => gettext('The text below the sitename on the home page (if no tusing an image) and in the copyright footer.')),
						gettext('Homepage') => array('key' => 'zenpage_homepage', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext("Choose here any <em>unpublished Zenpage page</em> (listed by <em>titlelink</em>) to act as your site's homepage instead the normal gallery index.")),
						gettext('Allow Search') => array('key' => 'allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable search form.')),
						gettext('Show Archive Link') => array('key' => 'show_archive', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Display a menu link to the Archive list.')),
						gettext('Show ZenPhoto Credit') => array('key' => 'show_credit', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to display the Powered by ZenPhoto link in the footer.')),
						gettext('Color') => array('key' => 'color_style', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Choose the primary color style. Easily customize by choosing custom and then adding the hex values in the custom.css file.')),
						gettext('Contrast') => array('key' => 'contrast', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Choose a dark or light contrast style for your gallery.')),
						gettext('Use Image as Title') => array('key' => 'use_image_logo', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Use an image for the logo/title area. If checked, enter image filename below.')),
						gettext('Use this File as Title') => array('key' => 'use_image_logo_filename', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1, 'desc' => gettext('If using an image file for the logo/title area, enter the full filename (including extension) of the image file located in the images directory in zpGalleriffic.')),
						gettext('ZenPage Latest News') => array('key' => 'zp_latestnews', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1, 'desc' => gettext('If using Zenpage, number of latest news artciles to show on the gallery page.  Make sure you enter a valid number here!')),
						gettext('ZenPage Latest News Truncation') => array('key' => 'zp_latestnews_trunc', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1, 'desc' => gettext('If using Zenpage, and you have articles set to display on the home page, set here the number of characters to show in the article snippet (truncation).  Make sure you enter a valid number here!')),		
						gettext('Show Image EXIF Data') => array('key' => 'show_meta', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Show the Image MetaData slide on Image page.')),
						gettext('Final Image Link') => array('key' => 'final_link', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Choose final link action at image.php: no link, colorbox, or default zenphoto.')),
						gettext('Use Galleriffic Script') => array('key' => 'nogal', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Uncheck this to use the alternate album and search pages that do not use the Galleriffic Script. This alternate is also what is displayed when the user has javascript disabled in their browser.')),
						gettext('Show Image Statistic Strip') => array('key' => 'image_statistic', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Shows a strip of thumbs depending on your option selected on the gallery, news, pages, archive, and contact pages. NOTE: Anything other than random the image_statistic plugin must be activated.')),
						gettext('Show ZenPage Category List on Gallery Page') => array('key' => 'show_cats', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to list the news categories on the gallery page, if you are using Zenpage....')),
						gettext('Show Download Link') => array('key' => 'download_link', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Show a download link for the image on the album and search pages.'))

					);				
	}

	function handleOption($option, $currentValue) {

		if ($option == 'contrast') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";
			
			echo '<option value="dark"';
				if ($currentValue == "dark") { 
				echo ' selected="selected">Dark</option>\n';
				} else {
				echo '>Dark</option>\n';
				}
			
			echo '<option value="light"';
				if ($currentValue == "light") { 
				echo ' selected="selected">Light</option>\n';
				} else {
				echo '>Light</option>\n';
				}
				
			echo "</select>\n";
		}
		
		if ($option == 'color_style') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";
			
			echo '<option value="default-orange"';
				if ($currentValue == "default-orange") { 
				echo ' selected="selected">Default Orange</option>\n';
				} else {
				echo '>Default Orange</option>\n';
				}
			
			echo '<option value="blue"';
				if ($currentValue == "blue") { 
				echo ' selected="selected">Blue</option>\n';
				} else {
				echo '>Blue</option>\n';
				}
			
			echo '<option value="green"';
				if ($currentValue == "green") { 
				echo ' selected="selected">Green</option>\n';
				} else {
				echo '>Green</option>\n';
				}

			echo '<option value="custom"';
				if ($currentValue == "custom") { 
				echo ' selected="selected">Custom</option>\n';
				} else {
				echo '>Custom</option>\n';
				}
				
			echo "</select>\n";
		}
		
		if ($option == 'final_link') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";
			
			echo '<option value="colorbox"';
				if ($currentValue == "colorbox") { 
				echo ' selected="selected">Colorbox</option>\n';
				} else {
				echo '>Colorbox</option>\n';
				}
			
			echo '<option value="nolink"';
				if ($currentValue == "nolink") { 
				echo ' selected="selected">No Link</option>\n';
				} else {
				echo '>No Link</option>\n';
				}
			
			echo '<option value="standard"';
				if ($currentValue == "standard") { 
				echo ' selected="selected">Standard ZenPhoto</option>\n';
				} else {
				echo '>Standard ZenPhoto</option>\n';
				}
			
			echo '<option value="standard-new"';
				if ($currentValue == "standard-new") { 
				echo ' selected="selected">Standard ZenPhoto - New Window</option>\n';
				} else {
				echo '>Standard ZenPhoto - New Window</option>\n';
				}
				
			echo "</select>\n";
		}
		
		if ($option == 'image_statistic') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";
			echo '<option value="none"';
				if ($currentValue == "none") { 
				echo ' selected="selected">None</option>\n';
				} else {
				echo '>None</option>\n';
				}
			echo '<option value="random"';
				if ($currentValue == "random") { 
				echo ' selected="selected">Random</option>\n';
				} else {
				echo '>Random</option>\n';
				}
			echo '<option value="popular"';
				if ($currentValue == "popular") { 
				echo ' selected="selected">Popular</option>\n';
				} else {
				echo '>Popular</option>\n';
				}
			echo '<option value="latest"';
				if ($currentValue == "latest") { 
				echo ' selected="selected">Latest</option>\n';
				} else {
				echo '>Latest</option>\n';
				}
			echo '<option value="latest-date"';
				if ($currentValue == "latest-date") { 
				echo ' selected="selected">Latest-date</option>\n';
				} else {
				echo '>Latest-date</option>\n';
				}
			echo '<option value="latest-mtime"';
				if ($currentValue == "latest-mtime") { 
				echo ' selected="selected">Latest-mtime</option>\n';
				} else {
				echo '>Latest-mtime</option>\n';
				}		
			echo '<option value="mostrated"';
				if ($currentValue == "mostrated") { 
				echo ' selected="selected">Most Rated</option>\n';
				} else {
				echo '>Most Rated</option>\n';
				}
			echo '<option value="toprated"';
				if ($currentValue == "toprated") { 
				echo ' selected="selected">Top Rated</option>\n';
				} else {
				echo '>Top Rated</option>\n';
				}
			echo "</select>\n";
		}
						
		if($option == "zenpage_homepage") {
			$unpublishedpages = query_full_array("SELECT titlelink FROM ".prefix('zenpage_pages')." WHERE `show` != 1 ORDER by `sort_order`");
			if(empty($unpublishedpages)) {
				echo gettext("No unpublished pages available");
				// clear option if no unpublished pages are available or have been published meanwhile
				// so that the normal gallery index appears and no page is accidentally set if set to unpublished again.
				setOption("zenpage_homepage", "none", true); 
			} else {
				echo '<input type="hidden" name="'.CUSTOM_OPTION_PREFIX.'selector-zenpage_homepage" value=0 />' . "\n";
				echo '<select id="'.$option.'" name="zenpage_homepage">'."\n";
				if($currentValue === "none") {
					$selected = " selected = 'selected'";
				} else {
					$selected = "";
				}
				echo "<option$selected>".gettext("none")."</option>";
				foreach($unpublishedpages as $page) {
					if($currentValue === $page["titlelink"]) {
						$selected = " selected = 'selected'";
					} else {
						$selected = "";
					}
					echo "<option$selected>".$page["titlelink"]."</option>";
				}
				echo "</select>\n";
			}
		}
	}

}
?>