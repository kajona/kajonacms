<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\Link;

/**
 * FlowGraphWriter
 *
 * @author christoph.kappestein@artemeon.de
 */
class FlowGraphWriter
{
    /**
     * Generates a mermaid graph definition of the flow object
     *
     * @param FlowConfig $objFlow
     * @param mixed $objHighlite
     * @return string
     */
    public static function write(FlowConfig $objFlow, $objHighlite = null)
    {
        return self::writeMermaid($objFlow, $objHighlite);
    }

    private static function writeCytoscape(FlowConfig $objFlow)
    {
        $arrStatus = $objFlow->getArrStatus();

        $arrNodes = [];
        foreach ($arrStatus as $objStatus) {
            $arrNodes[] = [
                'data' => [
                    'id' => $objStatus->getSystemid(),
                    'name' => $objStatus->getStrName(),
                ]
            ];
        }

        $arrTrans = [];

        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            $arrTransitions = $objStatus->getArrTransitions();
            foreach ($arrTransitions as $objTransition) {
                /** @var $objTransition FlowTransition */
                $objParentStatus = $objTransition->getParentStatus();
                $objTargetStatus = $objTransition->getTargetStatus();

                $arrTrans[] = [
                    'data' => [
                        'id' => $objTransition->getSystemid(),
                        'source' => $objParentStatus->getSystemid(),
                        'target' => $objTargetStatus->getSystemid(),
                    ]
                ];
            }
        }

        $strNodes = json_encode($arrNodes);
        $strTransitions = json_encode($arrTrans);

        return <<<HTML
<div id='flow-graph' class='mermaid' style='position:absolute;width:90%;height:800px;border:1px solid #999;'></div>
<script type="text/javascript">
    mxBasePath = '/agp-core/core/module_flow/scripts/mxgraph/src';
    require(['cytoscape', 'cytoscape-dagre', 'dagre', 'cytoscape-cxtmenu'], function(cytoscape, cd, dagre, cm){
        
        cd(cytoscape, dagre);
        //cm(cytoscape);

        var cy = cytoscape({
          container: document.getElementById('flow-graph'),

          boxSelectionEnabled: false,
          autounselectify: true,

          style: cytoscape.stylesheet()
            .selector('node')
              .css({
                'font-size': '6',
                'label': 'data(name)',
                'text-valign': 'center',
                'shape': 'roundrectangle',
                'width': '40',
                'height': '20',
                'border-width' : '1',
                'border-color' : '#222'
              })
            .selector('edge')
              .css({
                'target-arrow-shape': 'triangle',
                'width': 4,
                'line-color': '#ddd',
                'target-arrow-color': '#ddd',
                'curve-style': 'bezier'
              }),

          elements: {
            nodes: {$strNodes}, 
            edges: {$strTransitions}
          },
					layout: {
						name: 'dagre'
					}
        });
        
        var bfs = cy.elements().bfs('#a', function(){}, true);
        
        
        bfs.path[i].addClass('highlighted');
        i++;
    });

</script>
HTML;
    }


    private static function writeMxgraph(FlowConfig $objFlow)
    {
        $arrStatus = $objFlow->getArrStatus();

        $arrNodes = [];
        foreach ($arrStatus as $objStatus) {
            $arrNodes[] = 'var status' . $objStatus->getSystemid() . ' = graph.insertVertex(parent, null, ' . json_encode($objStatus->getStrName()) . ', 0, 0, 120, 50);';
        }

        $arrTrans = [];

        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            $arrTransitions = $objStatus->getArrTransitions();
            foreach ($arrTransitions as $objTransition) {
                /** @var $objTransition FlowTransition */
                $objParentStatus = $objTransition->getParentStatus();
                $objTargetStatus = $objTransition->getTargetStatus();

                $arrTrans[] = 'var transition' . $objTransition->getSystemid() . ' = graph.insertEdge(parent, null, "", status' . $objParentStatus->getSystemid() . ', status' . $objTargetStatus->getSystemid() . ');';
            }
        }

        $strNodes = implode("\n", $arrNodes);
        $strTransitions = implode("\n", $arrTrans);

        return <<<HTML
<div id='flow-graph' class='mermaid' style='position:relative;overflow:hidden;width:100%;height:800px;cursor:default;border:1px solid #999;padding:8px;'></div>
<script type="text/javascript">
    mxBasePath = '/agp-core/core/module_flow/scripts/mxgraph/src';
    require(['mxgraph', 'loader'], function(mxgraph, loader){
        loader.loadFile(["/core/module_flow/scripts/mxgraph/src/css/common.css"], function(){
            var container = document.getElementById('flow-graph');
            // Checks if the browser is supported
			if (!mxClient.isBrowserSupported()) {
				// Displays an error message if the browser is not supported.
				mxUtils.error('Browser is not supported!', 200, false);
			} else {
			    
				// Disables the built-in context menu
				mxEvent.disableContextMenu(container);
				
				// Creates the graph inside the given container
				var graph = new mxGraph(container);

				// Enables rubberband selection
				new mxRubberband(graph);
				
				// Changes the default vertex style in-place
				var style = graph.getStylesheet().getDefaultVertexStyle();
				style[mxConstants.STYLE_PERIMETER] = mxPerimeter.RectanglePerimeter;
				style[mxConstants.STYLE_GRADIENTCOLOR] = 'white';
				style[mxConstants.STYLE_PERIMETER_SPACING] = 6;
				style[mxConstants.STYLE_ROUNDED] = false;
				style[mxConstants.STYLE_SHADOW] = false;
				style[mxConstants.STYLE_FONTSIZE] = 20;
				
				style = graph.getStylesheet().getDefaultEdgeStyle();

				// Creates a layout algorithm to be used
				// with the graph
				var layout = new mxCompactTreeLayout(graph, false);
				layout.nodeDistance = 80;

				// Gets the default parent for inserting new cells. This
				// is normally the first child of the root (ie. layer 0).
				var parent = graph.getDefaultParent();
								
				// Adds cells to the model in a single step
				graph.getModel().beginUpdate();
				try {
					{$strNodes}
					{$strTransitions}
					
					layout.execute(parent);
				} finally {
					// Updates the display
					graph.getModel().endUpdate();
				}
			}
        });
    });
</script>
HTML;
    }

    private static function writeMermaid(FlowConfig $objFlow, $objHighlite = null)
    {
        $arrStatus = $objFlow->getArrStatus();
        $arrList = array("graph TD;");

        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            $arrTransitions = $objStatus->getArrTransitions();
            foreach ($arrTransitions as $objTransition) {
                /** @var $objTransition FlowTransition */
                $objTargetStatus = $objTransition->getTargetStatus();
                if ($objTargetStatus instanceof FlowStatus) {
                    $arrList[] = $objStatus->getStrSystemid() . "[" . $objStatus->getStrName() . "]-- <span data-" . $objTransition->getSystemid() . ">______</span> -->" . $objTargetStatus->getSystemid() . "[" . $objTargetStatus->getStrName() . "];";
                }
            }
        }

        if ($objHighlite instanceof FlowStatus) {
            $arrList[] = "style {$objHighlite->getSystemid()} fill:#ccc,stroke:#333,stroke-width:4px;";
        } elseif ($objHighlite instanceof FlowTransition) {
            $arrList[] = "style {$objHighlite->getParentStatus()->getSystemid()} fill:#ccc,stroke:#333,stroke-width:4px;";
        }

        $strGraph = implode("\n", $arrList);

        $strTmpSystemId = generateSystemid();
        $strLinkTransition = Link::getLinkAdminHref("flow", "listTransition", "&systemid=" . $strTmpSystemId);
        $strLinkTransitionAction = Link::getLinkAdminHref("flow", "listTransitionAction", "&systemid=" . $strTmpSystemId);
        $strLinkTransitionCondition = Link::getLinkAdminHref("flow", "listTransitionCondition", "&systemid=" . $strTmpSystemId);

        return <<<HTML
<div id='flow-graph' class='mermaid' style='color:#fff;'>{$strGraph}</div>
<script type="text/javascript">
    var callback = function(statusId) {
        location.href = "{$strLinkTransition}".replace('{$strTmpSystemId}', statusId);
    };

    require(['mermaid', 'loader', 'jquery'], function(mermaid, loader, $){
        loader.loadFile(["/core/module_flow/scripts/mermaid/mermaid.forest.css"], function(){
            mermaid.init(undefined, $("#flow-graph"));

            $('div > span.edgeLabel > span').each(function(){
                var data = $(this).data();
                var transitionId;
                for (var key in data) {
                    transitionId = key;
                }

                var actionLink = "{$strLinkTransitionAction}".replace('{$strTmpSystemId}', transitionId);
                var conditionLink = "{$strLinkTransitionCondition}".replace('{$strTmpSystemId}', transitionId);

                $(this).html('<a href="' + actionLink + '"><i class="kj-icon fa fa-play-circle-o"></i></a> <a href="' + conditionLink + '"><i class="kj-icon fa fa-table"></i></a');
            });

            $('.node').on('click', function(){
                var statusId = $(this).attr('id');
                location.href = "{$strLinkTransition}".replace('{$strTmpSystemId}', statusId);
            });
            
            $('.node div').css('cursor', 'pointer');
        });
    });
</script>
HTML;
    }
}
