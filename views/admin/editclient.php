<?php
 //pr($data);
 
?>
<h1>Edit Client</h1>
<form method="post">
<p> <input type="submit" name="b" value="Update" ></p>
<p><label for="salesrep_id">Sales rep</label><select name="salesrep_id" >
<?php foreach($data['salesreps'] as $r):?>
<?php $selected = $r['id']==$data['client']['salesrep_id']?" selected ":""?>
  <option value="<?=$r['id']?>" <?=$selected?> ><?=$r['name']?></option>
<?php endforeach;?>
</select>
</p>
<?php foreach($data['client'] as $k=>$v):?>
<?php if ($k=='salesrep_id'){continue;}?> 
 <?php if($k =='client_id'):?>
<input type="hidden" name="<?=$k?>" id="" value="<?=$v?>" /> 
 <?php else:?>
 <label for="<?=$k?>"><?=$k?></label>
 <input type="text" name="<?=$k?>" id="" value="<?=$v?>" /><br />
<?php endif;?>

<?php endforeach;?>

</form>