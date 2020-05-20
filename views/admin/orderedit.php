<h2>Edit Order</h2>
<?php //pr($data);?>
<div style="width:240px;float:left">
<?php if (is_array($data['salesreps'])):?>
<select id="salesRep" >
      <option value="">Select Sales Rep</option>
<?php foreach($data['salesreps'] as $rep) :?>
      <option value="<?=$rep['id']?>"><?=$rep['name']?></option>
<?php endforeach;?>
</select>
<p>&nbsp;</p>

<div id="datePicker">Date Selector - Default to TODAY date</div>
</div> 


<?php endif;?>
<div><input type="button" name="b" value="Save" id="saveButton"> </div>
 <div id="runsheet" class="subPanel hidden" style="margin-left:250px"> 
	 	<div id="clientAutoComplete" class="yui-ac " style="width:500px"> 
		    <label for="clientInput">Client:</label>&nbsp;<br /><input type="text" id="clientInput" value="" name="clientInput" class="yui-ac-input" />
		    <div id="clientContainer" class="yui-ac-container"></div>
		</div>
		<p>&nbsp;</p>
		
		
        
        <div id="orderEntry" class="hidden">
        	<div id="orderItemAutoComplete" class="yui-ac " style="width:500px"> 
		    <label for="orderItemInput">Item:</label>&nbsp;<br /><input type="text" id="orderItemInput" value="" name="orderItemInput" class="yui-ac-input" />
		    <div id="orderItemContainer" class="yui-ac-container"></div> 
		    <br style="clear:both"/>
		    
		</div>
		<div id="OT"></div><!-- The  OrderDetails go here -->  </div>
        <p>&nbsp;</p>
         <input type="button" id="saveOrderButton" value="Save Order" >
         
        
         <p>&nbsp</p>
        
        
        </div>
        		
		<p>&nbsp;</p>
		<P>When you edit the order you can change the following:
		<li>order date</li>
		<li>the rep the order is assigned to</li>
		<li>the items in the list - if the status is not-printed</li>
		
		</p>
	    
	
	
</div>
<!--<script  type="text/javascript" src="<?php echo url('js/page_controllers/orderedit_controller.js')?>"></script>-->
