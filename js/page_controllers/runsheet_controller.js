/*
    append theis to ajax calls to get debgrrer to trigger
    'DBGSESSID=1d=1&'+
*/





var Controller =  function(){};

Controller.prototype.salesRepId = undefined;
Controller.prototype.selectedClientId = 0;
Controller.prototype.contactDate = undefined;
Controller.prototype.tainted = false;
Controller.prototype.ajaxCallCounter = 0;


Controller.prototype.selectedProduct = {};

Controller.prototype.init = function(){

    	var that = this;
		/* 	This controller initialises the page and
		* 	listens and reacts to events
		*/
		this.initDatePicker();
		this.initOT();
		
		// start with the logged in user
		if(window.authUser){
			this.salesRepId = window.authUser;
		   	TWD.show('travelform');
	   		TWD.show('runsheet');
	   		this.getTravelData();
	   		this.initClientSelector();  
	   		this.initDT();
		}
		
		
		$E.on('salesRep','change', function () {	
			var el = $D.get('salesRep');
			this.salesRepId = el[el.selectedIndex].value;
			// show the client auto completer and the runsheet table
			if(this.salesRepId){
			   TWD.show('travelform');
			   TWD.show('runsheet');
			   this.getTravelData();
			   this.initClientSelector();  
			   this.initDT();		
			} else {
			  TWD.hide('travelform'); 
			  TWD.hide('runsheet');
			}
			
			
		},this,true);
		
		// Listen for SAVE button click and save runsheet details
		$E.on('saveButton','click', function(){
		      this.saveRunsheet();
		},this,true)
		
		// Listen for Update travel click and save runsheet details
		$E.on('saveTravel','click', function(){
		      this.saveTravel();
		},this,true)
		
		$E.on('saveOrderButton','click', function(){
		      this.saveOrder();
		},this,true)
		
		$E.on('saveTravelButton','click', function(){
		      this.saveTravel();
		},this,true)
		
		
		// Clear the autocomplete box onFocus
		$E.on('clientInput','focus', function(){
			$D.get('clientInput').value = '';
		});
		$E.on('orderItemInput','click', function(){
		cl('orderItemFocus')
			$D.get('orderItemInput').value = '';
		});
        
		/* page events:
		 *	Select product type
		 *
		 */
		 var onEvent = function(e,p) {
				cl('Event heard event: ' + e + ' parameter =' + p);
				if (e == 'listClientOrders') {
					this.openOrderList(p);
					TWD.show('addItemButton');
					TWD.show('orderEntry');
					this.initOrderItemSelector();
					//this.initOT();
						
				}
				if (e =='contactDateChange'){
					this.closeOrderList();			
					TWD.hide('addItemButton');				
					TWD.hide('orderEntry');
					if(this.salesRepId > 0){
						TWD.show('travelform');
						this.getTravelData();
					    TWD.show('runsheet');
						this.initClientSelector();  
						this.initDT(); // setup contact record table
					}	
					
				
				}
				
		 };
		 
		 
		 TWD.Event.subscribe('listClientOrders',onEvent,that);
		 TWD.Event.subscribe('contactDateChange',onEvent,that);
		
		 
}
// Open a table to list all the client Orders
Controller.prototype.openOrderList = function(clientId){
	var toCurrency = function(el, oRecord, oColumn, oData){		
		   el.innerHTML = Number(oData)/100;
	}
	var toDate = function(el, oRecord, oColumn, oData){	
			
			var oDate = oData;
       		el.innerHTML = oDate.getDate() + "-" +oDate.getMonth() + "-" +oDate.getFullYear();
	   
	}
	var myColumnDefs = [  
			
			{key: "date", label: "Date",  formatter: toDate, sortable: true },
			{key: "status", label: "Status"},
			{key: "value", label: "Value", formatter:toCurrency}
			
		];
	    
		this.OLDS = new YAHOO.util.DataSource(baseurl+ 'contact/getClientOrderList/' );
		this.OLDS.responseType = YAHOO.util.DataSource.TYPE_JSON;
		this.OLDS.connMethodPost = false;
		this.OLDS.responseSchema = {
			resultsList: "data",     
			fields: ["id",{key: "date",parser:YAHOO.util.DataSource.parseDate},"status","value"]
		};
	
		var oConfigs = {
              initialRequest : '?client_id=' + clientId + '&date=' + this.contactDate
		}; 
		this.OL = new YAHOO.widget.DataTable("orderList",  myColumnDefs, this.OLDS, oConfigs);
		
		this.OL.subscribe('cellClickEvent',function(ev) { 
			cl('rowSelect');
		    var target = YAHOO.util.Event.getTarget(ev);
		    //var column = this.OL.getColumn(target);
		    var record = this.OL.getRecord(target);
		    cl('show order id = '+ record.getData('id'));
		    
		    // open or update  the orderedit window
		    if(this.orderEditWindow && !this.orderEditWindow.closed ){
		       this.orderEditWindow.location.href = baseurl + "admin/orderedit/" + record.getData('id');
		       this.orderEditWindow.focus();
		    } else {
		        this.orderEditWindow = window.open(baseurl + "admin/orderedit/" + record.getData('id'),'orderEditwindow','width=1000,height=600');
		    }
		    
		    //var orderId =  record.getData('id');
		    //var action = column.key;
		    this.OL.unselectAllRows();           
       	    this.OL.selectRow(target);    
		        
		    },this,true); 

}

Controller.prototype.closeOrderList = function(){
   if (this.OL){
      this.OL.destroy();
   }
}



Controller.prototype.saveRecord = function (o){
    var sUrl = baseurl + 'contact/saverecord/';
    var postData = "id=" + o.getData('id') +
                   "&client_id=" + o.getData('client_id') +
                   "&contacts=" + escape(o.getData('contacts')) +
                   "&call_type=" + escape(o.getData('call_type')) +
                   "&note=" +  escape(o.getData('note')) +
                   "&call_by=" + this.salesRepId  +
                   "&call_datetime=" + this.contactDate ;
                   
   
    
    var callback =
	{
	  success: function(o) {/*success handler code*/ this.ajaxActivity(-1);o.argument[0].setData('tainted',undefined);this.DT.render()},
	  failure: function(o) {/*failure handler code*/ this.ajaxActivity(-1);},
	  argument: [o],
	  scope: this  
	}
    this.ajaxActivity(1);
	var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
}
// Save the runsheet record set
Controller.prototype.saveRunsheet = function(){
       cl('saving runsheet recordset');
       var rs = this.DT.getRecordSet();
       var records = rs.getRecords() 
       for (n = 0; n < records.length; n++){
              cl(records[n]);
               
              if (records[n].getData('tainted')){
                   this.saveRecord(records[n]);
              }           
             
       }
       this.tainted = false;
}


Controller.prototype.initDatePicker = function() {
    var d = new Date(); 
	this.contactDate = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate() ;   
	this.datePicker = new YAHOO.widget.Calendar("datePicker");
	var dateToLocaleString = function(dt, cal) {
                        var wStr = cal.cfg.getProperty("WEEKDAYS_LONG")[dt.getDay()];
                        var dStr = dt.getDate();
                        var mStr = cal.cfg.getProperty("MONTHS_LONG")[dt.getMonth()];
                        var yStr = dt.getFullYear();
                        return (wStr + ", " + dStr + " " + mStr + " " + yStr);
                }
	var mySelectHandler = function(type,args,obj){
	   
        var selected = args[0];
        var selDate = this.datePicker.toDate(selected[0]);
       
       
       // cl("SELECTED: " + dateToLocaleString(selDate, this.datePicker));
       // alert if there are unsaved changes in current runsheet
       
       if (this.tainted){
           alert('data has not been saved');
           this.saveRunsheet();
       }
       
        this.contactDate = selDate.getFullYear() + "-" +  (selDate.getMonth() + 1) + "-" + selDate.getDate();
        cl('firing event'); 
        TWD.Event.fire('contactDateChange');
       // update runsheet with the details for the new date
       	        
	}
	
	this.datePicker.selectEvent.subscribe(mySelectHandler,this,true);
	this.datePicker.render();

}
Controller.prototype.insertNewContact = function(ob) {

	// make sure the client is not already in list
	var rs = this.DT.getRecordSet();
   	var records = rs.getRecords();
    var doUpdate = 1;

   for (n = 0; n < records.length; n++){        
   		if(records[n].getData('client_id') == ob.client_id) {
   		     doUpdate = 0; // cancel update because client already in list
   		}     
   }
   if (doUpdate){
       this.DT.addRow({
              client_id:ob.client_id,
              name: ob.name,
              contacts:ob.contacts
              
	   });
	    
	    this.DT.render();
   }
   

}
Controller.prototype.initClientSelector = function() {
 	var that = this;
 	this.oACDSClient = new YAHOO.widget.DS_XHR(baseurl+"contact/listClients/", ["data","name"]);
	this.oACDSClient.queryMatchContains = true;
	this.oAutoCompClient = new YAHOO.widget.AutoComplete("clientInput","clientContainer", this.oACDSClient);
	this.oAutoCompClient.minQueryLength = 1;
	this.oAutoCompClient.forceSelection = true;
	this.oAutoCompClient.useIFrame = true;
	this.oAutoCompClient.useShadow = false;
	this.oAutoCompClient.maxResultsDisplayed = 30;
	 
	var selectHandler = function  (sType,aArgs) {
				 //cl(aArgs[2][1]);
				this.insertNewContact(aArgs[2][1]);                             
						        
	};
	
     this.oAutoCompClient.doBeforeSendQuery = function( sQuery ){
     	return sQuery;
        // return sQuery + "&salesrep_id=" + that.salesRepId; // uncomment if you want only reps clients to show
     };
     
     
	this.oAutoCompClient.itemSelectEvent.subscribe(selectHandler,this,true);                   
	this.oAutoCompClient.formatResult = function(oResultItem, sQuery) {
		 return oResultItem[1].name;
	};      
	this.oAutoCompClient.doBeforeExpandContainer = function(oTextbox, oContainer, sQuery, aResults) {
		var pos = YAHOO.util.Dom.getXY(oTextbox);
		pos[1] += YAHOO.util.Dom.get(oTextbox).offsetHeight + 2;
		YAHOO.util.Dom.setXY(oContainer,pos);
		return true;
	};	
 };
 Controller.prototype.initOrderItemSelector = function() {
 	var that = this;
 	this.oACDSOI = new YAHOO.widget.DS_XHR(baseurl+"product/findProducts/", ["data","product_code"]);
	this.oACDSOI.queryMatchContains = true;
	this.oAutoCompOI = new YAHOO.widget.AutoComplete("orderItemInput","orderItemContainer", this.oACDSOI);
	this.oAutoCompOI.minQueryLength = 1;
	this.oAutoCompOI.forceSelection = true;
	this.oAutoCompOI.useIFrame = true;
	this.oAutoCompOI.useShadow = false;
	this.oAutoCompOI.maxResultsDisplayed = 20;
	 
	var selectHandler = function  (sType,aArgs) {
				 cl(aArgs[2][1]);
				 this.selectedProduct = aArgs[2][1]; 
				 $D.get('orderItemInput').value = this.selectedProduct['product_code'] + ' : ' +  this.selectedProduct['description'];
				 cl('itemSelected');
				 this.insertNewLineItem();
				                            
						        
	};
	
     
     
     
	this.oAutoCompOI.itemSelectEvent.subscribe(selectHandler,this,true);                   
	this.oAutoCompOI.formatResult = function(oResultItem, sQuery) {
		 return '<img src="'+ baseurl +'img/products/tn_'+ oResultItem[1].typeid +'.jpg" width=50 />' + oResultItem[1].product_code + " : " + oResultItem[1].description;
	};      
	this.oAutoCompOI.doBeforeExpandContainer = function(oTextbox, oContainer, sQuery, aResults) {
		var pos = YAHOO.util.Dom.getXY(oTextbox);
		pos[1] += YAHOO.util.Dom.get(oTextbox).offsetHeight + 2;
		YAHOO.util.Dom.setXY(oContainer,pos);
		return true;
	};	
 };
Controller.prototype.initDT = function() {

	var formatDate = function(el, oRecord, oColumn, oData) {
	   
        el.innerHTML = YAHOO.util.Date.format(oData, {format:"DD/MM/YYYY"});
    
	}
	var myFormatter = function(el, oRecord, oColumn, oData){
		 //cl('myFormatter'); 
		var tainted = oRecord.getData('tainted');
		if (typeof(oData) == 'string'){
		   var html = oData.replace(/\n/g,'<br />');
		} else if (typeof(oData) == 'undefined'){
			var html ='';
		
		}
		
		
		if(tainted){
		 	el.innerHTML = '<span class="tainted">' + html + '</span>';
		} else {	
		   el.innerHTML =  html ;	   
		}
	
	}
	var myParser = function(oData){
	//cl('myParser');
	   if(oData == 'undefined' || oData =='null') {
		 	oData = '';
		}
		return oData;
	}
	
	var myColumnDefs = [  
			
			{key: "name", label: "Customer",formatter: myFormatter, sortable: true},
			{key: "contacts", label: "Contact", editor: "textbox" },
			{key: "call_type", label: "Call type", formatter:myFormatter, editor:YAHOO.widget.DataTable.editDropdown, editorOptions:{dropdownOptions:["","visit","delivery","phone","email","visit (No Contact)"]} },
			{key: "note", label: "Call notes", formatter: myFormatter, editor: "textarea"},
			{key:'unlink',label:'',formatter:function(elCell) {
		        elCell.innerHTML = '<img src="'+ baseurl +'img/unlink.png" title="remove client from this runsheet" />';
		        elCell.style.cursor = 'pointer';
		    }}	
		];
	    
		this.DTDS = new YAHOO.util.DataSource(baseurl+ 'contact/getRunsheet/' );
		this.DTDS.responseType = YAHOO.util.DataSource.TYPE_JSON;
		this.DTDS.connMethodPost = false;
		this.DTDS.responseSchema = {
			resultsList: "data",     
			fields: ["client_id","id","name",{key:"contacts",parser:myParser},{key:"note", parser:myParser},{key:"call_type", parser:myParser},"tainted"]
		};
	
		var oConfigs = {
              initialRequest : '?salesrep_id=' + escape(this.salesRepId) + "&date="+ escape(this.contactDate) 
		}; 
		this.DT = new YAHOO.widget.DataTable("DT",  myColumnDefs, this.DTDS, oConfigs);
		
		var url = baseurl +'contact/updateRunsheet'; 
		this.DT.subscribe("editorSaveEvent", function(o){
			//oArgs.editor , oArgs.newData , oArgs.oldData 
			cl('edit save event');
			var row =  o.editor.cell.parentNode;
			//$D.addClass(row,"tainted");
			// set tainted in recordset would be better
			o.editor.record.setData('tainted',1);
			this.tainted = true;
			this.DT.render();
		
		},this,true)
		
		// Subscribe to events for row selection
		this.DT.subscribe("rowMouseoverEvent", this.DT.onEventHighlightRow);
		this.DT.subscribe("rowMouseoutEvent", this.DT.onEventUnhighlightRow);
		
		this.DT.subscribe("cellClickEvent", this.DT.onEventShowCellEditor);
		
		
		this.DT.subscribe('cellClickEvent',function(ev) { 
		    var target = YAHOO.util.Event.getTarget(ev);

		    var column = this.DT.getColumn(target);
		    var record = this.DT.getRecord(target);
		    var contactRecordId =  record.getData('id');
		    var action = column.key;
		   
		    if (action == 'unlink' ) { 
		    
		            if(contactRecordId == undefined){
		               this.DT.deleteRow(target); 
		               return;
		            }
		            
		            var callData = 'contact_record_id=' + contactRecordId;		    
					function handleSuccess(o) {
							this.ajaxActivity(-1);
							// returns success or failure
							if(o.responseText !== undefined){
									try{ 
									    var response = YAHOO.lang.JSON.parse( o.responseText );                          
									}
									catch (e) {alert('JSON ERROR getting unlink Contact. Response= ' + o.responseText);
									}
									this.DT.deleteRow(target);
																	 
								}   
					};
			
					function handleFailure(o) {
						   this.ajaxActivity(-1);
						   alert('failure');
					};
					
					var callback = {	success:handleSuccess,
						  				failure:handleFailure,
						  				scope:this
					}; 
					var url = baseurl + 'contact/deleteRecord/';
					this.ajaxActivity(1);
					var cObj = YAHOO.util.Connect.asyncRequest('POST',url, callback, callData);	            
       
		        
		        } else if (action == 'name') {
		           // show order list for client
		           this.DT.unselectAllRows();
		           this.DT.selectRow(target);
		           this.selectedClientId = record.getData('client_id');
		           // uncomment if you want to get the client orders
		           //TWD.Event.fire('listClientOrders',record.getData('client_id'));
		        
		        }   
		    },this,true); 
		    
		    
 }
 Controller.prototype.insertNewLineItem = function() {
 //cl('insertNewLineItem')    
     this.OT.addRow({
              typeid: this.selectedProduct['typeid'], 
              id: this.selectedProduct['id'],
              product_code: this.selectedProduct['product_code'],
              description:  this.selectedProduct['description'],
              price: this.selectedProduct['price']
              
     });
     this.OT.render();

}
 Controller.prototype.initOT = function() {

	var formatDate = function(el, oRecord, oColumn, oData) {
	   
        el.innerHTML = YAHOO.util.Date.format(oData, {format:"DD/MM/YYYY"});
    
	}
	var myFormatter = function(el, oRecord, oColumn, oData){	
		var tainted = oRecord.getData('tainted');	
		if(tainted){
		 	el.innerHTML = '<span class="tainted">' + oData + '</span>';
		} else {
		   el.innerHTML = oData;
		}
	}
	
	var myImgFormatter = function(el, oRecord, oColumn, oData) {
		var typeid = oRecord.getData('typeid');
		cl("typeid="+typeid);
		el.innerHTML = '<img src="'+ baseurl +'img/products/tn_'+ typeid +'.jpg" width=50 />' 
	}
	var myParser = function(oData){
	
	   if(oData == 'undefined' || oData =='null') {
		 	oData = '';
		}
		return oData;
	}
	
	var myColumnDefs = [  
			{key: "typeid",formatter:myImgFormatter},
			{key: "product_code", label: "P.Code",formatter: myFormatter, sortable: true},
			{key: "description", label: "Description" },
			{key: "qty", label: "QTY",editor: "textbox"} ,
			{key: "price", label: "Price"},
			{key:'unlink',label:'',formatter:function(elCell) {
		        elCell.innerHTML = '<img src="'+ baseurl +'img/unlink.png" title="remove item from this order" />';
		        elCell.style.cursor = 'pointer';
		    }}	
		];
	    
		this.OTDS = new YAHOO.util.DataSource(
			[{}], 
			{responseType: YAHOO.util.DataSource.TYPE_JSARRAY}
		);
		this.OTDS.responseSchema = {     
			fields: ["product_code","id","description","price","typeid","tainted"]
		}; 
	
		var oConfigs = {
                initialLoad: false
		}; 
		this.OT = new YAHOO.widget.DataTable("OT",  myColumnDefs, this.OTDS, oConfigs);
		
		
		
		// Subscribe to events for row selection
		this.OT.subscribe("rowMouseoverEvent", this.OT.onEventHighlightRow);
		this.OT.subscribe("rowMouseoutEvent", this.OT.onEventUnhighlightRow);
		
		this.OT.subscribe("cellClickEvent", this.OT.onEventShowCellEditor);
		
		
		this.OT.subscribe('cellClickEvent',function(ev) { 
		    var target = YAHOO.util.Event.getTarget(ev);

		    var column = this.OT.getColumn(target);
		    var record = this.OT.getRecord(target);
		    var contactRecordId =  record.getData('id');
		    var action = column.key;
		   
		    if (action == 'unlink' ) { 
		    
		            if(contactRecordId == undefined){
		               this.OT.deleteRow(target); 
		               return;
		            }
		            
		            var callData = 'contact_record_id=' + contactRecordId;		    
					function handleSuccess(o) {
							// returns success or failure
							this.ajaxActivity(-1);
							if(o.responseText !== undefined){
									try{ 
									    var response = YAHOO.lang.JSON.parse( o.responseText );                          
									}
									catch (e) {alert('JSON ERROR getting unlink Contact. Response= ' + o.responseText);
									}
									this.OT.deleteRow(target);
																	 
								}   
					};
			
					function handleFailure(o) {
					    this.ajaxActivity(-1);
						alert('failure');
					};
					
					var callback = {	success:handleSuccess,
						  				failure:handleFailure,
						  				scope:this
					}; 
					var url = baseurl + 'contact/deleteRecord/';
					this.ajaxActivity(1);
					var cObj = YAHOO.util.Connect.asyncRequest('POST',url, callback, callData);	            
       
		        
		        } else if (action == 'typeid') {
		           // show order list for client
		           this.OT.unselectAllRows();
		           this.OT.selectRow(target);
		        
		        }   
		    },this,true); 
		    
		    
 }

 // Save the runsheet record set
Controller.prototype.saveOrder = function(){
this
	   cl('saving order recordset');
	   var myData =[];
	   myData['user_id'] =  this.salesRepId;
	   myData['client_id']= this.selectedClientId;
	   // include instructions
	   
	   var rs = this.OT.getRecordSet();
	   var records = rs.getRecords();
	    
	   for (n = 0; n < records.length; n++){
	          
	          // ToDo Convert to non jSON as php4 objectifies the data
	          
	          myData[n] = {
	          	id: records[n].getData('id'),
	          	product_code: records[n].getData('product_code'),
	          	price: records[n].getData('price'),
	          	qty: records[n].getData('qty')
	          
	          }; 
	                     
	         
	   }
	   // POST the data to save the order
	     var sUrl = baseurl + 'product/saveorder/';
    	 var postData = "json=" + YAHOO.lang.JSON.stringify(myData) + 
    	 				"&user_id=" + this.salesRepId + 
    	 				"&client_id=" + this.selectedClientId ;
    	 
                   
    cl('saving record');
    cl(postData);
    
    var callback =
	{
	  success: function(o) {/*success handler code*/ 
	  		this.ajaxActivity(-1);
	  		this.openOrderList(this.selectedClientId);// update the order history listing
	  		this.OT.initializeTable(); // clear the order table
	  		this.OT.render();
	  		},
	  failure: function(o) {/*failure handler code*/ this.ajaxActivity(-1);},
	  scope: this  
	}
    this.ajaxActivity(1);
	var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
	   
}

Controller.prototype.getTravelData = function() {
this.ajaxActivity(1);
   var callback =
	{
	  success: function(o) {/*success handler code*/
	  this.ajaxActivity(-1);
	  var r = YAHOO.lang.JSON.parse(o.responseText);
	  		$D.get('startkm').value = r.data.startkm?r.data.startkm:0;
	  		$D.get('endkm').value = r.data.endkm?r.data.endkm:0;   
	  		
	  		
	  		
	  		},
	  failure: function(o) {/*failure handler code*/ this.ajaxActivity(-1);;},
	  scope: this  
	}
	
	
	var postData =    "sales_rep_id=" + this.salesRepId + 
    	 				"&date=" + this.contactDate ;
    var sUrl = baseurl + 'contact/gettraveldata/'; 
	var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}
Controller.prototype.saveTravel = function() {
	this.ajaxActivity(1);
   var callback =
	{
	  success: function(o) {/*success handler code*/ 
	  		this.ajaxActivity(-1);
	  		
	  		},
	  failure: function(o) {this.ajaxActivity(-1);},
	  scope: this  
	}
	
	
	var postData = "sales_rep_id=" + this.salesRepId +"&traveldate=" +  this.contactDate + "&startkm=" + $D.get('startkm').value +"&endkm="+ $D.get('endkm').value;
    var sUrl = baseurl + 'contact/savetraveldata/'; 
	var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}
 
Controller.prototype.ajaxActivity = function (i){
    if(i){
      this.ajaxCallCounter += i;
      if(this.ajaxCallCounter < 0){
         this.ajaxCallCounter = 0;
      }
    } else {
      this.ajaxCallCounter = 0;
    }
    if(this.ajaxCallCounter){
       TWD.visible('ajaxactivity');  
    } else {
       TWD.invisible('ajaxactivity');
    }
}

TWD.Controller = new Controller();

