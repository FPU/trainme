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

$hide_breadrumb_setting = theme_lambda_get_setting('hide_breadcrumb');
$hide_breadrumb = ((!isloggedin() or isguestuser()) and $hide_breadrumb_setting);
$standardlayout = FALSE;
if ($PAGE->theme->settings->block_layout == 1) {$standardlayout = TRUE;}
$sidebar = FALSE;
if ($PAGE->theme->settings->block_layout == 2) {$sidebar = TRUE;}
if (($sidebar) && (strpos($OUTPUT->body_attributes(), 'empty-region-side-pre') !== FALSE) && (strpos($OUTPUT->body_attributes(), 'editing') == FALSE)) {$sidebar = FALSE;}
if ($sidebar) {theme_lambda_init_sidebar($PAGE); $sidebar_stat = theme_lambda_get_sidebar_stat();}

if (right_to_left()) {
    $regionbsid = 'region-bs-main-and-post';
} else {
    $regionbsid = 'region-bs-main-and-pre';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google web fonts -->
    <?php require_once(dirname(__FILE__).'/includes/fonts.php'); ?>
</head>

<?php 
	$lambda_body_attributes = 'columns3';
	if ($sidebar) {$lambda_body_attributes .= ' sidebar-enabled '.$sidebar_stat;}
?>
<body <?php echo $OUTPUT->body_attributes("$lambda_body_attributes"); ?>>

<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<?php if ($sidebar) { ?>
<div id="sidebar" class="">
	<?php echo $OUTPUT->blocks('side-pre');?>
    <div id="sidebar-btn"><span></span><span></span><span></span></div>
</div>
<?php } ?>

<div id="wrapper">

<?php require_once(dirname(__FILE__).'/includes/header.php'); ?>

<!-- Start Main Regions -->
<div id="page" class="container-fluid">

    <div id ="page-header-nav" class="clearfix">
    	<?php if (!($hide_breadrumb)) { ?>
        <div id="page-navbar" class="clearfix">
            <div class="breadcrumb-nav"><?php echo $OUTPUT->navbar(); ?></div>
            <nav class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); echo $OUTPUT->context_header_settings_menu(); ?></nav>
        </div>
        <?php } ?>
    </div>

    <div id="page-content" class="row-fluid">
        <div id="<?php echo $regionbsid ?>" class="span9">
            <div class="row-fluid">
            	<?php if ($standardlayout) { ?>
                <section id="region-main" class="span8 pull-right">
                <?php } else { ?>
                <section id="region-main" class="span8">
                <?php } ?>
                    <?php
                    echo $OUTPUT->course_content_header();
                    echo $OUTPUT->main_content();
					if ($CFG->version >= 2017111300) {echo $OUTPUT->activity_navigation();}
                    echo $OUTPUT->course_content_footer();
                    ?>
                </section>
                <?php 
				if ($standardlayout) {echo $OUTPUT->blocks('side-pre', 'span4 desktop-first-column pull-left');}
				else if (!$sidebar) {echo $OUTPUT->blocks('side-pre', 'span4 desktop-first-column pull-right');}
				?>
            </div>
        </div>
        <?php echo $OUTPUT->blocks('side-post', 'span3'); ?>
    </div>
    
    <!-- End Main Regions -->

    <a href="#top" class="back-to-top"><i class="fa fa-chevron-circle-up fa-3x"></i><span class="lambda-sr-only"><?php echo get_string('back'); ?></span></a>

	</div>

	<footer id="page-footer" class="container-fluid">
		<?php require_once(dirname(__FILE__).'/includes/footer.php'); echo $OUTPUT->login_info();?>
	</footer>

    <?php echo $OUTPUT->standard_end_of_body_html() ?>

</div>

<!--[if lte IE 9]>
<script src="<?php echo $CFG->wwwroot;?>/theme/lambda/javascript/ie/iefix.js"></script>
<![endif]-->

</body>
</html>