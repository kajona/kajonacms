//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2011 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

/**
 *
 * This file includes a script to be used to make lists drag n dropable.
 * The array arrayListIds is parsed, all tr-elements are added
 * See the YUI dragdrop-list-example for further infos
 */

if(arrayTableIds == null) {
	var arrayTableIds = [];
}

//create namespaces
KAJONA.admin.dragndroplist = {};

(function() {
	var Dom = YAHOO.util.Dom;
	var Event = YAHOO.util.Event;
	var DDM = YAHOO.util.DragDropMgr;

	var posBeforeMove = -1;

    var sourceTableId = -1;

	//Basic functions
	KAJONA.admin.dragndroplist.DDApp = {
	
		safeInit : function() {
			if(typeof YAHOO == "undefined") {
                window.setTimeout(KAJONA.admin.dragndroplist.DDApp.safeInit(), 1000);
                return;
            }
            
            KAJONA.admin.dragndroplist.DDApp.init();
		},
	
    	init: function() {
		   //iterate over all lists available
		   for(var l=0; l<arrayTableIds.length; l++) {
		   	   var listId = arrayTableIds[l];
	           //basic dnd list
	           new YAHOO.util.DDTarget(listId);
			   //load items in list
			   var arrayListItems = YAHOO.util.Dom.getChildren(listId);
			   if(arrayListItems[0].nodeName.toLowerCase() == "tbody") {
		   	   	  arrayListItems = arrayListItems[0].childNodes;
			   }

			   for(var i=0;i<arrayListItems.length;i=i+1) {
			       if(arrayListItems[i].id != null && arrayListItems[i].id != "") {
			   		  Dom.setStyle(arrayListItems[i], "cursor", "move");
		 			  new KAJONA.admin.dragndroplist.DDList(arrayListItems[i].id);
			       }
		   	   }
		   }
    	},
	    getCurrentPos : function(idOfRow) {
		   for(l=0; l<arrayTableIds.length; l++) {
		   	   listId = arrayTableIds[l];
		       var arrayListItems = YAHOO.util.Dom.getChildren(listId);
			   if(arrayListItems[0].nodeName.toLowerCase() == "tbody") {
		   	   	  arrayListItems = arrayListItems[0].childNodes;
			   }

		   	   var intCounter = 1;
			   for(i=0;i<arrayListItems.length;i=i+1) {
			       if(arrayListItems[i].id != null && arrayListItems[i].id != "") {
			 	       if(arrayListItems[i].id == idOfRow) {
			 		      return intCounter;
			 		   }
			 		   intCounter++;
			       }
			   }
		   }
	    },

		getCurrentList : function(idOfRow) {
		   for(l=0; l<arrayTableIds.length; l++) {
		   	   listId = arrayTableIds[l];
		       var arrayListItems = YAHOO.util.Dom.getChildren(listId);
			   if(arrayListItems[0].nodeName.toLowerCase() == "tbody") {
		   	   	  arrayListItems = arrayListItems[0].childNodes;
			   }
			   for(i=0;i<arrayListItems.length;i=i+1) {
			 		if(arrayListItems[i].id == idOfRow) {
			 			return listId;
			 		}
			   }
		   }
	    }
	};

	KAJONA.admin.dragndroplist.DDList = function(id, sGroup, config) {
	    KAJONA.admin.dragndroplist.DDList.superclass.constructor.call(this, id, sGroup, config);
	    var el = this.getDragEl();
	    Dom.setStyle(el, "opacity", 0.67); // The proxy is slightly transparent
	    this.goingUp = false;
	    this.lastY = 0;
	};

	YAHOO.extend(KAJONA.admin.dragndroplist.DDList, YAHOO.util.DDProxy, {

	    startDrag: function(x, y) {
	        // make the proxy look like the source element
	        var dragEl = this.getDragEl();
	        var clickEl = this.getEl();
			//save the start-pos
			posBeforeMove = KAJONA.admin.dragndroplist.DDApp.getCurrentPos(clickEl.id);
			
	        Dom.setStyle(clickEl, "visibility", "hidden");
	        dragEl.innerHTML = clickEl.innerHTML;
			//jump the inner element up until an tr-element
			while(!clickEl.nodeName.toLowerCase() == "tr") {
				clickEl = clickEl.parentNode;
			}

			//get table-node
			var parentNode = clickEl.parentNode;
			if(parentNode.nodeName.toLowerCase() == "tbody") {
				parentNode = parentNode.parentNode;
			}

            sourceTableId = parentNode.id;

			//make a regular table out of it and make it look like the original
			dragEl.innerHTML = "<table "+
						         " class=\""+parentNode.className+"\""+
								 " width=\""+(parentNode.getAttribute('width') != null ? parentNode.getAttribute('width') : "100%" )+"\""+
								 " border=\""+(parentNode.getAttribute('border') != null ? parentNode.getAttribute('border') : "0" )+"\""+
								 " cellpadding=\"0\" cellspacing=\"0\" "+
								 " ><tr"+
						         " class=\""+clickEl.className+"\""+
								 ">" +clickEl.innerHTML+ "</tr></table>";
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
	        //save new pos to backend?
			var posAfterMove = KAJONA.admin.dragndroplist.DDApp.getCurrentPos(this.id);
			if(posAfterMove != posBeforeMove) {
	        	KAJONA.admin.ajax.setAbsolutePosition(this.id, posAfterMove, KAJONA.admin.dragndroplist.DDApp.getCurrentList(this.id));
			}
	    },

	    onDragDrop: function(e, id) {
	        //is target element an allowed one?
	        if (DDM.interactionInfo.drop.length === 1) {
	            var pt = DDM.interactionInfo.point;
	            var region = DDM.interactionInfo.sourceRegion;
	            if (!region.intersect(pt)) {
	                var destEl = Dom.get(id);
	                var destDD = DDM.getDDById(id);
	                if(destEl != null && destEl.nodeName.toLowerCase() == "table") {
	                   return;
	                }
	                   
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
	        // We are only concerned with list items, we ignore the dragover notifications for the table.
	        if (destEl.nodeName.toLowerCase() == "tr") {

                if(bitOnlySameTable != undefined && bitOnlySameTable == true) {
                    //check, if it's the same table selected as a target
                    //get table-node
                    var parentNode = destEl.parentNode;
                    if(parentNode.nodeName.toLowerCase() == "tbody") {
                        parentNode = parentNode.parentNode;
                    }
                    var targetTableId = parentNode.id;
                    if(targetTableId != sourceTableId)
                        return;
                }

	            var p = destEl.parentNode;
	            if (this.goingUp) {
	                p.insertBefore(srcEl, destEl); // insert above
	            } else {
	                p.insertBefore(srcEl, destEl.nextSibling); // insert below
	            }
	            DDM.refreshCache();
	        }
	    }
	});

	//and init the app
	KAJONA.admin.dragndroplist.DDApp.safeInit();
})();