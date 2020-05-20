/*
    append theis to ajax calls to get debgrrer to trigger
    'DBGSESSID=1d=1&'+
*/





var Controller =  function(){};

Controller.prototype.salesRep= '';

Controller.prototype.init = function(){
    	var that = this;
		/* 	This controller initialises the page and
		* 	listens and reacts to events
		*/

		/* page events:
		 *	Select product type
		 *
		 */
		 var onEvent = function(e,p) {
				console.log('Lead Controller heard event: ' + e + ' parameter =' + p);
				if (e == 'changeSalesRep') {
					// do something
						
				}
		 };
		 
		 TWD.Event.subscribe('changeSalesRep',onEvent,that);
		 
		 $E.on('salesRep','change',function(e){
		 	var salesRepSelector = $D.get('salesRep');
		 	this.salesRep = salesRepSelector[salesRepSelector.selectedIndex].value;
		    this.initClientTable()
		 },this,true);
		 
		 
		 //now start up ui
		 this.initClientTable();
}

Controller.prototype.initClientSelector = function() {
 	
 };
 
 Controller.prototype.getRepClients = function( ) {
	var that = this;
	var oCallback = {
    success : that.clientTable.onDataReturnReplaceRows,
    failure : that.clientTable.onDataReturnReplaceRows,
    scope :that.clientTable
	};
	this.clientTableDS.sendRequest('salesrep=' + this.salesRep , oCallback);
}
 
 Controller.prototype.initClientTable = function() {
	var myColumnDefs = [  
			{key: "name", label: "Client", sortable:true},
			{key: "value_rating", label: "Rating", sortable:true,parser:YAHOO.util.DataSource.parseNumber },
			{key: "sales", label: "YTD Sales", sortable:true, parser:YAHOO.util.DataSource.parseNumber},
			{key: "contacts", label: "Contacts"},
			{key: "last_contacted", label: "Last contact", sortable:true , formatter:YAHOO.widget.DataTable.formatDate},
			{key: "contact_before", label: "Contact before", sortable:true },
			{key: "alert", label: "Alert" }
			
			
		];
	    
		this.clientTableDS = new YAHOO.util.DataSource(baseurl+ 'client/listAll/' );
		this.clientTableDS.responseType = YAHOO.util.DataSource.TYPE_JSON;
		this.clientTableDS.connMethodPost = true;
		this.clientTableDS.responseSchema = {
			resultsList: "data",     
			fields: ["client_id",
			"name",
			"status",
			"address1",
			"address2",
			"city",
			"postcode",
			"phone_area_code",
			"phone",
			"mobile",
			"fax",
			"contacts",
			"value_rating",
			"call_interval",
			"alert",
			"last_contacted",
			"contact_before",
			"sales"
			]
		};
	
		var oConfigs = {
              paginator : 	new YAHOO.widget.Paginator({ 
	          				rowsPerPage    : 20 
    						}),
    		   initialRequest: 'salesrep='+this.salesRep			  
		}; 
		this.clientTable = new YAHOO.widget.DataTable("clientTableGrid",  myColumnDefs, this.clientTableDS, oConfigs);
		
			
		// Subscribe to events for row selection
		this.clientTable.subscribe("rowMouseoverEvent", this.clientTable.onEventHighlightRow);
		this.clientTable.subscribe("rowMouseoutEvent", this.clientTable.onEventUnhighlightRow);
		
 }
 

 
 /* Init the product type selector */
 
 



TWD.Controller = new Controller();

