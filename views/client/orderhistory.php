<h2>3 month Order History: <?=$data['client']['name']?> </h2>
 
<?php
//pr($data['client']);
?>
<table >
<tr>
	<th width="100"><a href="<?=url('client/orderhistory/'.$data['client']['client_id'].'/product_code')?>">Product code</a></th>
	<th width="200">Description</th>
	<th width="100"><a href="<?=url('client/orderhistory/'.$data['client']['client_id'])?>">QTY</a></th>
    <th width="300">&nbsp;</th>
</tr>
<?php if (is_array($data['result']) && count($data['result']) > 0):?>
	<?php foreach ($data['result'] as $i):?>
		<tr>
			<td><?=$i['product_code']?></td>
			
			<td><?=$i['description']?> </td>
			<td><?=$i['tqty']?></td>
            <td>&nbsp;</td>
		</tr>
	<?php endforeach;?>
<?php endif?>
</table>
  
  