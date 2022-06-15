<h2>Pending Client Notifications</h2>

<?php
//pr($data['result']);
//pr($data['clientstock']);

?>


<table id="myTable" class="tablesorter">
<thead>
<tr>
	<th width="100" class="sort">Client</th>
	<th width="100">Emails</th>
	<th width="200" class="sort">Product code</th>
	<th width="100">Description</th>
    <th width="100">Instock</th>
    <th width="100" class="sort">K9 User</th>
    <th width="100">Action</th>
</tr>
</thead>
<tbody>
<?php if (is_array($data['result']) && count($data['result']) > 0): ?>
	<?php foreach ($data['result'] as $i): ?>
		<tr>
			<td><?=$i['clientname']?></td>
			<td>
				<?=!empty($i['email1']) ? trim($i['email1']).',' :'' ?>
				<?=!empty($i['email2']) ? trim($i['email2']).',' :'' ?>
				<?=!empty($i['email3']) ? trim($i['email3']) :'' ?>
		</td>
            <td><a href="<?=url('../catalog/?b=go&v=product_search&q=' . $i['product_code'])?>" target="catalog"><?=$i['product_code']?></a></td>

			<td><?=$i['description']?></td>
			<td><?=$i['qty_instock']?></td>
            <td><?=$i['k9_user']?></td>
            <td><a href="<?=url('client/notify_delete/' . $i['record_id'])?>">Done</a></td>

		</tr>
	<?php endforeach;?>
<?php endif?>
</tbody>
</table>

