<?php
  //pr($data['typeoptions']['data']);
  $types = $data['types']['data'];
  
?>
<h2>Types</h2>
<form name="typeform" method="POST">
<?php if (is_array($types)):?>
	<select name="selected_typeid" id="type" >
	      <option value="">Select Product Type</option>
	<?php foreach($types as $type) :?>
	      <?php $selected ="" ;if($data['typeid'] && $data['typeid'] == $type->typeid){ $selected = " selected ";}?>
	      <option value="<?=$type->typeid?>" <?=$selected?> ><?=$type->name?></option>
	<?php endforeach;?>
	</select> 
	
	<input type=submit name=b value="edit" >&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=b value="delete" > <br />
<?php endif;?> 
	
	<input type=input name=newtype style="width:40em" >
	<input type="radio" name="new_display_format" value="v" >Vertical <input type="radio" name="new_display_format" value="h" > Horizontal
	<input type=submit name=b value="add" >
	<hr>
	
<?php if (isSet($data['typeform'])):?>
	<div style="background: #ccc">
	<p>Edit Form</p>
	<p>typeid=<?=isSet($data['typeform']['typeid'])?$data['typeform']['typeid']:''?></p>
	<input type="hidden" name="typeid" value="<?=isSet($data['typeform']['typeid'])?$data['typeform']['typeid']:''?>">
	<label>Name</label><p><input type="text" size="70" name="name" value="<?=isSet($data['typeform']['name'])?$data['typeform']['name']:''?>" style="width:40em" ></p>
	
	<input type="radio" name="display_format" <?=$data['typeform']['display_format']=='v'?' checked=1 ' :''?> value="v" >Vertical <input type="radio" name="display_format" <?=$data['typeform']['display_format']=='h'?' checked=1 ' :''?> value="h" > Horizontal
	
	
<?php if(isSet($data['typeoptions']['data']) && count($data['typeoptions']['data']) > 0):?>
<hr>
	<?php $n = 1; foreach($data['typeoptions']['data'] as  $opt):?>
	optcode: <input type=text name=opt_code[<?=$n?>] value="<?=$opt['opt_code']?>" >
	optclass: <input type=text name=opt_class[<?=$n?>] value="<?=$opt['opt_class']?>" > <br />
	<?php $n++; ?>
	<?php endforeach; ?>
	
<?php endif;?>
<p>&nbsp;</p>
<?php if ($data['typeform']['display_format']=='h'):?>
	optcode: <input type=text name=opt_code[0] value="" > 
    optclass: <input type=text name=opt_class[0] value="" > <br />
    <?php endif;?>	
<p>&nbsp;</p> 
<input type=submit name=b value="update" > 	
</div>	
<?php endif;?>


<?php if(isSet($data['typeproducts']) && count($data['typeproducts']['data']) > 0):?>
<?php // pr($data['typeproducts']['data']); ?>
<?php endif;?>
<?php if(isSet($data['typecategories']) && count($data['typecategories']['data']) > 0):?>
<?php // pr($data['typecategories']['data']); ?>
<?php endif;?>
	</form>
	

