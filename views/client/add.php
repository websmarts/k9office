<h2>Add A Client</h2>
<?php
  //pr($data['online_status']);
  
  $formDef = array(
        
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
        '2' => array (
        	'name' =>'status',
        ),
        '3' => array (
        	'name' =>'address1',
        ),
        '4' => array (
        	'name' =>'address2',
        ),
        '5' => array (
        	'name' =>'city',
        ),
        '6' => array (
        	'name' =>'postcode',
        ),
        '7' => array (
        	'name' =>'salesrep',
        	'type'=> 'select',
        	'key'=>'id',
        	'value'=>'name'
        	
        ),
        '8' => array (
        	'name' =>'call_frequency',
        ),
        '9' => array (
        	'name' =>'call_planning_note',
        	'type' =>'textarea',
        	'style'=>'width:350px; height:6em'
        ),
        '10' => array(
            'name' => 'level'
            
        ),
        '12' => array(
            'name' => 'myob_record_id'
            
        ),
        '13' => array(
            'name' => 'login_user',
            'label' => 'Email (login user)'
            
        ),
        '14' => array(
            'name' => 'login_pass',
            'label' => 'Password'
            
        ),
        '15' => array(
            'name' => 'online_contact',
            'label' => 'Online Contact'
            
            
        ),
        '16' => array(
            'name' => 'online_status',
            'label' => 'Online status',
            'type'=>'select',
            'key'=>'key',
            'value'=>'value'
            
            
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
   				
   			  <option <?=$selected?> value="<?=$o[$e['key']]?>"><?=$o[$e['value']]?></option>
   			<?php endforeach;?>
	    </select>
   <?php elseif ($type == 'textarea'):?>
      <textarea  style="<?=isSet($e['style']) ? $e['style'] : ''?>" name="<?=$e['name']?>" ><?=isSet($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?></textarea>
   <?php elseif ($type == 'hidden'):?>
     <input  type="hidden" name="<?=$e['name']?>" value="<?=isSet($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?>" >   
   <?php else :?>
   	<input class="formfield" type="text" name="<?=$e['name']?>" value="<?=isSet($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?>" >
   
   <?php endif;?>
   </p>
<?php endforeach;?>
<input type="submit" name="b" value="Add" />
</form>