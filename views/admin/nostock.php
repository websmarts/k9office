<h4>No Stock Report</h4>
<?php
  //pr($data);
?>

<?php 
$r = $data['result'];
?>

<?php if (!empty($r)): ?>
<table>
	<?php foreach($r as $p):?>
	   
		<tr>
			<td><?=$p['product_code']?></td>
			<td><?=$p['description']?></td>
			<td><?=($p['qty_instock'])?></td>
		</tr>
	
	
	<?php endforeach;?>


<?php endif;?>

