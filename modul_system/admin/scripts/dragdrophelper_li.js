//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2010 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

/**
 * 
 * This file includes a script to be used to make lists drag n dropable.
 * The array arrayListIds is parsed, all li-elements are added
 * See the YUI dragdrop-list-example for further infos
 */

if(arrayListIds == null) {
	var arrayListIds = new Array();
}

(function() {
	var Dom = YAHOO.util.Dom;
	var Event = YAHOO.util.Event;
	var DDM = YAHOO.util.DragDropMgr;
	
	var posBeforeMove = -1;
	var ulBeforeMove = -1;
	
	var backgroundColor = "#FFFFFF";
	var paddingBottom = "0";

	//create namespaces
	var kajona = { };
	kajona.dragndroplistDashboard = {};
	//Basic functions
	kajona.dragndroplistDashboard.DDApp = {
		
		safeInit : function() {
			if(typeof YAHOO == "undefined") {
                window.setTimeout(kajona.dragndroplistDashboard.DDApp.safeInit(), 1000);
                return;
            }
            
            kajona.dragndroplistDashboard.DDApp.init();
		},
	
    	init: function() {
		   //iterate over all lists available
		   for(l=0; l<arrayListIds.length; l++) {
		   	   listId = arrayListIds[l];
		   	   if(listId == null) {
		   	      continue;
		   	   }
		   	      
	           //basic dnd list				
	           new YAHOO.util.DDTarget(listId);
			   
			   //load items in list
			   var arrayListItems = YAHOO.util.Dom.getChildren(listId);
			   for(i=0;i<arrayListItems.length;i=i+1) {
			   		Dom.setStyle(arrayListItems[i], "cursor", "move");
		 			new kajona.dragndroplistDashboard.DDList(arrayListItems[i].id);
		   	   }
		   }
    	},
    	
    	//this method behaves in a special way: count ALL widgets
    	//up till the searched one, because all widgets belong to the dashboard-module, not the column
	    getCurrentPos : function(idOfRow) {
	       posCounter = 0;
		   for(l=0; l<arrayListIds.length; l++) {
		   	   listId = arrayListIds[l];	
		       var arrayListItems = YAHOO.util.Dom.getChildren(listId);
			   for(i=0;i<arrayListItems.length;i=i+1) {
			   		posCounter++;
			 		if(arrayListItems[i].id == idOfRow) {
			 			return posCounter;
			 		}  
			   }
		   }
	    },
		
		getCurrentList : function(idOfRow) {
		   for(l=0; l<arrayListIds.length; l++) {
		   	   listId = arrayListIds[l];	
		   	   if(listId == null) {
		   	      continue;
		   	   }
		   	      
		       var arrayListItems = YAHOO.util.Dom.getChildren(listId);
			   for(i=0;i<arrayListItems.length;i=i+1) {
			 		if(arrayListItems[i].id == idOfRow) {
			 			return listId;
			 		}  
			   }
		   }
	    },
	    
	    resetUlBackground: function(destEl) {
	    	Dom.setStyle(destEl, "background-color", backgroundColor);
	    	Dom.setStyle(destEl, "padding-bottom", paddingBottom);
	    }
	};

	kajona.dragndroplistDashboard.DDList = function(id, sGroup, config) {
	    kajona.dragndroplistDashboard.DDList.superclass.constructor.call(this, id, sGroup, config);
	    var el = this.getDragEl();
	    Dom.setStyle(el, "opacity", 0.67); // The proxy is slightly transparent
	    this.goingUp = false;
	    this.lastY = 0;
	};

	YAHOO.extend(kajona.dragndroplistDashboard.DDList, YAHOO.util.DDProxy, {
	
	    startDrag: function(x, y) {
	        // make the proxy look like the source element
	        var dragEl = this.getDragEl();
	        var clickEl = this.getEl();
			
			//save the start-pos and ul
			posBeforeMove = kajona.dragndroplistDashboard.DDApp.getCurrentPos(clickEl.id);
			ulBeforeMove = kajona.dragndroplistDashboard.DDApp.getCurrentList(clickEl.id);
			backgroundColor = Dom.getStyle(ulBeforeMove, "background-color");
			paddingBottom = Dom.getStyle(ulBeforeMove, "padding-bottom");
			
	        Dom.setStyle(clickEl, "visibility", "hidden");
	        dragEl.innerHTML = clickEl.innerHTML;
	    },
	
	    endDrag: function(e) {
	        var srcEl = this.getEl();
	        var proxy = this.getDragEl();
	        // Show the proxy element and animate it to the src element's location
	        Dom.setStyle(proxy, "visibility", "");
	        var a = new YAHOO.util.Motion( 
	            proxy, { 
	                points: { 
	                    to: Dom.getXY(srcEl)
	                }
	            }, 
	            0.2, 
	            YAHOO.util.Easing.easeOut 
	        )
	        var proxyid = proxy.id;
	        var thisid = this.id;
	        // Hide and clear the proxy and show the source element when finished with the animation
	        a.onComplete.subscribe(function() {
	                Dom.setStyle(proxyid, "visibility", "hidden");
	                Dom.setStyle(thisid, "visibility", "");
	                document.getElementById(proxyid).innerHTML = "";
	            });
	        a.animate();
	        
	        //reset the color of the target-ul
	        kajona.dragndroplistDashboard.DDApp.resetUlBackground(kajona.dragndroplistDashboard.DDApp.getCurrentList(this.id));	
	        
			//save new pos to backend, if pos changed or ul changed
			var posAfterMove = kajona.dragndroplistDashboard.DDApp.getCurrentPos(this.id);
			var ulAfterMove = kajona.dragndroplistDashboard.DDApp.getCurrentList(this.id);
			if(posAfterMove != posBeforeMove || ulBeforeMove != ulAfterMove) {
	        	kajonaAdminAjax.setDashboardPos(this.id, kajona.dragndroplistDashboard.DDApp.getCurrentPos(this.id), kajona.dragndroplistDashboard.DDApp.getCurrentList(this.id));
			}
	    },
	
	    onDragDrop: function(e, id) {
	        if (DDM.interactionInfo.drop.length === 1) {
	            var pt = DDM.interactionInfo.point; 
	            var region = DDM.interactionInfo.sourceRegion; 
	            if (!region.intersect(pt)) {
	                var destEl = Dom.get(id);
	                var destDD = DDM.getDDById(id);
	                destEl.appendChild(this.getEl());
	                destDD.isEmpty = false;
	                DDM.refreshCache();
	            }
	        }
	    },
	
	    onDrag: function(e) {
	        var y = Event.getPageY(e);
	        if (y < this.lastY) {
	            this.goingUp = true;
	        } else if (y > this.lastY) {
	            this.goingUp = false;
	        }
	        this.lastY = y;
	    },
	
	    onDragOver: function(e, id) {
	        var srcEl = this.getEl();
	        var destEl = Dom.get(id);
	        // We are only concerned with list items, we ignore the dragover notifications for the list.
	        if (destEl.nodeName.toLowerCase() == "li") {
	            var orig_p = srcEl.parentNode;
	            var p = destEl.parentNode;
	            if (this.goingUp) {
	                p.insertBefore(srcEl, destEl); // insert above
	            } else {
	                p.insertBefore(srcEl, destEl.nextSibling); // insert below
	            }
	            DDM.refreshCache();
	        }
	        //highlight uls
	        if (destEl.nodeName.toLowerCase() == "ul") {
	        	Dom.setStyle(destEl, "background-color", "#efefef");
	        	Dom.setStyle(destEl, "padding-bottom", "50px");
	        }
	        
	    },
	    
	    onDragOut: function(e, id) {
	        var srcEl = this.getEl();
	        var destEl = Dom.get(id);
	        //highlight uls
	        if (destEl.nodeName.toLowerCase() == "ul") {
	        	kajona.dragndroplistDashboard.DDApp.resetUlBackground(destEl);	
	        }
	        
	    }
	    
	});

	//and init the app
	kajona.dragndroplistDashboard.DDApp.safeInit();
})();