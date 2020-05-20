<h2>Daily Run Sheet</h2>
<?php //pr($data);?>

<?php if ( isSet($data['user'])):?>
<script type="text/javascript">var authUser = <?=$data['user']?>;</script>
<?php endif;?>

<div style="width:240px;float:left">
<?php if (is_array($data['salesreps'])):?>
<select id="salesRep" >
      <option value="">Select Sales Rep</option>
<?php foreach($data['salesreps'] as $rep) :?>
	<?php $selected = $rep['id'] ==  $data['user']?" selected ":""?>
      <option <?=$selected?> value="<?=$rep['id']?>"><?=$rep['name']?></option>
<?php endforeach;?>
</select>
<p>&nbsp;</p>

<div id="datePicker">Date Selector - Default to TODAY date</div>
<p>&nbsp;</p>
 
</div> 


<?php endif;?>
 <div  style="margin-left:250px"><img src="<?=url('img/pleasewait.gif')?>" id="ajaxactivity" class="invisible"></div>
 <div id="travelform" class="subPanel hidden" style="margin-left:250px">
 			Start km: <input type="text" name="startkm" id="startkm"> End km: <input type="text" name="endkm" id="endkm"> <input type="button" id="saveTravelButton" value="Update" >
 		</div>
 <div id="runsheet" class="subPanel hidden" style="margin-left:250px"> 
 		
	 	<div id="clientAutoComplete" class="yui-ac " style="width:500px"> 
		    <label for="clientInput">Client:</label>&nbsp;<br /><input type="text" id="clientInput" value="" name="clientInput" class="yui-ac-input" />
		    <div id="clientContainer" class="yui-ac-container"></div>
		</div>
		<p>&nbsp;</p>
		
		<div id="DT"></div><!-- The customer list contacted today goes here -->
		<br/>
		<div><input type="button" name="b" value="Save" id="saveButton"> </div>  
        <p>&nbsp</p> 
        
        <div id="orderEntry" class="hidden">
        	<div id="orderItemAutoComplete" class="yui-ac " style="width:500px"> 
		    <label for="orderItemInput">Item:</label>&nbsp;<br /><input type="text" id="orderItemInput" value="" name="orderItemInput" class="yui-ac-input" />
		    <div id="orderItemContainer" class="yui-ac-container"></div> 
		    <br style="clear:both"/>
		    
		</div>
		<div id="OT"></div><!-- The  OrderDetails go here -->  </div>
        <p>&nbsp;</p>
         <input type="button" id="saveOrderButton" value="Save Order" class="hidden">
        <hr>
        <div id="orderList"></div><!-- The selected customer OrderList goes here --> 
        
         <p>&nbsp</p>
        
        
        </div>
        		
		<p>&nbsp;</p>
		
	    
	
	
</div>
<script  type="text/javascript" language="JavaScript1.5" src="<?php echo url('js/page_controllers/runsheet_controller.js')?>"></script>
