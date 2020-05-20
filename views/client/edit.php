<h2>EDIT CLIENT </h2>
<?php
//pr($data);

$formDef = array(
    '0' => array(
        'name' => 'client_id',
        'type' => 'hidden',
    ),
    '1' => array(
        'name' => 'name',
        'label' => 'Client name:',
        'type' => 'text',
    ),
    '1.1' => array(
        'name' => 'contacts',
    ),
    '1.2' => array(
        'name' => 'phone',
    ),
    '1.21' => array(
        'name' => 'mobile',
    ),

    '3' => array(
        'name' => 'address1',
    ),
    '4' => array(
        'name' => 'address2',
    ),
    '4.1' => array(
        'name' => 'address3',
    ),
    '5' => array(
        'name' => 'city',
    ),
    '6' => array(
        'name' => 'postcode',
    ),
    '6.1' => array(
        'name' => 'state',
        'type' => 'select',
        'key' => 'key',
        'value' => 'value',
        'current_value_key' => 'state',
    ),
    '6.2' => array(
        'name' => 'phone',
    ),
    '6.3' => array(
        'name' => 'phone2',
    ),
    '6.4' => array(
        'name' => 'mobile',
    ),
    '6.5' => array(
        'name' => 'fax',
    ),
    '6.6' => array(
        'name' => 'contacts',
        'label' => 'Contacts - owner',
    ),
    '6.7' => array(
        'name' => 'contacts_2',
        'label' => 'Contacts - instore',
    ),
    '6.8' => array(
        'name' => 'contacts_3',
        'label' => 'Contacts - ordering',
    ),
    '7' => array(
        'name' => 'salesrep',
        'label' => 'K9 sales rep',
        'type' => 'select',
        'key' => 'id',
        'value' => 'name',
        'current_value_key' => 'salesrep_name',
    ),
    // '9' => array(
    //     'name' => 'call_planning_note',
    //     'type' => 'textarea',
    //     'style' => 'width:350px; height:6em',
    // ),
    // '10' => array(
    //     'name' =>'status'

    // ),
    '11' => array(
        'name' => 'level',
        'label' => 'Sales Level Code',
        'type' => 'select',
        'key' => 'value',
        'value' => 'value',
        'current_value_key' => 'level',

    ),
    '12' => array(
        'name' => 'call_frequency',
        'label' => 'Call cycle (days)',

    ),
    '13' => array(
        'name' => 'login_user',
        'label' => 'Login user (web)',

    ),
    '14' => array(
        'name' => 'login_pass',
        'label' => 'Login pass (web)',

    ),
    '15' => array(
        'name' => 'email_1',
        'label' => 'Email (1) contact',

    ),
    '15.1' => array(
        'name' => 'email_2',
        'label' => 'Email (2) contact',

    ),
    '15.3' => array(
        'name' => 'email_3',
        'label' => 'Email (3) contact',

    ),
    '16' => array(
        'name' => 'client_note',
        'label' => 'Client note',
        'type' => 'textarea',
        'style' => 'width:350px; height:6em',
    ),
    // '15' => array(
    //     'name' => 'online_contact',
    //     'label' => 'Online Contact'

    // ),
    // '16' => array(
    //     'name' => 'online_status',
    //     'label' => 'Online status',
    //     'type'=>'select',
    //     'key'=>'key',
    //     'value'=>'value'

    // ),
    // '17' => array(
    //     'name' => 'custom_freight',
    //     'label' => 'Custom freight (Y=1 N=0)',
    //     'style'=>'width:50px;'

    // ),
    // '18' => array (
    //     'name' =>'freight_notes',
    //     'label' => 'Freight notes',
    //     'type' =>'textarea',
    //     'style'=>'width:350px; height:6em'
    // )
);
?>

<p><a href="<?=url('client/add')?>"> Add a Client</a></p>
<form method="post">
<?php foreach ($formDef as $e): ?>
   <p>
   <?php $e['type'] = isset($e['type']) ? $e['type'] : 'text'; // defalut to text type?>

   <?php if ($e['type'] != 'hidden'): ?>
    <label class="formlabel"><?=isset($e['label']) ? $e['label'] : $e['name']?></label>
    <?php endif;?>

   <?php $type = isset($e['type']) ? strtolower($e['type']) : 'text'?>
   <?php if ($type == 'select'): ?>
    <?php //pr($data);?>
     <select  class="formfield" name="<?=$e['name']?>" >
        <?php foreach ($data[$e['name']] as $o): ?>
          <?php $selected = $o[$e['value']] == $data['formdata'][$e['current_value_key']] ? 'selected' : ''?>
          <option <?=$selected?> value="<?=$o[$e['key']]?>"><?=$o[$e['value']]?></option>
        <?php endforeach;?>
      </select>
   <?php elseif ($type == 'textarea'): ?>
      <textarea  style="<?=isset($e['style']) ? $e['style'] : ''?>" name="<?=$e['name']?>" ><?=isset($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?></textarea>
   <?php elseif ($type == 'hidden'): ?>
     <input  type="hidden" name="<?=$e['name']?>" value="<?=isset($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?>" >
   <?php else: ?>
    <input style="<?=isset($e['style']) ? $e['style'] : ''?>"  class="formfield" type="text" name="<?=$e['name']?>" value="<?=isset($data['formdata'][$e['name']]) ? $data['formdata'][$e['name']] : ''?>" >

   <?php endif;?>
   </p>
<?php endforeach;?>
<input type="submit" name="b" value="Update" />  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="b" value="Delete" />
</form>