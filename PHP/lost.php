<?php
	// Start session.
	session_start();
	
	// Set a key, checked in mailer, prevents against spammers trying to hijack the mailer.
	$security_token = $_SESSION['security_token'] = uniqid(rand());
	
	if ( ! isset($_SESSION['formMessage'])) {
		$_SESSION['formMessage'] = 'Fill in the form below to send in a request for a copy of your license.<br />You must enter a valid email address on file or your original transaction ID.';	
	}
	
	if ( ! isset($_SESSION['formFooter'])) {
		$_SESSION['formFooter'] = '';
	}
	
	if ( ! isset($_SESSION['form'])) {
		$_SESSION['form'] = array();
	}
	
	function check($field, $type = '', $value = '') {
		$string = "";
		if (isset($_SESSION['form'][$field])) {
			switch($type) {
				case 'checkbox':
					$string = 'checked="checked"';
					break;
				case 'radio':
					if($_SESSION['form'][$field] === $value) {
						$string = 'checked="checked"';
					}
					break;
				case 'select':
					if($_SESSION['form'][$field] === $value) {
						$string = 'selected="selected"';
					}
					break;
				default:
					$string = $_SESSION['form'][$field];
			}
		}
		return $string;
	}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="robots" content="all" />
		<meta name="generator" content="RapidWeaver" />
		<meta http-equiv="Cache-Control" content="max-age=1209600, must-revalidate" />
		<link rel="icon" href="http://pickadisk.com/favicon.ico" type="image/x-icon" />
		<link rel="shortcut icon" href="http://pickadisk.com/favicon.ico" type="image/x-icon" />
		<link rel="apple-touch-icon" href="http://pickadisk.com/apple-touch-icon.png" />
		
		<title>Lost License</title>
		<link title="default" rel="Stylesheet" type="text/css" href="../rw_common/themes/badnas-pms/styles.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/width/800.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/grad/dark.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/header/header-custom1.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/skin/shadow.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/title/title-left.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/logo/logo-header.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/sidebar/hide.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/font-size/medium.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/font-family/helvetica.css" />
		
		<link rel="stylesheet" type="text/css" media="screen" href="../rw_common/themes/badnas-pms/css/colortags-theme-default.css"  />                 		
		
		<script type="text/javascript" src="../rw_common/themes/badnas-pms/javascript.js"></script>
		
		
		
		<!--[if lte IE 6]>
			<script>
                		var blankImg = "../rw_common/themes/badnas-pms/images/blank.gif";
                		var blankSrc = "../rw_common/themes/badnas-pms/images/blank.gif";
			</script>

			<style type="text/css" media="screen">
			
				#grad, img, #skin {behavior: url(../rw_common/themes/badnas-pms/scripts/iepngfix.htc);}
	
				#logo img, #title img { behavior: url(../rw_common/themes/badnas-pms/scripts/pngbehavior.htc); }
			
			</style>

			<link title="default" rel="Stylesheet" type="text/css" href="../rw_common/themes/badnas-pms/css/ie6.css">
		<![endif]-->

		<!--[if IE 7]>
		<link title="default" rel="Stylesheet" type="text/css" href="../rw_common/themes/badnas-pms/css/ie7.css">
		<![endif]-->
		<script type="text/javascript" src="../rw_common/themes/badnas-pms/scripts/shadedborder.js"></script>
		<script type="text/javascript">
			var corners_tlr         = RUZEE.ShadedBorder.create({ corner:6, edges:"tlr" });
		</script>
	</head>
	<body id="grad">
		<!--
			seyDoggy badNas r6 for RapidWeaver 3.6 - updated March 24, 2008
			for support issues, please contact
			http://www.seydesign.com/support/customersupport/index.php
		-->
		<div id="container">
			<div id="pageHeader">
				<div id="skin">
					<div id="title"><img src="../rw_common/images/PAD4x64.png" width="64" height="64" alt="Site logo"/>
						<div id="site_title">PickADisk</div>
						<div id="site_slogan">Media storage and retrieval software</div>
					</div><!-- #title -->
					<div id="toolbar_horizontal">
						<ul class="navlist"><li class="normal"><a href="../" rel="self" title="PickADisk">PickADisk</a></li><li class="normal"><a href="../rw/screenshots.html" rel="self" title="Screenshots">Screenshots</a></li><li class="normal"><a href="../rw/carousel.html" rel="self" title="Carousel">Carousel</a></li><li class="normal"><a href="../rw/download.html" rel="self" title="Download">Download</a></li><li class="normal"><a href="../rw/support.html" rel="self" title="Support">Support</a></li><li class="normal"><a href="../rw/bundle.html" rel="self" title="Purchase">Purchase</a></li></ul>
					</div><!-- #toolbar_horizontal -->
				</div><!-- #skin -->
			</div><!-- #pageHeader -->
			<div id="contentContainer">
				<div id="sidebarContainer">
					
					<div id="toolbar_vertical">
						<ul class="navlist"><li class="normal"><a href="../" rel="self" title="PickADisk">PickADisk</a></li><li class="normal"><a href="../rw/screenshots.html" rel="self" title="Screenshots">Screenshots</a></li><li class="normal"><a href="../rw/carousel.html" rel="self" title="Carousel">Carousel</a></li><li class="normal"><a href="../rw/download.html" rel="self" title="Download">Download</a></li><li class="normal"><a href="../rw/support.html" rel="self" title="Support">Support</a></li><li class="normal"><a href="../rw/bundle.html" rel="self" title="Purchase">Purchase</a></li></ul>
					</div><!-- #toolbar_vertical -->
					<div id="logo"><img src="../rw_common/images/PAD4x64.png" width="64" height="64" alt="Site logo"/></div>
					<div id="sidebar">
						<div class="sideHeader">
							
						</div><!-- #sideHeader -->
						
						<div id="plugin_sidebar">
							
						</div><!-- #plugin_sidebar -->
						
					</div><!-- #sidebar -->
				</div><!-- #sidebarContainer -->
				<div id="content">
					
<div class="message-text"><?php echo $_SESSION['formMessage']; unset($_SESSION['formMessage']); ?></div><br />

<form action="./lost_files/mailer.php" method="post" enctype="multipart/form-data">
	 <div>
		<label>Your Name:</label> <br />
		<input class="form-input-field" type="text" value="<?php echo check('element0'); ?>" name="form[element0]" size="40"/><br /><br />

		<label>Your Email:</label> *<br />
		<input class="form-input-field" type="text" value="<?php echo check('element1'); ?>" name="form[element1]" size="40"/><br /><br />

		<label>Transaction ID:</label> <br />
		<input class="form-input-field" type="text" value="<?php echo check('element2'); ?>" name="form[element2]" size="40"/><br /><br />

		<div style="display: none;">
			<label>Spam Protection: Please don't fill this in:</label>
			<textarea name="comment" rows="1" cols="1"></textarea>
		</div>
		<input type="hidden" name="form_token" value="<?php echo $security_token; ?>" />
		<input class="form-input-button" type="reset" name="resetButton" value="Reset" />
		<input class="form-input-button" type="submit" name="submitButton" value="Submit" />
	</div>
</form>

<br />
<div class="form-footer"><?php echo $_SESSION['formFooter']; unset($_SESSION['formFooter']); ?></div><br />

<?php unset($_SESSION['form']); ?>
					<br />
					
					<div id="breadcrumbcontainer">
						<a href="../">PickADisk</a>&nbsp;>&nbsp;<a href="lost.php">Lost License</a>&nbsp;>&nbsp;
					</div><!-- #breadcrumbcontainer -->
					<div id="footer">
						<p>&copy; 2008 polyMicro Systems <a href="#" id="rw_email_contact">Contact Us</a><script type="text/javascript">var _rwObsfuscatedHref0 = "mai";var _rwObsfuscatedHref1 = "lto";var _rwObsfuscatedHref2 = ":co";var _rwObsfuscatedHref3 = "nta";var _rwObsfuscatedHref4 = "ct@";var _rwObsfuscatedHref5 = "pol";var _rwObsfuscatedHref6 = "ymi";var _rwObsfuscatedHref7 = "cro";var _rwObsfuscatedHref8 = "sys";var _rwObsfuscatedHref9 = "tem";var _rwObsfuscatedHref10 = "s.c";var _rwObsfuscatedHref11 = "om";var _rwObsfuscatedHref = _rwObsfuscatedHref0+_rwObsfuscatedHref1+_rwObsfuscatedHref2+_rwObsfuscatedHref3+_rwObsfuscatedHref4+_rwObsfuscatedHref5+_rwObsfuscatedHref6+_rwObsfuscatedHref7+_rwObsfuscatedHref8+_rwObsfuscatedHref9+_rwObsfuscatedHref10+_rwObsfuscatedHref11; document.getElementById('rw_email_contact').href = _rwObsfuscatedHref;</script></p>
					</div><!-- #footer -->
					<div class="clearer"></div>
				</div><!-- #content -->
			</div><!-- #contenContainer -->
		</div><!-- #container -->
		<script type="text/javascript">
		if (!window.$) {
			window.$ = function(id) { return document.getElementById(id); }
		}
		var tabs = $('toolbar_horizontal').getElementsByTagName("li");
		for (var i=0; i < tabs.length; ++i) {
			corners_tlr.render(tabs[i]);
		}

		</script>
	<!-- Start Google Analytics -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-5312396-1");
pageTracker._initData();
pageTracker._trackPageview();
</script><!-- End Google Analytics -->
</body>
</html>