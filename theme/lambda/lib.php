<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Parent theme: Bootstrapbase by Bas Brands
 * Built on: Essential by Julian Ridden
 *
 * @package   theme_lambda
 * @copyright 2018 redPIthemes
 *
 */
function theme_lambda_get_setting($setting, $format = false) {
    global $CFG;
    require_once($CFG->dirroot . '/lib/weblib.php');
    static $theme;
    if (empty($theme)) {
        $theme = theme_config::load('lambda');
    }
    if (empty($theme->settings->$setting)) {
        return false;
    } else if (!$format) {
        return $theme->settings->$setting;
    } else if ($format === 'format_text') {
        return format_text($theme->settings->$setting, FORMAT_PLAIN);
    } else if ($format === 'format_html') {
        return format_text($theme->settings->$setting, FORMAT_HTML, array('trusted' => true, 'noclean' => true));
    } else {
        return format_string($theme->settings->$setting);
    }
}

function theme_lambda_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('lambda');		
		if ($filearea === 'logo') {
            return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
        } else if ($filearea === 'pagebackground') {
            return $theme->setting_file_serve('pagebackground', $args, $forcedownload, $options);
        } else if ($filearea === 'header_background') {
            return $theme->setting_file_serve('header_background', $args, $forcedownload, $options);
        } else if ($filearea === 'category_background') {
            return $theme->setting_file_serve('category_background', $args, $forcedownload, $options);
		} else if ($filearea === 'slide1image') {
            return $theme->setting_file_serve('slide1image', $args, $forcedownload, $options);
        } else if ($filearea === 'slide2image') {
            return $theme->setting_file_serve('slide2image', $args, $forcedownload, $options);
        } else if ($filearea === 'slide3image') {
            return $theme->setting_file_serve('slide3image', $args, $forcedownload, $options);
        } else if ($filearea === 'slide4image') {
            return $theme->setting_file_serve('slide4image', $args, $forcedownload, $options);
        } else if ($filearea === 'slide5image') {
            return $theme->setting_file_serve('slide5image', $args, $forcedownload, $options);
        } else if ((substr($filearea, 0, 15) === 'carousel_image_')) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);		
		} else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

function lambda_set_pagewidth1($css, $pagewidth) {
    $tag = '[[setting:pagewidth]]';
    $replacement = $pagewidth;
    if (is_null($replacement)) {
        $replacement = '1600';
    }
    if ( ($replacement == "90") || ($replacement == "100") ) {
		$css = str_replace($tag, $replacement.'%', $css);
	} else {
		$css = str_replace($tag, $replacement.'px', $css);
	}
    return $css;
}

function lambda_set_pagewidth2($css, $pagewidth) {
    $tag = '[[setting:pagewidth_wide]]';
    if ($pagewidth == "100") {
        $replacement = 'body {background:none repeat scroll 0 0 #fff;padding-top:0;} @media(max-width:767px){body {padding-left: 0; padding-right: 0;} #page {padding: 10px 0;}} #wrapper {max-width:100%;width:100%;} #page-header {margin:0 auto;max-width:100%;} @media(min-width: 768px) {#page-header > .container-fluid {max-width: 90%;}} .container-fluid {padding: 0; max-width:100%} .navbar {background: none repeat scroll 0 0 [[setting:menufirstlevelcolor]];padding: 0;} .navbar-inner {margin: 0 auto; max-width: 90%;} .navbar .brand {margin-left:0;} .navbar #search {margin-right:0;} .pagelayout-frontpage header.navbar + .container-fluid > img.lambda-shadow {display: none;} .camera_wrap {margin-top: 0;} #page {margin: 0 auto; max-width: 90%;} #page-footer .row-fluid {margin: 0 auto; max-width: 90%;} .spotlight-full {margin-left: -5.75% !important; margin-right: -5.75% !important;} .socials-header .social_icons.pull-right {padding-right:10%;} .socials-header .social_contact {padding-left:10%;}';
		$css = str_replace($tag, $replacement, $css);
	}
	else { 
		$css = str_replace($tag, "", $css);
	}
    return $css;
}

function lambda_set_block_style($css, $block_style) {
    $tag = '[[setting:block_style]]';
    if ($block_style == "1") {
        $replacement = '.block {background: #fff; margin-bottom: 30px; border-color: [[setting:maincolor]]; border-radius: 3px; border-style: solid; border-width: 3px 0 0; box-shadow: 0 1px 1px -1px #888;} .block .header .title h2::before {background: transparent; color: [[setting:maincolor]];} #page-footer .block, #region-main .block {box-shadow: none;}';
		$css = str_replace($tag, $replacement, $css);
	}
	else if ($block_style == "2") {
        $replacement = '#block-region-side-pre .block, #block-region-side-post .block {background: #fff; border: none; padding-top: 0;} #block-region-side-pre .block .header, #block-region-side-post .block .header {margin: 0 -12px; background: #f2f2f2; box-shadow: 0 0 0 1px rgba(0,0,0,.1) inset; padding-right: 12px; padding-top: 4px; height: 33px;} .block .header .title {background: none; min-height: 27px !important;} #block-region-side-pre .block .header .title h2::before, #block-region-side-post .block .header .title h2::before {border-radius: 0; font-size: 20px; height: 31px; margin-left: 0; margin-right: 0; margin-top: -4px; padding-top: 4px; width: 34px; height: 30px !important;} #block-region-side-pre .block .header .title h2, #block-region-side-post .block .header .title h2 {padding-left: 10px;}';
		$css = str_replace($tag, $replacement, $css);
	}
	else { 
		$css = str_replace($tag, "", $css);
	}
    return $css;
}

function lambda_set_logo_res($css, $logo_res) {
    $tag = '[[setting:logo_res]]';
    if ($logo_res) {
        $replacement = 'a.logo img {max-height:90px;} @media(max-width:767px){a.logo img {max-height:75px;}} @media(max-width:480px){a.logo img {max-height:60px;}}';
		$css = str_replace($tag, $replacement, $css);
	}
	else { 
		$css = str_replace($tag, "", $css);
	}
    return $css;
}

function lambda_set_block_icons($css, $block_icons) {
    $tag = '[[setting:block_icons]]';
    if ($block_icons == "1") {
		$replacement = '.block .header .title h2::before {background: transparent; color: #999;}';
		$css = str_replace($tag, $replacement, $css);
	}
	else if ($block_icons == "2") { 
		$replacement = '.block .header .title h2:before {display: none;}';
		$css = str_replace($tag, $replacement, $css);
	}
	else { 
		$css = str_replace($tag, "", $css);
	}
    return $css;
}

function lambda_hide_category_background($css, $hide_category_background) {
    $tag = '[[setting:hide_category_background]]';
    if ($hide_category_background == "1") {
		$replacement = '.course_category_tree .collapsible-actions {margin-bottom: 25px; background: #fff; height: 0; padding: 0; border-bottom: none;} .course_category_tree .collapsible-actions:before {display: none;} .course_category_tree .collapsible-actions .collapseexpand {color: #555 !important; font-size: 1em; font-weight: 300;}';
		$css = str_replace($tag, $replacement, $css);
	}
	else { 
		$css = str_replace($tag, "", $css);
	}
    return $css;
}

function lambda_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

function theme_lambda_process_css($css, $theme) {
    $pagewidth = $theme->settings->pagewidth;
    $css = lambda_set_pagewidth1($css,$pagewidth);
	$css = lambda_set_pagewidth2($css,$pagewidth);
	$logo_res = $theme->settings->logo_res;
	$css = lambda_set_logo_res($css,$logo_res);
	$block_style = $theme->settings->block_style;
	$css = lambda_set_block_style($css,$block_style);
	$block_icons = $theme->settings->block_icons;
	$css = lambda_set_block_icons($css,$block_icons);
	$hide_category_background = $theme->settings->hide_category_background;
	$css = lambda_hide_category_background($css,$hide_category_background);
    // Set the Fonts.
    if ($theme->settings->font_body ==1) {
        $bodyfont = 'Open Sans';
    } else if ($theme->settings->font_body ==2) {
        $bodyfont = 'Arimo';
    } else if ($theme->settings->font_body ==3) {
        $bodyfont = 'Arvo';
	} else if ($theme->settings->font_body ==4) {
        $bodyfont = 'Bree Serif';
	} else if ($theme->settings->font_body ==5) {
        $bodyfont = 'Cabin';
	} else if ($theme->settings->font_body ==6) {
        $bodyfont = 'Cantata One';
	} else if ($theme->settings->font_body ==7) {
        $bodyfont = 'Crimson Text';
	} else if ($theme->settings->font_body ==8) {
        $bodyfont = 'Droid Sans';
	} else if ($theme->settings->font_body ==9) {
        $bodyfont = 'Droid Serif';
	} else if ($theme->settings->font_body ==10) {
        $bodyfont = 'Gudea';
	} else if ($theme->settings->font_body ==11) {
        $bodyfont = 'Imprima';
	} else if ($theme->settings->font_body ==12) {
        $bodyfont = 'Lekton';
	} else if ($theme->settings->font_body ==13) {
        $bodyfont = 'Nunito';
	} else if ($theme->settings->font_body ==14) {
        $bodyfont = 'Montserrat';
	} else if ($theme->settings->font_body ==15) {
        $bodyfont = 'Playfair Display';
	} else if ($theme->settings->font_body ==16) {
        $bodyfont = 'Pontano Sans';
	} else if ($theme->settings->font_body ==17) {
        $bodyfont = 'PT Sans';
	} else if ($theme->settings->font_body ==18) {
        $bodyfont = 'Raleway';
	} else if ($theme->settings->font_body ==19) {
        $bodyfont = 'Ubuntu';
	} else if ($theme->settings->font_body ==20) {
        $bodyfont = 'Vollkorn';
	} else if ($theme->settings->font_body ==21) {
        $bodyfont = 'Work Sans';}
	
	if ($theme->settings->font_body_size ==1) {
        $bodysize = '12px';
    } else if ($theme->settings->font_body_size ==2) {
        $bodysize = '13px';
	} else if ($theme->settings->font_body_size ==3) {
        $bodysize = '14px';
	} else if ($theme->settings->font_body_size ==4) {
        $bodysize = '15px';
	} else if ($theme->settings->font_body_size ==5) {
        $bodysize = '16px';}
		
	if ($theme->settings->font_heading ==1) {
        $headingfont = 'Open Sans';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==2) {
        $headingfont = 'Abril Fatface';
		$headingweight = '400';
    } else if ($theme->settings->font_heading ==3) {
        $headingfont = 'Arimo';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==4) {
        $headingfont = 'Arvo';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==5) {
        $headingfont = 'Bevan';
		$headingweight = '400';
    } else if ($theme->settings->font_heading ==6) {
        $headingfont = 'Bree Serif';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==7) {
        $headingfont = 'Cabin';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==8) {
        $headingfont = 'Cantata One';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==9) {
        $headingfont = 'Crimson Text';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==10) {
        $headingfont = 'Droid Sans';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==11) {
        $headingfont = 'Droid Serif';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==12) {
        $headingfont = 'Gudea';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==13) {
        $headingfont = 'Imprima';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==14) {
        $headingfont = 'Josefin Sans';
		$headingweight = '400';
    } else if ($theme->settings->font_heading ==15) {
        $headingfont = 'Lekton';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==16) {
        $headingfont = 'Lobster';
    } else if ($theme->settings->font_heading ==17) {
        $headingfont = 'Nunito';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==18) {
        $headingfont = 'Montserrat';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==19) {
        $headingfont = 'Pacifico';
		$headingweight = '400';
    } else if ($theme->settings->font_heading ==20) {
        $headingfont = 'Playfair Display';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==21) {
        $headingfont = 'Pontano Sans';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==22) {
        $headingfont = 'PT Sans';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==23) {
        $headingfont = 'Raleway';
		$headingweight = '500';
    } else if ($theme->settings->font_heading ==24) {
        $headingfont = 'Sansita One';
		$headingweight = '400';
    } else if ($theme->settings->font_heading ==25) {
        $headingfont = 'Ubuntu';
		$headingweight = '700';
    } else if ($theme->settings->font_heading ==26) {
        $headingfont = 'Vollkorn';
		$headingweight = '700';
	} else if ($theme->settings->font_heading ==27) {
        $headingfont = 'Work Sans';
        $headingweight = '700';}
    
    $css = theme_lambda_set_headingfont($css, $headingfont, $headingweight);
    $css = theme_lambda_set_bodyfont($css, $bodyfont, $bodysize);
  
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = lambda_set_customcss($css, $customcss);
	
	if ($theme->settings->banner_font_color==0) {
        $banner_font_color = '#fff';
    } else if ($theme->settings->banner_font_color==1) {
        $banner_font_color = '#000';
    } else if ($theme->settings->banner_font_color==2) {
        $banner_font_color = '[[setting:maincolor]]';
    } else {
        $banner_font_color = null;
    }
    $css = theme_lambda_set_category_banner_font_color($css, $banner_font_color);
	
    if (!empty($theme->settings->maincolor)) {
        $maincolor = $theme->settings->maincolor;
    } else {
        $maincolor = null;
    }
    $css = theme_lambda_set_maincolor($css, $maincolor);

    if (!empty($theme->settings->mainhovercolor)) {
        $mainhovercolor = $theme->settings->mainhovercolor;
    } else {
        $mainhovercolor = null;
    }
    $css = theme_lambda_set_mainhovercolor($css, $mainhovercolor);
	
	if (!empty($theme->settings->linkcolor)) {
        $linkcolor = $theme->settings->linkcolor;
    } else {
        $linkcolor = null;
    }
    $css = theme_lambda_set_linkcolor($css, $linkcolor);

    if (!empty($theme->settings->def_buttoncolor)) {
        $def_buttoncolor = $theme->settings->def_buttoncolor;
    } else {
        $def_buttoncolor = null;
    }
    $css = theme_lambda_set_def_buttoncolor($css, $def_buttoncolor);

    if (!empty($theme->settings->def_buttonhovercolor)) {
        $def_buttonhovercolor = $theme->settings->def_buttonhovercolor;
    } else {
        $def_buttonhovercolor = null;
    }
    $css = theme_lambda_set_def_buttonhovercolor($css, $def_buttonhovercolor);
	
    if (!empty($theme->settings->headercolor)) {
        $headercolor = $theme->settings->headercolor;
    } else {
        $headercolor = null;
    }
    $css = theme_lambda_set_headercolor($css, $headercolor);

    if (!empty($theme->settings->menufirstlevelcolor)) {
        $menufirstlevelcolor = $theme->settings->menufirstlevelcolor;
    } else {
        $menufirstlevelcolor = null;
    }
    $css = theme_lambda_set_menufirstlevelcolor($css, $menufirstlevelcolor);

    if (!empty($theme->settings->menufirstlevel_linkcolor)) {
        $menufirstlevel_linkcolor = $theme->settings->menufirstlevel_linkcolor;
    } else {
        $menufirstlevel_linkcolor = null;
    }
    $css = theme_lambda_set_menufirstlevel_linkcolor($css, $menufirstlevel_linkcolor);

    if (!empty($theme->settings->menusecondlevelcolor)) {
        $menusecondlevelcolor = $theme->settings->menusecondlevelcolor;
    } else {
        $menusecondlevelcolor = null;
    }
    $css = theme_lambda_set_menusecondlevelcolor($css, $menusecondlevelcolor);

    if (!empty($theme->settings->menusecondlevel_linkcolor)) {
        $menusecondlevel_linkcolor = $theme->settings->menusecondlevel_linkcolor;
    } else {
        $menusecondlevel_linkcolor = null;
    }
    $css = theme_lambda_set_menusecondlevel_linkcolor($css, $menusecondlevel_linkcolor);

    if (!empty($theme->settings->footercolor)) {
        $footercolor = $theme->settings->footercolor;
    } else {
        $footercolor = null;
    }
    $css = theme_lambda_set_footercolor($css, $footercolor);

    if (!empty($theme->settings->footerheadingcolor)) {
        $footerheadingcolor = $theme->settings->footerheadingcolor;
    } else {
        $footerheadingcolor = null;
    }
    $css = theme_lambda_set_footerheadingcolor($css, $footerheadingcolor);

    if (!empty($theme->settings->footertextcolor)) {
        $footertextcolor = $theme->settings->footertextcolor;
    } else {
        $footertextcolor = null;
    }
    $css = theme_lambda_set_footertextcolor($css, $footertextcolor);
	
    if (!empty($theme->settings->copyrightcolor)) {
        $copyrightcolor = $theme->settings->copyrightcolor;
    } else {
        $copyrightcolor = null;
    }
    $css = theme_lambda_set_copyrightcolor($css, $copyrightcolor);

    if (!empty($theme->settings->copyright_textcolor)) {
        $copyright_textcolor = $theme->settings->copyright_textcolor;
    } else {
        $copyright_textcolor = null;
    }
	$css = theme_lambda_set_copyright_textcolor($css, $copyright_textcolor);
	
	if (!empty($theme->settings->socials_color)) {
        $socials_color = $theme->settings->socials_color;
    } else {
        $socials_color = null;
    }
    $css = theme_lambda_set_socials_color($css, $socials_color);
	
	if (!is_null($theme->setting_file_url('category_background', 'category_background'))) {
		$background = $theme->setting_file_url('category_background', 'category_background');
	} else {
		$background = null;
	}
	$css = theme_lambda_set_category_banner_bg($css, $background);
	
	if (!is_null($theme->setting_file_url('header_background', 'header_background'))) {
		$background = $theme->setting_file_url('header_background', 'header_background');
	} else {
		$background = null;
	}
	if ($theme->settings->header_bg_repeat==1)  {
        $repeat = ' repeat 0 0';
    }
	else {
		$repeat = ' 50% 50% / cover';
	}
	$css = theme_lambda_set_header_bg($css, $background, $repeat);

    $setting = 'list_bg';
	if (is_null($theme->setting_file_url('pagebackground', 'pagebackground'))) {
    	global $OUTPUT;
		if ($theme->settings->list_bg==1)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/page_bg_02', 'theme');
			$repeat = 'no-repeat fixed 0 0';
			$size = 'cover';}
		else if ($theme->settings->list_bg==2)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/page_bg_blur01', 'theme');
			$repeat = 'no-repeat fixed 0 0';
			$size = 'cover';}
		else if ($theme->settings->list_bg==3)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/page_bg_blur02', 'theme');
			$repeat = 'no-repeat fixed 0 0';
			$size = 'cover';}
		else if ($theme->settings->list_bg==4)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/page_bg_blur03', 'theme');
			$repeat = 'no-repeat fixed 0 0';
			$size = 'cover';}
		else if ($theme->settings->list_bg==5)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/cream_pixels', 'theme');
			$repeat = 'repeat fixed 0 0';
			$size = 'auto';}
		else if ($theme->settings->list_bg==6)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/mochaGrunge', 'theme');
			$repeat = 'repeat fixed 0 0';
			$size = 'auto';}
		else if ($theme->settings->list_bg==7)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/skulls', 'theme');
			$repeat = 'repeat fixed 0 0';
			$size = 'auto';}
		else if ($theme->settings->list_bg==8)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/sos', 'theme');
			$repeat = 'repeat fixed 0 0';
			$size = 'auto';}
		else if ($theme->settings->list_bg==9)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/squairy_light', 'theme');
			$repeat = 'repeat fixed 0 0';
			$size = 'auto';}
		else if ($theme->settings->list_bg==10)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/subtle_white_feathers', 'theme');
			$repeat = 'repeat fixed 0 0';
			$size = 'auto';}
		else if ($theme->settings->list_bg==11)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/tweed', 'theme');
			$repeat = 'repeat fixed 0 0';
			$size = 'auto';}
		else if ($theme->settings->list_bg==12)  {
        	$pagebackground = $OUTPUT->pix_url('page_bg/wet_snow', 'theme');
			$repeat = 'repeat fixed 0 0';
			$size = 'auto';}
		else {
			$pagebackground = $OUTPUT->pix_url('page_bg/page_bg_01', 'theme');
			$repeat = 'no-repeat fixed 0 0';
			$size = 'cover';}
		$css = theme_lambda_set_pagebackground($css, $pagebackground, $setting);
		$css = theme_lambda_set_background_repeat($css, $repeat, $size);
    }

    $setting = 'pagebackground';
    $pagebackground = $theme->setting_file_url($setting, $setting);
    $css = theme_lambda_set_pagebackground($css, $pagebackground, $setting);
	
	$repeat = 'no-repeat fixed 0 0';
	$size = 'cover';
    if ($theme->settings->page_bg_repeat==1)  {
        $repeat = 'repeat fixed 0 0';
		$size = 'auto';
    }
    $css = theme_lambda_set_background_repeat($css, $repeat, $size);
	
    return $css;
}

function theme_lambda_set_headingfont($css, $headingfont, $headingweight) {
    $tag = '[[setting:headingfont]]';
    $replacement = $headingfont;
    if (is_null($replacement)) {
        $replacement = 'Open Sans';
    }
    $css = str_replace($tag, $replacement, $css);
    $tag = '[[setting:headingweight]]';
    $replacement = $headingweight;
    if (is_null($replacement)) {
        $replacement = '700';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_bodyfont($css, $bodyfont, $bodysize) {
    $tag = '[[setting:bodyfont]]';
    $replacement = $bodyfont;
    if (is_null($replacement)) {
        $replacement = 'Open Sans';
    }
    $css = str_replace($tag, $replacement, $css);
	$tag = '[[setting:bodysize]]';
    $replacement = $bodysize;
    if (is_null($replacement)) {
        $replacement = '13px';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_maincolor($css, $themecolor) {
    $tag = '[[setting:maincolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#e2a500';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_mainhovercolor($css, $themecolor) {
    $tag = '[[setting:mainhovercolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#c48f00';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_linkcolor($css, $themecolor) {
    $tag = '[[setting:linkcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#966b00';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_def_buttoncolor($css, $themecolor) {
    $tag = '[[setting:def_buttoncolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#8ec63f';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_def_buttonhovercolor($css, $themecolor) {
    $tag = '[[setting:def_buttonhovercolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#77ae29';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_headercolor($css, $themecolor) {
    $tag = '[[setting:headercolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#ffffff';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_menufirstlevelcolor($css, $themecolor) {
    $tag = '[[setting:menufirstlevelcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#323A45';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_menufirstlevel_linkcolor($css, $themecolor) {
    $tag = '[[setting:menufirstlevel_linkcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#ffffff';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_menusecondlevelcolor($css, $themecolor) {
    $tag = '[[setting:menusecondlevelcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#f4f4f4';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_menusecondlevel_linkcolor($css, $themecolor) {
    $tag = '[[setting:menusecondlevel_linkcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#444444';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_footercolor($css, $themecolor) {
    $tag = '[[setting:footercolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#323A45';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_footerheadingcolor($css, $themecolor) {
    $tag = '[[setting:footerheadingcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#f9f9f9';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_footertextcolor($css, $themecolor) {
    $tag = '[[setting:footertextcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#bdc3c7';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_copyrightcolor($css, $themecolor) {
    $tag = '[[setting:copyrightcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#292F38';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_copyright_textcolor($css, $themecolor) {
    $tag = '[[setting:copyright_textcolor]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#bdc3c7';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_socials_color($css, $themecolor) {
    $tag = '[[setting:socials_color]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#a9a9a9';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_category_banner_font_color($css, $themecolor) {
    $tag = '[[setting:category_banner_font_color]]';
    $replacement = $themecolor;
    if (is_null($replacement)) {
        $replacement = '#fff';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_category_banner_bg($css, $background) {
    global $OUTPUT;
    $tag = '[[setting:category_banner_bg]]';
    $replacement = $background;
    if (is_null($replacement)) {
        $replacement = $OUTPUT->pix_url('bg/category-bg', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_header_bg($css, $background, $repeat) {
    global $OUTPUT;
    $tag = '[[setting:header_bg]]';
    $replacement = 'url('.$background.')';
	$replacement .= $repeat;
    if (is_null($background)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_pagebackground($css, $pagebackground, $setting) {
    global $OUTPUT;
    $tag = '[[setting:pagebackground]]';
    $replacement = $pagebackground;
    if (is_null($replacement)) {
        $replacement = $OUTPUT->pix_url('page_bg/page_bg_01', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_lambda_set_background_repeat($css, $repeat, $size) {
    $tag = '[[setting:background-repeat]]';
    $css = str_replace($tag, $repeat, $css);
	$tag = '[[setting:background-size]]';
    $css = str_replace($tag, $size, $css);
    return $css;
}

function theme_lambda_init_sidebar(moodle_page $page) {
    user_preference_allow_ajax_update('theme_lambda_sidebar', PARAM_TEXT);
    $page->requires->yui_module('moodle-theme_lambda-sidebar', 'M.theme_lambda.sidebar.init', array());
}

function theme_lambda_get_sidebar_stat() {
    return get_user_preferences('theme_lambda_sidebar', '');
}

function theme_lambda_page_init(moodle_page $page) {
    $page->requires->jquery();
	$page->requires->jquery_plugin('jquery.easing.min.1.4', 'theme_lambda'); 
	$page->requires->jquery_plugin('camera_main_slider', 'theme_lambda');
    $page->requires->jquery_plugin('jquery.bxslider', 'theme_lambda');
	$page->requires->jquery_plugin('lambda_customjs', 'theme_lambda');
}