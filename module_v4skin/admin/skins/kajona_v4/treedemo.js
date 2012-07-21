$(function () {

$("#treeDemo")
	.jstree({
		// List of active plugins
		"plugins" : [
			"themes","json_data","ui","crrm","cookies","dnd","types","hotkeys"
		],

		// I usually configure the plugin that handles the data first
		// This example uses JSON as it is most common
		"json_data" : {
			// This tree is ajax enabled - as this is most common, and maybe a bit more complex
			// All the options are almost the same as jQuery's AJAX (read the docs)
			"ajax" : {
				// the URL to fetch the data
				"url" : "tree.json",
				// the `data` function is executed in the instance's scope
				// the parameter is the node being loaded
				// (may be -1, 0, or undefined when loading the root nodes)
				"data" : function (n) {
					// the result is fed to the AJAX request `data` option
					return {
						"operation" : "get_children",
						"id" : n.attr ? n.attr("id").replace("node_","") : 1
					};
				}
			}
		},

		// UI & core - the nodes to initially select and open will be overwritten by the cookie plugin

		// the UI plugin - it handles selecting/deselecting/hovering nodes
		"ui" : {
			// this makes the node with ID node_4 selected onload
			"initially_select" : [ "node_4" ]
		},
		// the core plugin - not many options here
		"core" : {
			// just open those two nodes up
			// as this is an AJAX enabled tree, both will be downloaded from the server
			"initially_open" : [ "node_2" , "node_3" ]
		}
	})
    .delegate("a", "dblclick", function (e) {
        var id = $(this).parent().attr("id").split("_")[1];
        console.log('TREE DROUBLE', id);
        jQuery.jstree._reference("#treeDemo").rename(null);
        //data.inst.rename(NODE);
    })
    .bind("remove.jstree", function (e, data) {
		data.rslt.obj.each(function () {
			$.ajax({
				async : false,
				type: 'POST',
				url: "tree.json",
				data : {
					"operation" : "remove_node",
					"id" : this.id.replace("node_","")
				},
				success : function (r) {
					if(!r.status) {
						data.inst.refresh();
					}
				}
			});
		});
	})
	.bind("rename.jstree", function (e, data) {
		$.post(
			"tree.json",
			{
				"operation" : "rename_node",
				"id" : data.rslt.obj.attr("id").replace("node_",""),
				"title" : data.rslt.new_name
			},
			function (r) {
				if(!r.status) {
					$.jstree.rollback(data.rlbk);
				}
			}
		);
	})
	.bind("move_node.jstree", function (e, data) {
		data.rslt.o.each(function (i) {
			$.ajax({
				async : false,
				type: 'POST',
				url: "tree.json",
				data : {
					"operation" : "move_node",
					"id" : $(this).attr("id").replace("node_",""),
					"ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""),
					"position" : data.rslt.cp + i,
					"title" : data.rslt.name,
					"copy" : data.rslt.cy ? 1 : 0
				},
				success : function (r) {
					if(!r.status) {
						$.jstree.rollback(data.rlbk);
					}
					else {
						$(data.rslt.oc).attr("id", "node_" + r.id);
						if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
							data.inst.refresh(data.inst._get_parent(data.rslt.oc));
						}
					}
					//$("#analyze").click();
				}
			});
		});
	});

});