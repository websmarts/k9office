<h2>Products Admin Page</h2>

<div class="yui-gc">
	<div class="yui-u first">
	 	<div id="typeAutoComplete" class="yui-ac" style="width:60%"> 
		    <label for="typeInput">Product group:</label>&nbsp;<input type="text" id="typeInput" value="" name="typeInput" class="yui-ac-input" />
		    <div id="typeContainer" class="yui-ac-container"></div>
		</div>
		<p>&nbsp;</p>
		<div id="productTableGrid"></div>
	
	</div>
	<div class="yui-u">
		<div id="categoryList">categoryList</div>
		<p>
			<input type="text" style="width:3em" id="catid">
			<input type="submit" id="addCategoryButton" value="+">
		</p>
		<div id="typeList">typeList</div>
		
		<div style="margin-top:20px;"><?php include_element('color_chart');?></div> 
	</div>
	
</div>
<script  type="text/javascript" src="<?php echo url('js/page_controllers/products_controller.js')?>"></script>
