<h2>Clients Admin Page</h2>
<?php //pr($data);?>
<?php if (is_array($data['orphans'])):?>
<h3> Theses Clients do not have a sales rep assigned to them</h3>
<?php foreach ($data['orphans'] as $c):?>
<a href=""><?=$c['name']?> </a></br>     
<?php endforeach;?>

<?php endif;?>
	 	
<div id="clientTableGrid"></div>
<form action="<?=url('admin/clients/')?>" method="post" >
<input type="text" value="<?=$_POST['q']?>" name="q" size="20" >
<input type="submit" name="b" value="Go" >
</form>

<?php if (is_array($data['clients']) && count($data['clients']) >0 ):?>
<?php foreach ($data['clients'] as $c):?>
<a href="<?=url('admin/editclient/'.$c['client_id'])?>"><?=$c['name']?> </a></br> 
<?php endforeach;?>

<?php endif;?>
	
	
	
</div>

