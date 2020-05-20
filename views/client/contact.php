<h2>EDIT CLIENT </h2>
<?php
  //pr($data);
  
  $formDef = array(
        '0' => array (
        	'name' =>'client_id',
        	'type' =>'hidden'
        ),
        '1' => array (
        	'name' =>'name',
        	'label'=>'Client name:',
        	'type' =>'text'
        ),
        '1.1'=> array(
             'name'=>'contacts'
        ),
        '1.2'=> array(
             'name'=>'phone'
        ),
        '1.21'=> array(
             'name'=>'mobile'
        ),
        
        '3' => array (
        	'name' =>'address1',
        ),
        '4' => array (
        	'name' =>'address2'
        ),
        '5' => array (
        	'name' =>'city'
        ),
        '6' => array (
        	'name' =>'postcode'
        ),
       
        '13' => array(
            'name' => 'login_user',
            'label' => 'Email contact'     
        ),
        '14' => array(
            'name'=>'email_do_not_disturb',
            'label'=>'Do Not Send Emails',
            'type'=>'checkbox'
        )
  ) ;
?>


<form method="post">
<?php foreach($formDef as $e):?>
   <p>
   <?php $e['type'] = isSet($e['type']) ? $e['type'] : 'text'; // defalut to text type?>
   
   <?php if($e['type'] != 'hidden'):?>
   	<label class="formlabel"><?=isSet($e['label'])?$e['label']:$e['name']?></label>
   	<?php endif;?>
   	
   <?php  $type = isSet($e['type'])?strtolower($e['type']):'text' ?>
   <?php if($type == 'select'):?>
	   <select  class="formfield" name="<?=$e['name']?>" >
   			<?php foreach ($data[$e['name']] as $o):?> 
   				<?php $selected = $o[$e['value']] == $data['formdata'][$e['current_value_key']] ?  'selected' :''?>
   			  <option <?=$selected?> value="<?=$o[$e['key']]?>"><?=$o[$e['value']]?></option>
   			<?php endforeach;?>
	    </select>
   <?php elseif ($type == 'textarea'):?>
      <textarea  style="<?=isSet($e['style']) ? $e['style'] : ''?>" name="<?=$e['name']?>" ><?=isSet($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?></textarea>
   <?php elseif ($type == 'hidden'):?>
     <input  type="hidden" name="<?=$e['name']?>" value="<?=isSet($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?>" >   
   <?php elseif ($type == 'checkbox'):?>
    <?php $checked = isSet($data['formdata'][$e['name']]) && $data['formdata'][$e['name']] > 0 ? ' checked="checked" ' : ' ';?>
     <input type="checkbox" <?=$checked?> name="<?=$e['name']?>" /> 
   
   
   <?php else :?>
   	<input class="formfield" type="text" name="<?=$e['name']?>" value="<?=isSet($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?>" >
   
   <?php endif;?>
   </p>
<?php endforeach;?>
<input type="submit" name="b" value="Update" />  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="b" value="Delete" /> 
</form>