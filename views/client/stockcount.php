<h2>Instore Stock Count: <?=$data['client']['name']?> </h2>



<?php
// pr($_SERVER);
//pr($data['clientstock']);
// pr($data['last_orders']);

$month = (isSet($_GET['m']) && (int) $_GET['m'] > 0 && (int) $_GET['m'] < 25) ? (int) $_GET['m'] : 3;


?>
<h1>Reporting period: <?php echo $month; ?> month<?php echo $month !==1 ?'s':''; ?></h1>
<?php $monthOptions = [1,2,3,4,5,6,12,24];

foreach($monthOptions as $o){
    if($o !== $month){
        ?>
        <a href="<?php echo $_SERVER['SCRIPT_URI'].'?m='.$o?>"><?php echo $o ?> months</a> &nbsp; &nbsp; &nbsp;
        <?php
    } else {
        echo $o .' '.$months .'months  &nbsp; &nbsp; &nbsp;';
    }
    
}
?>
<p>&nbsp;</p>

<?php 
// if(is_array($data['last_orders']) && count($data['last_orders']) > 0){

//     $n = count($data['last_orders']);
//     if($n > 1){
//         echo '<p>Last '. $n . ' orders are listed below</p>';
//     } else {
//         echo '<p>LAST order is listed below</p>';
//     }
    
//     foreach($data['last_orders'] as $order){
//         _display_order($order);
//     }
// } else {
//     echo '<p>NO ORDERS IN THE PERIOD</p>';
// }

function _display_order($order) {
    echo '<hr>';
    _display_order_head($order['order']);
    _display_order_items($order['items']);
}

function _display_order_head($order){
    echo '<p style="font-size:120%; font-weight:bold; background: #ccc">'.$order['order_id'] . ' Date: ' .substr($order['modified'],0,10) .'</p>';
}

function _display_order_items($items){
    echo '<table>';
    echo '<tr>
            <th>Product</th>
            <th>Description</th>
            <th>Color</th>
            <th>Size</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
            </tr>';

    foreach($items as $i){
        echo '<tr>';
        echo '<td>'.$i['product_code'].'</td>';
        echo '<td>'.$i['description'].'</td>';
        echo '<td>'.$i['color_name'].'</td>';
        echo '<td>'.$i['size'].'</td>';
        echo '<td>'.$i['qty'].'</td>';
        echo '<td>'.$i['price'].'</td>';
        echo '<td>'.number_format($i['qty'] * $i['price']/100,2).'</td>';
        echo '</tr>';
    }

    echo '</table>';
}

?>
<form method="post" action="" id="geoform" name="stockcountForm" onsubmit="document.getElementById('submit_button').disabled = 1;">
<input type="submit" name="b" id="submit_button" value="Update"/>

<table >
<tr>
	<th width="100"><a href="<?=url('client/stockcount/'.$data['client']['client_id'].'/product_code?m='.$month)?>">Product code</a></th>
	<th width="200">Description</th>
	<th width="100"><a href="<?=url('client/stockcount/'.$data['client']['client_id'].'?m='.$month)?>">Purchased</a></th>
    <th width="100">Instore</th>
    <th width="100">Order</th>
</tr>
<?php if (is_array($data['result']) && count($data['result']) > 0):?>
	<?php foreach ($data['result'] as $i):?>
		<tr>
			<td><a href="<?=url('../catalog/?b=go&v=product_search&q='.$i['product_code'])?>" target="catalog"><?=$i['product_code']?></a></td>
			
			<td><?=$i['description']?> <?=$i['color_name']?> <?=$i['size']?></td>
			<td><?=$i['tqty']?></td>
            <td><input style="width:3em" type="number" name="instock[<?=$i['product_code']?>]" value="<?=$data['clientstock'][ $i['product_code']]['stock_count']?>" /></td>
            <td>
            <?php 
            // dont show order qty input if notify_when_instock = y
            
            
            if (strtolower($i['notify_when_instock']) =='y' && $i['qty_instock'] < 1 ){
                echo 'On notify</td>';
            } else {
                // provide a flag to let rep know if product can be backorded if insufficient qty instock
                $bo = $i['can_backorder'] =='y' ? ' (bo)' :'';
                if( $i['qty_instock'] >0 || $i['can_backorder'] =='y'){
                    echo '<input style="width:3em" type="number" name="order['.$i['product_code'].']" value="'.$data['clientstock'][ $i['product_code']]['suggested_order_qty'].'" /> <span style="font-size: 10px">'.$i['qty_instock'].$bo.'</span></td>';
                } else {
                    echo 'No Stock</td>';
                }
                
            }
            ?>
            
            
            
		</tr>
	<?php endforeach;?>
<?php endif?>
</table>
</form>  
  