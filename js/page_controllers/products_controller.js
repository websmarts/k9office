/*
    append theis to ajax calls to get debgrrer to trigger
    'DBGSESSID=1d=1&'+
*/





var Controller =  function(){};

Controller.prototype.typeId = 0;

Controller.prototype.init = function(){
    	var that = this;
		/* 	This controller initialises the page and
		* 	listens and reacts to events
		*/

		/* page events:
		 *	Select product type
		 *
		 */
         
         console.log('starting product controller');
		 var onEvent = function(e,p) {
                
				console.log('Lead Controller heard event: ' + e + ' parameter =' + p);
                
				if (e == 'selectProductType') {
					this.typeId = p;
					this.initProductTable();
					this.initCategoryTable();
					this.initTypeTable();
                    
						
				}
		 };
		 
		 TWD.Event.subscribe('selectProductType',onEvent,that);
		 
		 
		 
		 //now start up ui
		 this.initProductTypeSelector();
}

Controller.prototype.initProductTypeSelector = function() {
    console.log('init type selector');
    console.log(baseurl+"product/listProductTypes/");
 	this.oACDSType = new YAHOO.widget.DS_XHR(baseurl+"product/listProductTypes/", ["data","name"]);
    //console.log(this.oACDSType);
	this.oACDSType.queryMatchContains = true;
	this.oAutoCompType = new YAHOO.widget.AutoComplete("typeInput","typeContainer", this.oACDSType);
	this.oAutoCompType.minQueryLength = 1;
	this.oAutoCompType.forceSelection = true;
	this.oAutoCompType.useIFrame = true;
	this.oAutoCompType.useShadow = false;
	 
	var selectHandler = function  (sType,aArgs) {
				var typeId = Number(aArgs[2][1]['typeid']); // force to number
				TWD.Event.fire('selectProductType',typeId);                                
						        
	};

	this.oAutoCompType.itemSelectEvent.subscribe(selectHandler,this,true);                   
	this.oAutoCompType.formatResult = function(oResultItem, sQuery) {
		 return oResultItem[1].name;
	};      
	this.oAutoCompType.doBeforeExpandContainer = function(oTextbox, oContainer, sQuery, aResults) {
		var pos = YAHOO.util.Dom.getXY(oTextbox);
		pos[1] += YAHOO.util.Dom.get(oTextbox).offsetHeight + 2;
		YAHOO.util.Dom.setXY(oContainer,pos);
		return true;
	};	
    console.log('inited product selector');
 };
 
 Controller.prototype.initProductTable = function() {
	var myColumnDefs = [  
			{key: "product_code", label: "Product code", sortable:true,editor: "textbox" },
			{key: "description", label: "Description", sortable:true,  editor:"textarea" },
			{key: "price", label: "Price", sortable:true, editor: "textbox" },
			{key: "qty_instock", label: "Instock", sortable:true,editor: "textbox" },
			{key: "cost", label: "Cost", sortable:true, editor: "textbox" },
			{key: "special", label: "Special", sortable:true, editor:YAHOO.widget.DataTable.editDropdown, editorOptions:{dropdownOptions:[0,1]} },    
			{key: "status", label: "Status", sortable:true, editor:YAHOO.widget.DataTable.editDropdown, editorOptions:{dropdownOptions:["active","in-active"]} }   
			
		];
	    alert('intProductTable');
		this.productTableDS = new YAHOO.util.DataSource(baseurl+ 'product/listTypeProducts/' );
		this.productTableDS.responseType = YAHOO.util.DataSource.TYPE_JSON;
		this.productTableDS.connMethodPost = true;
		this.productTableDS.responseSchema = {
			resultsList: "data",     
			fields: ["id","description","price","product_code","qty_instock","cost","special","status"]
		};
	
		var oConfigs = {
              initialRequest : 'typeId=' + this.typeId 
		}; 
		this.productTable = new YAHOO.widget.DataTable("productTableGrid",  myColumnDefs, this.productTableDS, oConfigs);
		
		var url = baseurl +'product/updateProduct/'; 
		this.patchForInCellEditing(this.productTable,url);
		
		// Subscribe to events for row selection
		this.productTable.subscribe("rowMouseoverEvent", this.productTable.onEventHighlightRow);
		this.productTable.subscribe("rowMouseoutEvent", this.productTable.onEventUnhighlightRow);
		
		this.productTable.subscribe("cellClickEvent", this.productTable.onEventShowCellEditor);
		this.productTable.subscribe("editorSaveEvent", function(){     
		    this.productTable.render(); 
		},this,true); 
 }
  Controller.prototype.initCategoryTable = function() {
	var myColumnDefs = [  
			{key: "name", label: "Category", sortable:true, },
			{key:'unlink',label:' ',formatter:function(elCell) {
				elCell.innerHTML = '<img src="'+ baseurl +'img/unlink.gif" title="unlink type category " />';
				elCell.style.cursor = 'pointer';
			}}
		];
	    
		this.categoryTableDS = new YAHOO.util.DataSource(baseurl+ 'product/listTypeCategories/' );
		this.categoryTableDS.responseType = YAHOO.util.DataSource.TYPE_JSON;
		this.categoryTableDS.connMethodPost = true;
		this.categoryTableDS.responseSchema = {
			resultsList: "data",     
			fields: ["id","name","description"]
		};
	
		var oConfigs = {
              initialRequest : 'typeId=' + this.typeId 
		}; 
		this.categoryTable = new YAHOO.widget.DataTable("categoryList",  myColumnDefs, this.categoryTableDS, oConfigs);
		
		
		
		// Subscribe to events for row selection
		this.categoryTable.subscribe("rowMouseoverEvent", this.categoryTable.onEventHighlightRow);
		this.productTable.subscribe("rowMouseoutEvent", this.categoryTable.onEventUnhighlightRow);
		
 }
 
  Controller.prototype.initTypeTable = function() {
	var myColumnDefs = [  
	        {key: "typeid", label: "typeid",  },
			{key: "opt_code", label: "Code", sortable:true, },
			{key: "opt_class", label: "Class", editor: "textbox", formatter: function(elCell, oRecord, oColumn, oData){
			        elCell.innerHTML = oData;
			        $D.addClass(elCell,oData);	        
			}},
			
			{key:'unlink',label:' ',formatter:function(elCell) {
				elCell.innerHTML = '<img src="'+ baseurl +'img/unlink.gif" title="unlink type option " />';
				elCell.style.cursor = 'pointer';
			}}
		];
	    
		this.typeTableDS = new YAHOO.util.DataSource(baseurl+ 'product/listTypeOptions/' );
		this.typeTableDS.responseType = YAHOO.util.DataSource.TYPE_JSON;
		this.typeTableDS.connMethodPost = true;
		this.typeTableDS.responseSchema = {
			resultsList: "data",     
			fields: ["typeid","opt_code","opt_class"]
		};
	
		var oConfigs = {
              initialRequest : 'typeId=' + this.typeId 
		}; 
		this.typeTable = new YAHOO.widget.DataTable("typeList",  myColumnDefs, this.typeTableDS, oConfigs);
		
		var url = baseurl +'product/updateTypeOption/'; 
		this.patchForInCellEditing(this.typeTable,url);
		
		
		// Subscribe to events for row selection
		this.typeTable.subscribe("rowMouseoverEvent", this.typeTable.onEventHighlightRow);
		this.typeTable.subscribe("rowMouseoutEvent", this.typeTable.onEventUnhighlightRow);
		this.typeTable.subscribe("cellClickEvent", this.typeTable.onEventShowCellEditor);
		this.typeTable.subscribe("editorSaveEvent", function(){     
		    this.typeTable.render(); 
		},this,true); 
		
 }
 
 Controller.prototype.patchForInCellEditing = function(table,url) {
	table.saveCellEditor = function() { // override standard function

		// ++++ this is the inner function to handle the several possible failure conditions
		    var onFailure = function (msg) {
		        alert(msg);

		// --------      on failure section
		        this.resetCellEditor();
		        this.fireEvent("editorRevertEvent",
		            {editor:this._oCellEditor, oldData:oldData, newData:newData}
		        );
		// --------      end of on failure section

		    };

		// +++ this comes from the original except for the part I cut to place in the function above.

		    if(this._oCellEditor.isActive) {
		        var newData = this._oCellEditor.value;
		        var oldData = this._oCellEditor.record.getData(this._oCellEditor.column.key);
		        
		        

		        if(this._oCellEditor.validator) {
		            newData = this._oCellEditor.validator.call(this, newData, oldData);
		            this._oCellEditor.value = newData;
		            if(newData === null ) {

		// this is where the contents of the inner function onFailure used to be.
		                onFailure('validation');
		                return;
		            }
		        }

		// ++++++ from here on I added new, except for the 'success' case pasted in.
		        if (newData != oldData){
		        	
		             YAHOO.util.Connect.asyncRequest(
		            'POST',
		            url,
		            {
		                success: function (o) {
		                    var r = TWD.parseJSON(o.responseText);
		                    if (r.replyCode == 200) {

		// --------     on success section

		                        this._oRecordSet.updateKey(this._oCellEditor.record, this._oCellEditor.column.key, newData);
		                        this.formatCell(this._oCellEditor.cell);
		                        this.resetCellEditor();
		                       
		                        this.fireEvent("editorSaveEvent",
		                            {editor:this._oCellEditor, oldData:oldData, newData:newData}
		                        );
		// --------     end of on success section

		                    } else {
		                        onFailure(r.replyText);
		                    }
		                },
		                failure: function(o) {
		                    onFailure(o.statusText);
		                },
		                scope: this
		            },
		            'newData=' + escape(newData) +'&oldData=' + escape(oldData) + myBuildUrl.call(this,this._oCellEditor.record) + '&field=' + this._oCellEditor.column.key+'&id='+this._oCellEditor.record.getData('id')
		        	);
		        } else { // new and old data the same!
		             	this.resetCellEditor();
				        this.fireEvent("editorRevertEvent",
				            {editor:this._oCellEditor, oldData:oldData, newData:newData}
				        );
		        }
		        
		    } else {
		    }
		};		

}
 
 
 /* Init the product type selector */
 
 



TWD.Controller = new Controller();

