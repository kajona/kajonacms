//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

define(['jquery', 'jstree', 'ajax', 'lang', 'cacheManager'], function ($, jstree, ajax, lang, cacheManager) {

    var kajonatree = {
        helper: {},
        contextmenu: {},
        conditionalselect: {}
    };

    /**
     * Object to initilaze a JsTree
     */
    kajonatree.jstree = function () {

        var treeContext = this;

        this.loadNodeDataUrl = null;
        this.rootNodeSystemid = null;
        this.treeConfig = null;//@see class \Kajona\System\System\SystemJSTreeConfig for structure
        this.treeId = null;
        this.treeviewExpanders = null;

        /**
         * Moves nodes below another node.
         * Triggers a relaod of the page after node was moved
         *
         * @param data
         * @returns {boolean}
         */
        function moveNode(data) {
            //node data
            var strNodeId = data.node.id,
                strNewParentId = data.parent,
                strOldParentId = data.old_parent,
                intNewPostiton = data.position,
                intOldPostiton = data.old_position;

            /* Get table row which should be moved*/
            var $objTableRowMoved = $('tr[data-systemid=' + strNodeId + ']').closest('tbody');

            //same parent
            if (strNewParentId == strOldParentId) {

                /* Move table row to according position*/
                if ($objTableRowMoved.length > 0) {
                    var arrElementsInTable = $objTableRowMoved.closest('table').find('tbody');

                    if (intOldPostiton > intNewPostiton) {
                        $(arrElementsInTable[intOldPostiton]).insertBefore($(arrElementsInTable[intNewPostiton]));
                    }
                    if (intOldPostiton < intNewPostiton) {
                        $(arrElementsInTable[intOldPostiton]).insertAfter($(arrElementsInTable[intNewPostiton]));
                    }
                }

                /* Call server*/
                ajax.genericAjaxCall("system", "setAbsolutePosition", strNodeId + "&listPos=" + (intNewPostiton + 1), function (data, status, jqXHR) {
                    ajax.regularCallback(data, status, jqXHR);
                });
            }
            //different parent
            else if (strNewParentId != strOldParentId) {

                /* hide table row*/
                if ($objTableRowMoved.length > 0) {
                    $objTableRowMoved.hide();
                }

                /* Call server*/
                ajax.genericAjaxCall("system", "setPrevid", strNodeId + "&prevId=" + strNewParentId, function (data, status, jqXHR) {

                    if(status == 'success') {
                        ajax.genericAjaxCall("system", "setAbsolutePosition", strNodeId + "&listPos=" + (intNewPostiton + 1), function (data, status, jqXHR) {
                            ajax.regularCallback(data, status, jqXHR);
                        });
                    }
                    else {
                        ajax.regularCallback(data, status, jqXHR);
                    }
                });
            }

            return true;
        }

        /**
         * Checks if a node can be dropped to a certain place in the tree
         *
         * @param node - the dragged node
         * @param node_parent
         * @param node_position
         * @param more
         * @returns {boolean}
         */
        function checkMoveNode(node, node_parent, node_position, more) {
            var targetNode = more.ref,
                strDragId = node.id,
                strTargetId = targetNode.id,
                strInsertPosition = more.pos; //"b"=>before, "a"=>after, "i"=inside


            //user can only move node if he has right on the dragged node and the parent node
            if(!node.data.rightedit && !node_parent.data.rightedit) {
                return false;
            }

            //dragged node already direct childnode of target?
            var arrTargetChildren = targetNode.children;
            if ($.inArray(strDragId, arrTargetChildren) > -1) {
                return false;
            }

            //dragged node is parent of target?
            var arrTargetParents = targetNode.parents;
            if ($.inArray(strDragId, arrTargetParents) > -1) {
                return false;//TODO maybe not needed, already check by jstree it self
            }

            //dragged node same as target node?
            if (strDragId == strTargetId) {
                return false;//TODO maybe not needed, already check by jstree it self
            }

            return true;
        }


        /**
         * Callback used for dragging elements from the list to the tree
         *
         * @param e
         * @returns {*}
         */
        this.listDnd = function (e) {
            var strSystemId = $(this).closest("tr").data("systemid");
            var strTitle = $(this).closest("tr").find(".title").text();

            //Check if there a jstree instance (there should only one)
            var jsTree = $.jstree.reference('#' + treeContext.treeId);

            //create basic node
            var objNode = {
                id: strSystemId,
                text: strTitle
            };

            //if a jstree instanse exists try to find a node for it
            if (jsTree != null) {
                var treeNode = jsTree.get_node(strSystemId);
                if (treeNode != false) {
                    objNode = treeNode;
                }
            }

            var objData = {
                'jstree': true,
                'obj': $(this),
                'nodes': [
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

            /* Create config object*/
            var jsTreeObj = {
                'core': {
                    /**
                     *
                     * @param operation operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
                     * @param node the selected node
                     * @param node_parent
                     * @param node_position
                     * @param more on dnd => more is the hovered node
                     * @returns {boolean}
                     */
                    'check_callback': function (operation, node, node_parent, node_position, more) {
                        // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
                        // in case of 'rename_node' node_position is filled with the new node name

                        var bitReturn = false;

                        if (operation === 'move_node') {
                            //check when dragging
                            bitReturn = true;
                            if (more.dnd) {
                                bitReturn = checkMoveNode(node, node_parent, node_position, more);
                            }
                        }

                        if (operation === 'create_node') {
                            bitReturn = true;//Check for assignment tree
                        }

                        return bitReturn;
                    },
                    'expand_selected_onload': true,//if left as true all parents of all selected nodes will be opened once the tree loads (so that all selected nodes are visible to the user)
                    'data': {
                        'url': function (node) {
                            return treeContext.loadNodeDataUrl;
                        },
                        'data': function (node, cb) {
                            var data = {};
                            if (node.id === "#") {
                                data.systemid = treeContext.rootNodeSystemid;
                                data.jstree_initialtoggling = treeContext.treeviewExpanders;
                            }
                            else {
                                data.systemid = node.id;

                                if(node.data) {
                                    data.jstree_loadallchildnodes = node.data.jstree_loadallchildnodes;
                                }
                            }

                            return data;
                        }
                    },
                    'themes': {
                        "url": false,
                        "icons": false
                    },
                    'animation': false
                },
                'dnd': {
                    'check_while_dragging': true
                },
                'types': {},
                'contextmenu': {},
                'conditionalselect': kajonatree.conditionalselect.handleConditionalSelect,

                'plugins': ['conditionalselect']
            };

            /* Extend Js Tree Object due to jsTreeConfig*/
            if (this.treeConfig.checkbox) {
                jsTreeObj.plugins.push('checkbox');
            }
            if (this.treeConfig.dnd) {
                jsTreeObj.plugins.push('dnd');
            }
            if (this.treeConfig.types) {
                jsTreeObj.plugins.push('types');
                jsTreeObj.types = this.treeConfig.types;
            }
            if (this.treeConfig.contextmenu) {
                jsTreeObj.plugins.push('contextmenu');
                jsTreeObj.contextmenu.items = this.treeConfig.contextmenu.items;
                jsTreeObj.contextmenu.show_at_node = false;
            }

            /* Create the tree */
            var $jsTree = $('#' + this.treeId).jstree(jsTreeObj);

            /*Register events*/
            $jsTree
                .on("show_contextmenu.jstree", function (objNode, x, y) {
                    //initialze properties when context menu is shown
                    lang.initializeProperties($('.jstree-contextmenu'));
                });

            $jsTree
                .on('move_node.jstree', function (e, data) {
                    moveNode(data);
                });

            $jsTree
                .on('ready.jstree', function (e, data) {

                    var elemId = "#" + treeContext.treeviewExpanders[treeContext.treeviewExpanders.length-1];
                    var $element = $(elemId);
                    kajonatree.helper.getTreeInstance().select_node($element);
                });

            //4. init jstree draggable for lists
            $('td.treedrag.jstree-listdraggable').on('mousedown', this.listDnd);
        };
    };


    /**
     * Get the current tree instance
     *
     * @returns {*}
     */
    kajonatree.helper.getTreeInstance = function () {
        var treeId = $('.treeDiv').first()[0].id;
        return $.jstree.reference('#' + treeId);

    };

    /**
     *  Creates the contextmenu
     *
     * @param o - the node
     * @param cb - callback function
     */
    kajonatree.contextmenu.createDefaultContextMenu = function (o, cb) {
        var objItems = {
            "expand_all": {
                "label": "<span data-lang-property=\"system:commons_tree_contextmenu_loadallsubnodes\"></span>",
                "action": kajonatree.contextmenu.openAllNodes,
                "icon": "fa fa-sitemap"
            }
        };

        return objItems;
    };


    /**
     *  Each time a node should be select, this method is being fired via the conditionalselect plugin.
     *  Handles conitional select events.
     *
     * @param objNode - the node to be selected
     * @param event - the event being fired
     *
     */
    kajonatree.conditionalselect.handleConditionalSelect = function (objNode, event) {

        //hanlde on click events
        if (event.type == "click") {

            //if node contains a_attr with href -> relaod page
            if (objNode.a_attr) {
                if (objNode.a_attr.href) {
                    document.location.href = objNode.a_attr.href;//Document reload
                }
            }
        }

        return true;
    };

    /**
     * Function to open all nodes via the contextmenu
     *
     * @param data
     */
    kajonatree.contextmenu.openAllNodes = function (data) {
        var objTreeInstance = $.jstree.reference(data.reference),
            objNode = objTreeInstance.get_node(data.reference);

        /*Check if node was already loaded (also check if parent node was loaded)*/
        var arrNodesToCheck = objNode.parents;
        arrNodesToCheck.unshift(objNode.id);
        var bitAlreadyLoaded = false;

        for(var i = 0; i < arrNodesToCheck.length; i++) {
            var objCurrNode = objTreeInstance.get_node(arrNodesToCheck[i]);

            if(!objCurrNode.data) {
                objCurrNode.data = {};
            }

            if(objCurrNode.data.jstree_loadallchildnodes) {
                bitAlreadyLoaded = true;
                break;
            }
        }

        //only load if have not been loaded yet, else just open all nodes
        if(!bitAlreadyLoaded) {
            objNode.data.jstree_loadallchildnodes = true;
            objTreeInstance.load_node(objNode, function(node){
                objTreeInstance.open_all(node);
            });
        }
        else {
            //all child nodes are already loaded
            objTreeInstance.open_all(objNode);
        }
    };


    kajonatree.toggleInitial = function(strTreeId) {
        var treeStates = cacheManager.get("treestate");
        if(treeStates != null && treeStates != '') {
            treeStates = JSON.parse(treeStates);

            if(treeStates[strTreeId] == 'false') {
                kajonatree.toggleTreeView(strTreeId);
            }
        }
    };

    kajonatree.toggleTreeView = function(strTreeId) {
        var $treeviewPane = $(".treeViewColumn[data-kajona-treeid="+strTreeId+"]");
        var $contentPane = $(".treeViewContent[data-kajona-treeid="+strTreeId+"]");
        var treeStates = cacheManager.get("treestate");
        if(treeStates == null || treeStates == '') {
            treeStates = {};
        }
        else {
            treeStates = JSON.parse(treeStates);
        }
        if(!treeStates[strTreeId])
            treeStates[strTreeId] = 'true';

        if($treeviewPane.hasClass("col-md-4")) {
            $treeviewPane.addClass("hidden").removeClass("col-md-4");
            $contentPane.addClass("col-md-12").removeClass("col-md-8");
            treeStates[strTreeId] = 'false';

        } else {
            $treeviewPane.addClass("col-md-4").removeClass("hidden");
            $contentPane.addClass("col-md-8").removeClass("col-md-12");
            treeStates[strTreeId] = 'true';
        }

        cacheManager.set("treestate", JSON.stringify(treeStates));
        return false;
    };

    return kajonatree;

});
