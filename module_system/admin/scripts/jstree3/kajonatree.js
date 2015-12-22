//   (c) 2007-2015 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (typeof KAJONA === "undefined") {
    alert('load kajona.js before!');
}

KAJONA.kajonatree = {

};

/**
 * Object to initilaze a JsTree
 *
 */
KAJONA.kajonatree.jstree = function () {

    var treeContext = this;

    this.loadNodeDataUrl = null;
    this.rootNodeSystemid = null;
    this.treeConfig = null;//@see class \Kajona\System\System\SystemJSTreeConfig for structure
    this.treeId = null;
    this.treeviewExpanders  = null;

    /**
     * Moves nodes below another node
     * @param node
     * @param node_parent
     * @param node_position
     * @param more
     * @returns {boolean}
     */
    this.moveNode = function (node, node_parent, node_position, more) {
        //node moved
        var strNodeId = node.id,
         strNewParentId = node_parent.id;

        //save new parent to backend
        KAJONA.admin.ajax.genericAjaxCall("system", "setPrevid", strNodeId+"&prevId="+strNewParentId, function() {
            location.reload();
        });
        return true;
    };

    /**
     * Checks if a node cann be dropped to a certain place in the tree
     *
     * @param node
     * @param node_parent
     * @param node_position
     * @param more
     * @returns {boolean}
     */
    this.checkMoveNode = function (node, node_parent, node_position, more) {
        var targetNode = more.ref,
         strDragId = node.id,
         strTargetId = targetNode.id,
         strInsertPosition = more.pos; //"b"=>before, "a"=>after, "i"=inside

        //only insert are allowed, no ordering
        if (strInsertPosition !== "i") {
            return false;
        }

        //dragged node already direct childnode of target?
        var arrTargetChildren = targetNode.children;
        if($.inArray(strDragId, arrTargetChildren) > -1){
            return false;
        }

        //dragged node is parent of target?
        var arrTargetParents = targetNode.parents;
        if($.inArray(strDragId, arrTargetParents) > -1){
            return false;//TODO maybe not needed, already check by jstree it self
        }

        //drage node same as target node?
        if(strDragId == strTargetId) {
            return false;//TODO maybe not needed, already check by jstree it self
        }

        return true;
    };


    /**
     * Callback used for dragging elements from the list to the tree
     *
     * @param e
     * @returns {*}
     */
    this.listDnd = function(e) {
        var strSystemId = $(this).closest("tr").data("systemid");
        var strTitle = $(this).closest("tr").find(".title").text();

        //Check if there a jstree instance (there should only one)
        var jsTree = $.jstree.reference('#'+treeContext.treeId);

        //create basic node
        var objNode =   {
            id : strSystemId,
            text: strTitle
        };

        //if a jstree instanse exists try to find a node for it
        if(jsTree != null) {
            var treeNode = jsTree.get_node(strSystemId);
            if(treeNode != false) {
                objNode = treeNode;
            }
        }

        var objData = {
            'jstree' : true,
            'obj' : $(this),
            'nodes' : [
                objNode
            ]
        };
        var event = e;
        var strHtml = '<div id="jstree-dnd" class="jstree-default"><i class="jstree-icon jstree-er"></i>' + strTitle + '</div>';//drag container
        return $.vakata.dnd.start(event, objData, strHtml);
    };


    /**
     * Initializes the jstree
     */
    this.initTree = function () {

        //1. Create config object
        var jsTreeObj = {
            'core' : {
                /**
                 *
                 * @param operation operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
                 * @param node the selected node
                 * @param node_parent
                 * @param node_position
                 * @param more on dnd => more is the hovered node
                 * @returns {boolean}
                 */
                'check_callback' : function (operation, node, node_parent, node_position, more) {
                    // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
                    // in case of 'rename_node' node_position is filled with the new node name

                    if(operation === 'move_node') {
                        //check when dragging
                        if(more.dnd) {
                            return treeContext.checkMoveNode(node, node_parent, node_position, more);
                        }
                        else {
                            return treeContext.moveNode(node, node_parent, node_position, more);
                        }
                    }

                    if(operation === 'create_node') {
                        return true;//Check for assignment tree
                    }

                    return false;
                },
                'expand_selected_onload': true,
                'data': {
                    'url': function (node) {
                        return treeContext.loadNodeDataUrl;
                    },
                    'data': function (node) {
                        if (node.id === "#") {
                            node.systemid = treeContext.rootNodeSystemid;
                            node.jstree_initialtoggling = treeContext.treeviewExpanders;
                        }
                        else {
                            node.systemid = node.id;
                        }
                        return node;
                    }
                },
                'themes': {
                    "url": false,
                    "icons": false
                }
            },
            'dnd': {
                'check_while_dragging' : true
            },
            'types': {
            },
            'plugins': arrPlugins
        };


        //2. Extend Js Tree Object due to jsTreeConfig
        var arrPlugins = [];

        if(this.treeConfig.checkbox) {
            arrPlugins.push('checkbox');
        }
        if(this.treeConfig.dnd) {
            arrPlugins.push('dnd');
        }
        if(this.treeConfig.types) {
            arrPlugins.push('types');
            jsTreeObj.types = this.treeConfig.types;
        }

        jsTreeObj.plugins = arrPlugins;


        //3. Create the tree
        $('#'+this.treeId).jstree(jsTreeObj)
            .bind("select_node.jstree", function (event, data) {
                if(data.node.a_attr) {
                    if(data.node.a_attr.href) {
                        document.location.href = data.node.a_attr.href;//Document reload
                    }
                }
            });

        //4. init jstree draggable for lists
        $('td.treedrag.jstree-listdraggable').on('mousedown', this.listDnd);
    };
};