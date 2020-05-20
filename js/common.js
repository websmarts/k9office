var $D = YAHOO.util.Dom;   
var $E = YAHOO.util.Event; 
var TWD = window.TWD || {};// our namespace 


TWD.ajaxError = function(r){

	var str = '<p>' + r.replyCode + '</p>';
	str += '<p>' + r.errFile + '</p>'; 
	str += '<p>' + r.errLine + '</p>';
	 
	$D.get('ajaxError').innerHTML = str;
    TWD.show('ajaxError');
}

TWD.Event = function(){
	var events = {};
	return {
			subscribe: function(e,callback,obj) {
						if (events[e] == undefined ){
 					     	events[e] = new  YAHOO.util.CustomEvent(e); 
 					 	} 
 	 					events[e].subscribe(callback,obj,true); 
					},
			create: function(e) {
 					 if (events[e] == undefined ){
 					     events[e] = new  YAHOO.util.CustomEvent(e); 
 					 } 
				},
			fire:  function(e,p){
					if (events[e] != undefined ){
 					    events[e].fire(p); 
 					} 
			        
			},
			get: function() {
				return events; // debug
			}
	}

}(); // events object

// call the init method on every class in our TWD namespace
function start() {
	  
	
	TWD.hide = function (id){
				$D.addClass(id,'hidden'); 
			 };
	TWD.show = function (id){
				$D.removeClass(id,'hidden'); 
	}
	TWD.invisible = function (id){
				$D.addClass(id,'invisible'); 
			 };
	TWD.visible = function (id){
				$D.removeClass(id,'invisible'); 
	}
	
	TWD.parseJSON = function (jsonString) {

		try {
		    var r = YAHOO.lang.JSON.parse(jsonString);
		    if (r.replyCode == 200) { 
		       return r;
			} else {
		       alert('Error from server: replyCode=' + r.replyCode + "<br> replyText=" + r.replyText);
		    }
		    
		}
		catch (e) {
		    alert("parseJSON() error: Invalid json data");
		}
	}
	
	
	for (k in TWD) {
		if(typeof TWD[k].init == 'function') {
		 	TWD[k].init();
		}	
	}
	// start the page controller
	TWD.Controller.start();			
}
function launch() {
	  if (  typeof window.start == 'function') {
		start();
	 } 
}
function cl (d) { // console logger
    if (window.debug == false){
        return;
    }
    if (  window.debug == true && typeof console.log == 'function') {
		console.log(d);
	 } 
}

function myBuildUrl (record) {
	    cl('building url');
	    cl(record);
	    var url = '';
	    var cols = this.getColumnSet().keys;
	    for (var i = 0; i < cols.length; i++) {
	        if (1==1 || cols[i].isPrimaryKey) {
	            url += '&' + cols[i].key + '=' + escape(record.getData(cols[i].key));
	        }
	    }
	    return url;
};

window.debug = true;

$E.onDOMReady(launch);