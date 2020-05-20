<h2>Call Report</h2>

<?php
//pr($data['sales7']);//
?>

<style>
/* call planner styles */
.overdue {background: #fcc}
.overdue20 {background: #fcc}
.overdue50 {background: #fcc}
.overdue80 {background: #fcc}
.nextdue7 {background: #fff}
.nextdue14 {background: #fff}
.nextdue21 {background: #fff}

.selected a{font-size:120%; background:#333; color: white;padding:5px; margin:5px;}
#rep li {float: left;display:block;margin:2px;padding:4px;border:1px solid#ccc}

#resultstable {width:100%}
</style>

<form method="post">
<p><strong> Report period includes activity from the begining of the start_date up to the begining of the end-date </strong></p>
Start date:(yyyy-mm-dd): <input type="text" name="startdate" value="<?php echo $data['startDate'] ?>"/> &#160; &nbsp; End date (yyyy-mm-dd): <input type="text" name="enddate"  value="<?php echo $data['endDate'] ?>"/>  <input type="submit" name="b" value="go" />
</form>
<p></p>
<?php
if ($data['salesRange']) {
    //pr($data['salesRange']);
    //pr($data['startDate']);
    //pr($data['endDate']);

}
?>


<table cellpadding=0 cellspacing=0 >
<tr>
<th>Period</th>
<th width=120 >Who</th>
<th width="100">Calls</th>
<th width="100">Orders</th>
<th>Conversion rate</th>
<th>Average sale</th>
</tr>



<?php if ($data['salesRange']): ?>

<?php foreach ($data['salesreps'] as $rep): ?>
    <?php if ($rep['id'] == 10 || $rep['id'] == 13 || $rep['id'] == 6): // ignore Darren & Kerry and Sahju?>
		    <tr>
		    <td>Selected Date Range:</td>
		    <td><?=$rep['name']?></td>
		    <td><?=$data['salesRange'][$rep['id']]['calls']?></td>
		    <td><?=$data['salesRange'][$rep['id']]['orders']?></td>
		    <td><?=$data['salesRange'][$rep['id']]['calls'] > 0 ? number_format($data['salesRange'][$rep['id']]['orders'] / $data['salesRange'][$rep['id']]['calls'], 2) : 'no calls'?></td>
		    <td>$<?=$data['salesRange'][$rep['id']]['orders'] > 0 ? number_format($data['salesRange'][$rep['id']]['sales'] / ($data['salesRange'][$rep['id']]['orders'] * 100), 2) : ' no orders '?></td>
		    </tr>
		    <?php endif;?>
<?php endforeach;?>
<tr height=10 ><td height=10 colspan=6>&nbsp;</td></tr>
 <?php endif;?>

<?php foreach ($data['salesreps'] as $rep): ?>
	<?php if ($rep['id'] == 10 || $rep['id'] == 13 || $rep['id'] == 6): // ignore Darren & Kerry and Sahju?>
		        <tr>
		        <td>7 days</td>
		        <td><?=$rep['name']?></td>
		        <td><?=$data['sales7'][$rep['id']]['calls']?></td>
		        <td><?=$data['sales7'][$rep['id']]['orders']?></td>
		        <td><?=$data['sales7'][$rep['id']]['calls'] > 0 ? number_format($data['sales7'][$rep['id']]['orders'] / $data['sales7'][$rep['id']]['calls'], 2) : 'no calls'?></td>
		        <td>$<?=$data['sales7'][$rep['id']]['orders'] > 0 ? number_format($data['sales7'][$rep['id']]['sales'] / ($data['sales7'][$rep['id']]['orders'] * 100), 2) : ' no orders '?></td>
		        </tr>
		        <?php endif;?>
<?php endforeach;?>

<tr height=10 ><td height=10 colspan=6>&nbsp;</td></tr>
<?php foreach ($data['salesreps'] as $rep): ?>
<?php if ($rep['id'] == 10 || $rep['id'] == 13 || $rep['id'] == 6): // ignore Darren & Kerry and Sahju?>
		<tr>
		<td>90 days</td>
		<td><?=$rep['name']?></td>
		<td><?=$data['sales30'][$rep['id']]['calls']?></td>
		<td><?=$data['sales30'][$rep['id']]['orders']?></td>
		<td><?=$data['sales30'][$rep['id']]['calls'] > 0 ? number_format($data['sales30'][$rep['id']]['orders'] / $data['sales30'][$rep['id']]['calls'], 2) : 'no calls'?></td>
		<td>$<?=$data['sales30'][$rep['id']]['orders'] > 0 ? number_format($data['sales30'][$rep['id']]['sales'] / ($data['sales30'][$rep['id']]['orders'] * 100), 2) : 'no orders'?></td>
		</tr>
		<?php endif;?>
<?php endforeach;?>
</table>


<?php if (is_array($data['salesreps'])): ?>

<ul id="rep">
<?php foreach ($data['salesreps'] as $rep): ?>
	<?php if ($rep['id'] == $data['salesrep_id']) {
    $class = "selected";
} else {
    $class = "";
}
?>


      <li class="<?=$class?>"><a href="<?=url('planner/index/' . $rep['id'])?>" ><?=$rep['name']?></a></li>

<?php endforeach;?>
</ul>
<p style="clear:both">&nbsp;</p>
<?php endif;?>



<?php if (is_array($data['results']['data']) && count($data['results']['data'])): ?>
<table id="resultstable" cellpadding="0" cellspacing="0">
<tr>
	<?php if ($_SESSION['PASS']['user'] == 6): ?>
	<th width="80">&nbsp;</th>
	<?php endif?>
	<th>Client</th>
	<th>Contact</th>
    <th>Level</th>
	<th>Call Every<br/> (days)</th>
	<th>Last Ordered</th>
	<th>Last Called</th>
	<th>Call due <br/>in (days)</th>

	<th>Call planning note</th>
</tr>


<?php foreach ($data['results']['data'] as $r): ?>
<?php if ($r['call_frequency']): ?>
	<?php // determine row highlight class

//$due = $r['call_frequency'] - (time() - strtotime($r['lastcall']))/(3600*24) ;
$due = $r['duein'];
if ($due < 0) {
    // call is overdue
    $overdue = $due / $r['call_frequency'] * -1;
    if ($overdue > .8) {
        $class = 'overdue80';
    } elseif ($overdue > .5) {
        $class = 'overdue50';
    } elseif ($overdue > .2) {
        $class = 'overdue20';
    } else {
        $class = 'overdue';
    }
} else {

    if ($due > 21) {
        $class = '';
    } elseif ($due > 14) {
        $class = 'nextdue21';
    } elseif ($due > 7) {
        $class = 'nextdue14';
    } else {
        $class = 'nextdue7';
    }
}

?>
	<tr class="<?=$class?>">
		<?php if ($_SESSION['PASS']['user'] == 6): ?>
		<td><a target="fido" href="http://fido.k9homes.com.au/client/<?php echo $r['client_id']?>/edit">edit client</a><br><br>
			<a target="ecat" href="https://k9homes.com.au/catalog/?e=SelectClient&client_id=<?php echo $r['client_id']?>">view runsheet</a>
		</td>
		<?php endif;?>
		<td><?=$r['name']?></a></td>
		<td><?=$r['phone_area_code']?>  <?=$r['phone']?><br /> <?=$r['contact']?></td>
        <td><?=$r['level']?></td>
		<td><?=$r['call_frequency']?></td>
		<?php if (!empty($r['lastorderdate'])): ?>
			<td><?=date('j-m-Y', (strtotime($r['lastorderdate'])))?></td>
			<? else :?>
			<td>-</td>
		<?php endif;?>
		<?php if ($r['lastcall']): ?>
			<td><?=date('j-m-Y', (strtotime($r['lastcall'])))?></td>
			<td class=""><?=round($due)?></td>
		<?php else: ?>
			<td>-</td>
			<td class=""><?=$r['call_frequency']?></td>
		<?php endif;?>

		<td><?=$r['call_planning_note']?></td>
	</tr>
<?php endif;?>
<?php endforeach;?>
</table>
<?php endif;?>