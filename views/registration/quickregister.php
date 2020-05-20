

<?php
  //pr($data);
?>


<table class="rego"><tbody>
<tr><td>Company name:</td><td><?=$data['client']['name']?></td></tr>
<tr><td>Address:</td><td>
<?=!empty($data['client']['address1']) ? $data['client']['address1'].'<br />' :'';?>
<?=!empty($data['client']['address2']) ? $data['client']['address2'].'<br />' :'';?>
<?=!empty($data['client']['address3']) ? $data['client']['address3'].'<br />' :'';?>
<?=$data['client']['city']?> <?=$data['client']['postcode']?><br />
<?=$data['client']['country']?><br />
</td></tr>
<tr><td>Phone:</td><td><?=$data['client']['phone']?></td></tr>


</tbody></table>

<p >You are completing an online registration for the company above. </p>
<p >To complete your online registration please complete the form below. </p>

<p>If you are not associated with this company then please <a href="<?=url('../?v=contactus')?>">contact us.</a></p>

<p>&nbsp;</p>
<form method="post">
<input type="hidden" name="quickregcode" value="<?=$data['quickregcode']?>" />
<div  class="row">
<label><?=$data['fieldLabels']['contact_name']?>:</label>
<input type="text" name="contact_name" value="<?=$_SESSION['formdata']['contact_name']?>" />
</div>
<div class="row">
<label><?=$data['fieldLabels']['email']?><br />Note your email address will be used as your login username</label>
<input type="text" name="email" value="<?=$_SESSION['formdata']['email']?>" />
</div>
<div  class="row">
<label><?=$data['fieldLabels']['pass1']?><br /> Note your password must be at least <?=MIN_PASSWORD_LENGTH?> character long</label>
<input type="text" name="pass1" value="<?=$_SESSION['formdata']['pass1']?>" />
</div>
<div  class="row center">
<input class="button" type="submit" name="b" value="continue" />
</div>
</form>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

