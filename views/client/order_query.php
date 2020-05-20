<h2>Order Query</h2>

<?php
//pr($data['result']);
//pr($data['clientstock']);

?>


<table >
<tr>
<td colspan="8"><form method="post" action="">Find Order ID (eg 17456) <input  type="text" name="order_id"><input type="submit" name="b" value="Go" /></form></td></tr>
<tr>
	<th></th>
    <th width="100">Order ID</th>
    <th>#Item</th>
	<th width="200">Client</th>
	<th width="100">K9User</th>
    <th width="100">Status</th>
    <th>Date</th>
    <th width="100">Action</th>
</tr>


<?php if (is_array($data['result']) && count($data['result']) > 0):?>
	<?php foreach ($data['result'] as $i):?>
		<tr>
			<td align="right"><a target="_blank" href="<?=url('../catalog/?v=orderview&order_id=T0_'.$i['order_id'])?>" >view</a></td><td>T0_<?=$i['order_id']?></td>
            <td><?=$i['item_count']?></td>
			<td><?=$i['clientname']?></td>
			<td><?=$i['k9user']?></td>
            <td><?=$i['orderstatus']?></td>
            <td><?=date('d-m-Y',strtotime($i['modified']))?></td>
            <td align="right"><a href="<?=url('client/order_delete/'.$i['order_id'])?>" onclick="return confirm('Are you sure you want to really delete this order?')" >DELETE</a></td>
            
		</tr>
	<?php endforeach;?>
<?php endif?>
</table>
 
  