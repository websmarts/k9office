<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>K9Homes Registration</title>
	<link rel="stylesheet" type="text/css" href="<?php url("css/reset.css");?>" /> 
	<link rel="stylesheet" type="text/css" href="<?php url("css/fonts.css");?>" /> 
	<link rel="stylesheet" type="text/css" href="<?php url("css/grids.css");?>" /> 
	<link rel="stylesheet" type="text/css" href="<?php url("css/base.css");?>" />
	<link rel="stylesheet" type="text/css" href="<?php url("css/main.css");?>" />
    <link rel="stylesheet" type="text/css" href="<?php url("css/rego.css");?>" />
</head>
<body >
<div id="doc">
	<div ><a href="<?php url("../");?>"><img src="<?php url('img/k9logo.jpg')?>"  /></a><h2> Online Registration</h2></div>
    <div><a href="<?php url("../");?>">Home</a></div>
	
	<?php if(isSet($data['flash_message'])) {
        echo '<p class="flash_message">'.$data['flash_message'].'</p>';
    }
    ?>
    <?php if(isSet($data['flash_error'])) {
        echo '<p class="flash_error">'.$data['flash_error'].'</p>';
    }
    ?>
    
	<div id="content"><div id="rego"><?=$content_for_layout?></div></div>
		
	<div id="ft"></div> 
</div>		
		
	<?php if(isSet($data['flash_debug'])) {
        echo '<p class="flash_debug">'.$data['flash_debug'].'</p>';
    }
    ?>	
</body>
</html>