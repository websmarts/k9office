<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>

<head>

	<title>K9 Office</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">



	<link rel="stylesheet" type="text/css" href="<?php url("css/reset.css");?>" />

	<link rel="stylesheet" type="text/css" href="<?php url("css/fonts.css");?>" />

	<link rel="stylesheet" type="text/css" href="<?php url("css/grids.css");?>" />

	<link rel="stylesheet" type="text/css" href="<?php url("css/base.css");?>" />

	<link rel="stylesheet" type="text/css" href="<?php url("css/main.css");?>" />

    <script src="http://yui.yahooapis.com/3.4.1/build/yui/yui-min.js"></script>



    <script type="text/javascript" src="/office/js/jquery-latest.js"></script>

	<script type="text/javascript" src="/office/js/jquery.tablesorter.min.js"></script>

	<script>

	$(document).ready(function()

	    {

	        $("#myTable").tablesorter();

	    }

	);

	</script>

</head>

<body >

<div id="doc2" >

	<div id="header"></div>

	<div class="no-print">

	<a href="<?php url('../catalog/')?>">K9 Catalog</a>&nbsp; &nbsp;

	<?php if ($_SESSION['PASS']['user'] != 20): ?>

    <a href="<?php url('planner/index')?>">Call Report</a>&nbsp;&nbsp;

    <a href="<?php url('client/notifies')?>">Client Notifies</a>

	<?php endif;?>



    <!--<a href="<?php url('admin/runsheet')?>">Runsheet</a>&nbsp;&nbsp;-->

	<?php if ($_SESSION['PASS']['user'] == 6): ?>

 &nbsp;&nbsp;<br /><br /> <a href="<?url('admin/products')?>">Products Manager</a>&nbsp;&nbsp;

	&nbsp;&nbsp;<a href="<?php url('admin/export/products')?>">Get Product Data</a>

    &nbsp;&nbsp;<a href="<?php url('admin/export/clients')?>">Get Clients Data</a>

	&nbsp;&nbsp;<a href="<?php url('admin/export/clients?for=mc')?>">Get Clients Mailchimp Data</a>

    &nbsp;&nbsp;<a href="<?php url('admin/export/clientprices')?>">Get Client Prices</a><br /><br />



    &nbsp;&nbsp;<a href="<?php url('client/order_query')?>">Order Query</a>

	&nbsp;&nbsp;<a href="<?php url('admin/nostock/')?>">No Stock Report</a>

    &nbsp;&nbsp;<a href="<?php url('product/catalog/')?>">Printed Catalog</a>

	<?php endif;?>

	<hr>

	</div>



	<div id="content"><?php echo $content_for_layout ?></div>



	<div id="footer"></div>

</div>





</body>

</html>