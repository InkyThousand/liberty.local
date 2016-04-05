<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head><meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<title><?php echo Site::name() . ' : ' . Site::title() ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<?php
Action::run('theme_header');
Stylesheet::add('public/themes/' . Site::theme() . '/css/Template.css');
Stylesheet::add('public/themes/' . Site::theme() . '/css/Content.css');
Stylesheet::add('public/themes/' . Site::theme() . '/css/Navigation.css');
Stylesheet::add('public/themes/' . Site::theme() . '/css/Forms.css');
Stylesheet::add('public/themes/' . Site::theme() . '/css/Store.css');
Stylesheet::load();
Javascript::add('public/themes/' . Site::theme() . '/js/script.js');
Javascript::load();
?>
<link rel="icon" href="<?php echo Site::root(); ?>favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo Site::root(); ?>favicon.ico" type="image/x-icon" />
</head>
<body>
<div id="container">
<div id="header">
<div id="header-logo">
<div id="logo"><h1>Liberty Pumps</h1><a href="/"><span>Home</span></a></div>
</div>
<div id="header-search-secondarynav">
<div id="search-container">
<div id="search-box">
<div class="search-box-content">
<div class="search-box-text">SEARCH</div>
<div class="search-box-field"><form><input type="text" id="kw" name="kw" maxlength="100" value="Keyword/Product Name" class="kw"
onfocus="if(this.value=='Keyword/Product Name'){this.value=''}" onblur="if(this.value==''){this.value='Keyword/Product Name'}"
onkeypress="return trapEnterKey(event,'searchSubmit()')"/></form></div>
<div class="search-box-button"><img src="<?php echo Site::themeRoot() ?>/Images/Buttons/btn-find.gif" onclick="searchSubmit()" style="cursor:pointer;border:0" alt="Find It"/></div>
</div>
</div></div>
<div id="topnav-container">
<div id="topnav">
</div>
</div>
</div>
</div>
<div id="body-container">
<div id="body-subcontainer">
<div id="main-container">