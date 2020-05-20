<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Admin</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php url("css/reset.css");?>" /> 
	<link rel="stylesheet" type="text/css" href="<?php url("css/fonts.css");?>" /> 
	<link rel="stylesheet" type="text/css" href="<?php url("css/grids.css");?>" /> 
	<link rel="stylesheet" type="text/css" href="<?php url("css/base.css");?>" />
	<link rel="stylesheet" type="text/css" href="<?php url("css/main.css");?>" />

<script>var baseurl ='<?url()?>';</script> 
	
	<!-- YAHOO source files --> 
<!-- Load the YUI Loader script: -->

<link  type="text/css" rel="stylesheet" href="http://yui.yahooapis.com/2.5.1/build/autocomplete/assets/skins/sam/autocomplete.css">
<link type="text/css" rel="stylesheet" href="http://yui.yahooapis.com/2.5.1/build/datatable/assets/skins/sam/datatable.css">
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/calendar/assets/skins/sam/calendar.css" />

<script  type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/element/element-beta-min.js"></script>
<script  type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/connection/connection-min.js"></script>
<script  type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/json/json-min.js"></script>

<script type="text/javascript" src="http://yui.yahooapis.com/2.5.2/build/calendar/calendar-min.js"></script>

<script  type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/autocomplete/autocomplete-min.js"></script>
<script  type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/datasource/datasource-beta-min.js"></script>
<script  type="text/javascript" src="http://yui.yahooapis.com/2.5.1/build/datatable/datatable-beta-min.js"></script>

<script type="text/javascript"> var debug = true;</script>
<script  type="text/javascript" src="<?php echo url('js/common.js')?>"></script> 

</head>
<body class="yui-skin-sam" >
<div id="doc3">
<a href="../../../">K9 Catalog</a> &nbsp;&nbsp;<a href="<?url('planner/index')?>">Call Report</a> &nbsp;&nbsp;<a href="<?url('admin/runsheet')?>">Runsheet</a> &nbsp;&nbsp;
<?php if($_SESSION['PASS']['user'] == 6 ):?>
 &nbsp;&nbsp;&nbsp; <a href="<?url('admin/products')?>">Products Manager</a>&nbsp;&nbsp;  
	&nbsp;&nbsp;<a href="<?url('admin/export/products')?>">Download Product Data</a>
	&nbsp;&nbsp;<a href="<?url('admin/nostock/')?>">No Stock Report</a> 
<?php endif; ?> 
	<hr>
	<div id="yui-main">
		
			<?=$content_for_layout?>
		
	</div>
	<div id="footer">- - -</div> 
</div>				
</body>
</html>