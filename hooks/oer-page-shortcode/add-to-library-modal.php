<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

global $oerPageDataById;
$resource = null;
$resourceid = 0;
if ($oerPageDataById) {
    $resource = $oerPageDataById;
    $resourceid = $resource['resourceid'];
} else {
    global $wpdb;
    if (empty($_REQUEST['pageurl']))
    $_REQUEST['pageurl'] = '';
    if (empty($_REQUEST['rid']))
        $_REQUEST['rid'] = '';
    $pageurl = str_replace("/", "", $_REQUEST['pageurl']);
    $rid = str_replace("/", "", $_REQUEST['rid']);
    $q_resource = "";
    if (strlen($pageurl) > 0) {
        $q_resource = "SELECT * FROM resources WHERE pageurl = '" . $pageurl . "'";
        $resource = $wpdb->get_row($q_resource);
        $resourceid = $resource->resourceid;
    } elseif (strlen($rid) > 0) {
        $resourceid = $rid;
    }
}

$pages_to_load_script = array("oer");
$pagename = get_query_var('pagename');
if (isset($pagename) && $pagename != null && in_array($pagename, $pages_to_load_script)) {
    wp_enqueue_script('angular', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js', array('jquery'), false, true); // Not using imagesLoaded? :( Okay... then this.
    wp_enqueue_style('nprog-css', plugins_url('/core/oer/assets/css/nprogress.css', dirname(__FILE__)), null, false, 'all'); // Add the styles first, in the <head> (last parameter false, true = bottom of page!)
    wp_enqueue_script('nprog-js', plugins_url('/core/oer/assets/js/nprogress.js', dirname(__FILE__)), array('angular'), false, true); // Not using imagesLoaded? :( Okay... then this.    
}

//wp_enqueue_script('ng-sortabl', "//rawgithub.com/angular-ui/ui-sortable/master/src/sortable.js", array('angular'), false, true);
wp_enqueue_script('ng-ctrlr-modal', plugins_url('/core/oer/assets/custom/js/app/controllers.js', dirname(__FILE__)), array('angular'), false, true);

wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js' , array('jquery'), false, true);

wp_enqueue_style('ui-fancytree', plugins_url('/core/oer/assets/js/fancytree/src/skin-win7/ui.fancytree.css', dirname(__FILE__)), array(), '1.0.0', 'all');
wp_enqueue_script('fancytree', plugins_url('/core/oer/assets/js/fancytree/src/jquery.fancytree.js', dirname(__FILE__)), array('jquery'), false, true);
wp_enqueue_script('fancytree-columnview', plugins_url('/core/oer/assets/js/fancytree/src/jquery.fancytree.columnview.js', dirname(__FILE__)), array('jquery'), false, true);
wp_enqueue_script('fancytree-dnd', plugins_url('/core/oer/assets/js/fancytree/src/jquery.fancytree.dnd.js', dirname(__FILE__)), array('jquery'), false, true);
wp_enqueue_script('fancytree-table', plugins_url('/core/oer/assets/js/fancytree/src/jquery.fancytree.table.js', dirname(__FILE__)), array('jquery'), false, true);
wp_enqueue_script('fancytree-glyph', plugins_url('/core/oer/assets/js/fancytree/src/jquery.fancytree.glyph.js', dirname(__FILE__)), array('jquery'), false, true);
wp_enqueue_style('prettify-css', plugins_url('/core/oer/assets/js/fancytree/lib/prettify.css', dirname(__FILE__)), array(), '1.0.0', 'all');
wp_enqueue_script('prettify-js', plugins_url('/core/oer/assets/js/fancytree/lib/prettify.js', dirname(__FILE__)), array('jquery'), false, true);
?>

<script type="text/javascript">

    var sort_by = "most_recent";
    var selected_group = 0;
    var new_node = null;
    var last_selected_collection = 0;
    var last_selected_group = 0;
    var new_node_for_no_record = null;

    renderMyLibraryTree();

    function renderMyLibraryTree()
    {
        window.selected_group = 0;
        window.new_node = null;
        window.last_selected_collection = 0;
        window.last_selected_group = 0;
        window.new_node_for_no_record = null;
        
        jQuery(function () {
            var count = 1;
            // Attach the fancytree widget to an existing <div id="tree"> element
            // and pass the tree options as an argument to the fancytree() function:
            jQuery("#tree").fancytree({
                extensions: ["dnd"],
                clickFolderMode: 3,
                autoScroll: false,
                generateIds: true,
                //			checkbox: true,
                //			debugLevel: 1,
                source: [{"key": 1, "title": "<?php echo __('My Collections','curriki'); ?>", "folder": true, "lazy": true}, {"key": 2, "title": "<?php echo __('My Groups','curriki'); ?>", "folder": true, "lazy": true}],
                activate: function (event, data) {
                },
                lazyLoad: function (event, data) {

                    //data.result = {url: "<?php echo get_stylesheet_directory_uri(); ?>/js/fancytree/test/unit/ajax-sub2.json"}

                    var node = data.node;

                    node.data.hasOwnProperty("ExpandableNode")

                    if (node.title == "My Collections" || node.title == "My Groups")
                    {
                        data.result = {
                            method: "POST",
                            url: ajaxurl,
                            data: jQuery.param({'action': 'get_user_library_collection', 'sort_by': window.sort_by, 'libraryTopTreeSelectedValue': node.title, 'selected_group': window.selected_group}),
                            cache: false
                        };
                    } else if ((node.data.hasOwnProperty("Source") && node.data.Source === "My Collections") || (node.data.hasOwnProperty("ExpandableNode") === true && node.data.ExpandableNode.toString() === "1")) {
                        //==== THIS IS TO FETCH RESOURCES OF SELECTED COLLECTION
                        //data.result = {url: "<?php //echo get_stylesheet_directory_uri();  ?>/js/fancytree/test/unit/ajax-sub2.json"}
                        window.last_selected_collection = parseInt(node.key);
                        window.last_selected_group = 0;

                        jQuery("#go_to_collection_btn").text("<?php echo __("Go to Collection !", "curriki"); ?>");

                        data.result = {
                            method: 'POST',
                            url: ajaxurl,
                            data: jQuery.param({'action': 'get_user_library_collection_resources', 'rid': node.key, 'selected_group': window.selected_group}),
                            cache: false
                        };
                    } else if (node.data.hasOwnProperty("Source") && node.data.Source === "My Groups") {

                        //==== THIS IS TO FETCH RESOURCES OF SELECTED COLLECTION
                        window.last_selected_group = parseInt(node.key);
                        window.last_selected_collection = 0;

                        jQuery("#go_to_collection_btn").text("Go to Group !");

                        window.selected_group = node.key;
                        data.result = {
                            method: "POST",
                            url: ajaxurl,
                            data: jQuery.param({'action': 'get_user_library_collection', 'sort_by': window.sort_by, 'libraryTopTreeSelectedValue': 'My Groups', 'selected_group': window.selected_group}),
                            cache: false
                        };
                    }
                },
                dnd: {
                    preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
                    preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
                    autoExpandMS: 400,
                    draggable: {
                        //zIndex: 9999,
                        // appendTo: "body",                                            
                        //scroll: false,
                        revert: "invalid"
                    },
                    dragStart: function (node, data) {
                        if (data.originalEvent.shiftKey) {
                            console.log("dragStart with SHIFT");
                        }
                        // allow dragging `node`:
                        return true;
                    },
                    dragEnter: function (node, data) {
                        //console.log("data **** " , jQuery(data.draggable.element).attr("id") );
                        var returnVal = false;
                        if ((node.parent.data.hasOwnProperty("Source") && (node.parent.data.Source === "My Collections" || node.parent.data.Source === "My Groups")) || (node.data.hasOwnProperty("ExpandableNode") === true && node.data.ExpandableNode.toString() === "1") || (node.data.hasOwnProperty("ExtendedNodeType") === true && node.data.ExtendedNodeType === "resource") || (node.data.hasOwnProperty("ExtendedNode") === true && node.data.ExtendedNode === 1))
                        {
                            if (jQuery(data.draggable.element).attr("id") === "draggable-resource")
                            {
                                //returnVal = true;
                                returnVal = ['before', 'after'];
                            } else
                            {
                                returnVal = false;
                            }
                        }
                        else
                        {
                            returnVal = false;
                        }

                        return returnVal;
                        //return true;
                    },
                    dragDrop: function (node, data) {
                        var title = jQuery(data.draggable.element).text().toString().trim();
                        var child_to_remove = null;
                        var child_to_remove_is_last_child = false;
                        var no_record_obj = null;
                        var no_record_obj_parent = null;
                        var is_dropped_on_same_position = false;

                        //jQuery(node.parent.getChildren()).each(function(i,obj){
                        jQuery(node.parent.children).each(function (i, obj) {                            
                            if (obj.title.toString().trim() === title)
                            {
                                child_to_remove = obj;

                                if (i === (node.parent.getChildren().length - 1) && obj.key.toString().trim() === node.key.toString().trim())
                                {
                                    child_to_remove_is_last_child = true;
                                    //console.log("dropped on last same child");
                                } else if (i !== (node.parent.getChildren().length - 1) && obj.key.toString().trim() === node.key.toString().trim())
                                {
                                    is_dropped_on_same_position = true;
                                }
                            }

                            if (obj.data.hasOwnProperty("no_record") && obj.data.no_record === 1)
                            {
                                no_record_obj = obj;
                                no_record_obj_parent = obj.parent;
                            }

                        });

                        if (is_dropped_on_same_position || child_to_remove_is_last_child)
                        {
                            alert("You are trying to drop resource on same position!");
                            return false;
                        }
                        
                        if (child_to_remove && node.parent.getChildren().length > 1)
                        {
                            node.parent.removeChild(child_to_remove);
                        } else if (child_to_remove && node.parent !== null && node.parent.getChildren().length === 1) {
                            return false;
                        }

                        if (!data.otherNode) {
                            // It's a non-tree draggable
                            //var title = jQuery(data.draggable.element).text() + " (" + (count)++ + ")";


                            // creating node of dropped resource in tree

                            if (node.parent.getChildren().length === 1 && no_record_obj) {
                                //=== Case to remove "Now record found!" =======
                                var resource_id_val = <?php echo $resourceid; ?>;
                                node.parent.children[0]['title'] = title;
                                //node.parent.children[0]['key'] = jQuery("input[name='resourceid']").val().toString();
                                node.parent.children[0]['key'] = resource_id_val;
                                node.parent.render(true, true);

                                window.new_node_for_no_record = node.parent.children[0];
                            } else if (node.parent.getChildren().length > 0 && no_record_obj === null)
                            {

                                var resource_id_val = <?php echo $resourceid; ?>;
                                window.new_node = {'title': title, 'key': resource_id_val, 'resourceid': resource_id_val};

                                if (node.data.hasOwnProperty("ExtendedNode") && node.data.ExtendedNode === 1)
                                {
                                    window.new_node["ExtendedNode"] = 1;
                                }

                                //node.parent.addNode(window.new_node, data.hitMode);
                                node.addNode(window.new_node, data.hitMode);

                                //console.log("window.new_node = " , window.new_node);
                                //console.log("node.parent = " , node.parent);

                                node.parent.render(true, true);
                            } else {

                                //=== Case Add new node in all cases ============

                                var resource_id_val = <?php echo $resourceid; ?>;
                                if (node.data.hasOwnProperty("ExtendedNode") && node.data.ExtendedNode === 1)
                                {
                                    window.new_node["ExtendedNode"] = 1;
                                }
                                node.addNode(window.new_node, data.hitMode);

                            }

                            //node.addNode(window.new_node, data.hitMode);                                                                                                
                            //console.log( "toDict N === " , JSON.stringify( node.toDict() ) ) ;                                                
                            //console.log( "toDict P === " , JSON.stringify( node.parent.toDict(true,function(dict, node){}) ) ) ;                                                                                                

                            if (no_record_obj_parent)
                            {
                                node.parent = no_record_obj_parent;
                            }

                            var collection_resources = node.parent.toDict(true, function (dict, node) {
                            });
                            var hit_node = node.toDict();


                            if (node.parent.data.hasOwnProperty("Source") && (node.parent.data.Source === "My Collections") || node.data.hasOwnProperty("ExtendedNode") && node.data.ExtendedNode === 1)
                            {
                                var source_val = node.parent.data.hasOwnProperty("Source") ? node.parent.data.Source : "ExpandableNode";
                                jQuery.ajax({
                                    method: "POST",
                                    url: ajaxurl,
                                    data: jQuery.param({action: 'add_user_library_collection_resource', collection_resources: collection_resources, hit_node: hit_node, new_node: window.new_node, source: source_val})
                                }).done(function (data) {
                                    console.log("data 11= ", data);
                                    alert("<?php echo __("Resource Added!", "curriki"); ?>");
                                    post_resource_drop_process();
                                });

                            } else if (node.parent.data.hasOwnProperty("Source") && (node.parent.data.Source === "My Groups"))
                            {
                                var n_rcd = null;
                                if (window.new_node_for_no_record)
                                {
                                    n_rcd = {key: window.new_node_for_no_record["key"], title: window.new_node_for_no_record["title"], resourceid: window.new_node_for_no_record["key"]};
                                } else {
                                    n_rcd = window.new_node;
                                }

                                jQuery.ajax({
                                    method: "POST",
                                    url: ajaxurl,
                                    data: jQuery.param({action: 'add_user_library_collection_resource', collection_resources: collection_resources, hit_node: hit_node, new_node: n_rcd, source: node.parent.data.Source})
                                }).done(function (data) {
                                    console.log("data 22= ", data);
                                    alert("Resource Added!");
                                    post_resource_drop_process();
                                });
                            }
                            //return;
                        }


                        //data.otherNode.moveTo(node, data.hitMode);


                        //jQuery(data.draggable.element).remove();

                        //console.log("Childrens = " , node.parent.getChildren()); 
                        //}else{                                            

                        //console.log("DE ***  " , data.draggable );
                        //data.draggable.element.draggable('option','revert',true);

                        //jQuery(data.draggable.element).draggable( "destroy" );
                        //jQuery(data.draggable.element).draggable('option','revert',true);
                        //jQuery("#draggable-resource").draggable("destroy");
                        //}
                    }
                },
                renderNode: function (event, data) {
                    var node = data.node;


                    //=== check rendering list is of groups =======                                

                    //console.log("******* node  = ", node);
                    if (node.title === "My Collections" || node.title === "My Groups")
                    {
                        var $span = jQuery(node.span);
                        $span.find("> span.fancytree-icon").css({
                            /*border: "1px solid green",*/
                            backgroundImage: "none",
                            /*backgroundPosition: "0 0"*/
                        }).addClass("fa fa-globe tree-root-node");
                    }


                    if (node.data.hasOwnProperty("Source") && node.data.Source === "My Groups")
                    {
                        var $span = jQuery(node.span);
                        $span.find("> span.fancytree-icon").css({
                            /*border: "1px solid green",*/
                            backgroundImage: "none",
                            /*backgroundPosition: "0 0"*/
                        }).addClass("fa fa-group");

                    }

                    if ((node.parent.data.hasOwnProperty("Source") && (node.parent.data.Source === "My Collections" || node.parent.data.Source === "My Groups")) || (node.data.hasOwnProperty("ExpandableNode") === true && node.data.ExpandableNode.toString() === "1") || (node.data.hasOwnProperty("ExtendedNode") === true && node.data.ExtendedNode === 1))
                    {
                        if (node.title === jQuery("#resource-title-modal").text().toString().trim())
                        {
                            var $span = jQuery(node.span);
                            $span.css("font-weight", "bold");
                            $span.css("color", "#53830c");
                        }
                    }
                },
                loadChildren: function (event, data) {
                    /*
                     if(data.node.hasOwnProperty("li") && data.node.li !== null)
                     {
                     console.log( ">>>>>>>> ",  jQuery(data.node.li).attr("id") );
                     var id_str = jQuery(data.node.li).attr("id");
                     var target = jQuery("#"+id_str);
                     //var target = data.node.li;                                                                                    
                     jQuery('.scrollbar-wrapper').animate({
                     scrollTop: target.offset().top
                     }, 1000);
                     
                     }*/
                }
            });
        });

    }

    function stripslashes(str) {
        str = str.replace(/\\'/g, '\'');
        str = str.replace(/\\"/g, '"');
        str = str.replace(/\\0/g, '\0');
        str = str.replace(/\\\\/g, '\\');
        return str;
    }

    function sortCollections(act)
    {
        window.sort_by = act;

        renderMyLibraryTree();
    }


    jQuery(document).ready(function () {
        jQuery("#done-btn").click(function () {
            if (jQuery(this).hasClass("button-save-disable"))
            {
                alert("You have not dropped any resource to Collection(s) or Group's Collection(s) yet! ");
            } else {
                jQuery("#add-to-lib-dialog").hide();
                // jQuery("#add-to-lib-alert-box").modal('show');
                jQuery("#add-to-lib-alert-box").show();
                //jQuery("#add-to-lib-alert-box").centerx();
            }
        });
    });

</script>


<script type="text/javascript">
    function load_add_to_lib_modal(rid)
    {
        rid = rid || "";
        rid_param = 0;
        if (rid.length > 0)
        {
            rid_arr = rid.split("-");
            rid_param = parseInt(rid_arr[1]);
        }


        var page_name = "<?php echo $pagename; ?>";

        if (page_name === "oer")
        {
            var rsource_name = "<?php echo $resource["title"]; ?>";
            jQuery("#resource-title-modal").text(rsource_name);
        } else if (page_name === "search" || page_name === "search-page" || page_name === "resources-curricula")
        {
            jQuery("#resource-title-modal").text(jQuery("#resource_title_mdl").val());
        }

        jQuery("#add-to-lib-dialog").show();
        //jQuery("#add-to-lib-dialog").centerLibModal();
        jQuery("#add-to-lib-dialog").css("z-index", "5");
        /*      
         if (angular.element(jQuery("#app-container")).scope() == undefined)
         {
         angular.bootstrap(jQuery("#app-container"), ['ngappmodal']);
         }
         
         var scope_app = angular.element(jQuery("#app-container")).scope();
         if (rid_param > 0)
         {
         scope_app.$apply(function () {
         scope_app.rid = rid_param;
         });
         }
         
         scope_app.libraryTopTreeSelectedValue = 'My Collections';
         scope_app.getCollections();
         */

        renderMyLibraryTree();
    }

    //*******************************
    var add_to_lib_pre_call_end = false;
    var add_to_lib_pre_call_end_sr_page = false;

    jQuery(document).ready(function () {

        jQuery("#continue_adding_btn").on("click", function () {
            // jQuery("#add-to-lib-alert-box").modal('hide');
            jQuery("#add-to-lib-alert-box").hide();

            jQuery("#done-btn").addClass("button-save-disable");

            jQuery("#add-to-lib-dialog").show();
            //jQuery("#add-to-lib-dialog").centerx();
            jQuery("#add-to-lib-dialog").css("z-index", "5");

            renderMyLibraryTree();
        });
        jQuery(".close-add-to-lib-alert-box").on("click", function () {
            jQuery("#add-to-lib-alert-box").hide();
        });
        jQuery("#go_to_lib_btn").on("click", function () {
            var lib_url = jQuery("#base_url").val() + "/my-library";
            window.location = lib_url;
        });
        jQuery(".add-to-lib-close-btn").on("click", function () {
            jQuery("#add-to-lib-dialog").hide();
            jQuery("#addtolibrary").show();
        });
        jQuery("#addtolibrary").on("click", function () {
            add_to_lib_pre_call_end_sr_page = true;
        });
        jQuery(document).on('click', '.add-to-library', function () {
            add_to_lib_pre_call_end = true;            
            var r_hd = jQuery(this).parents(".post").find(".post-content h4 a:eq(0)").text();
            var rid_mdl = jQuery(this).attr("rid-fld");
            jQuery("#resource_title_mdl").val(r_hd);
            
            console.log("rid_mdl == " , rid_mdl);
            
            jQuery("#rid_mdl").val(rid_mdl);
        });

        /*
        jQuery(document).ajaxComplete(function () {

            if (add_to_lib_pre_call_end_sr_page == true)
            {
                jQuery("#resource_title_mdl").val("");
                jQuery("#rid_mdl").val("");
                load_add_to_lib_modal();
                add_to_lib_pre_call_end_sr_page = false;
                //renderMyLibraryTree();
            }

        });
        */

        /*jQuery.fn.centerLibModal = function () {
            var h = jQuery(this).height();
            var w = jQuery(this).width();
            var wh = jQuery(window).height();
            var ww = jQuery(window).width();
            var wst = jQuery(window).scrollTop();
            var wsl = jQuery(window).scrollLeft();
            this.css("position", "absolute");
            var $top = Math.round((wh - h) / 2 + wst);
            var $left = Math.round((ww - w) / 2 + wsl);
            $top = $top - 180;
            this.css("top", $top + "px");
            this.css("left", ($left - 30) + "px");
            return this;
        }*/
        /*jQuery.fn.centerx = function () {
            var h = jQuery(this).height();
            var w = jQuery(this).width();
            var wh = jQuery(window).height();
            var ww = jQuery(window).width();
            var wst = jQuery(window).scrollTop();
            var wsl = jQuery(window).scrollLeft();
            this.css("position", "absolute");
            var $top = Math.round((wh - h) / 2 + wst);
            var $left = Math.round((ww - w) / 2 + wsl);
            this.css("top", $top + "px");
            this.css("left", ($left - 30) + "px");
            return this;
        }*/

        //*************************
        /*
        var element = jQuery('#add-to-lib-dialog'),
                originalY = element.offset().top;
        // Space between element and top of screen (when scrolling)
        var topMargin = 100;
        // Should probably be set in CSS; but here just for emphasis
        element.css('position', 'relative');
        jQuery(window).on('scroll', function (event) {
            var scrollTop = jQuery(window).scrollTop();
            element.stop(false, false).animate({
                top: scrollTop < originalY
                        ? 0
                        : scrollTop - originalY + topMargin
            }, 300);
        });
        */
        //**************************
        jQuery("#go_to_collection_btn").on("click", function () {

            if (window.last_selected_group > 0)
            {
                groupid = window.last_selected_group;

                jQuery.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: jQuery.param({action: 'get_group_record', groupid: groupid})
                }).done(function (data) {
                    //console.log((typeof data) , data);                          
                    var data = JSON.parse(data);
                    window.location = jQuery("#base_url").val().trim() + "/groups/" + data.slug;
                });
            } else {
                var rId = parseInt(jQuery("input[name='resourceid']").val());
                if (window.last_selected_collection > 0)
                {
                    rId = window.last_selected_collection;
                }
                window.location = jQuery("#base_url").val().trim() + "/oer/?rid=" + rId;
            }
        });
        //jQuery("#draggable-resource").draggable({
        jQuery(".draggable").draggable({
            revert: true,
            drag: function (event, ui) {
                // Keep the left edge of the element
                // at least 100 pixels from the container                               
            },
            connectToFancytree: true
        });

        jQuery(".droppable").droppable({
            drop: function (event, ui) {
                var sourceNode = jQuery(ui.helper).data("ftSourceNode");
                alert("Dropped source node " + sourceNode);
            }
        });


        /*jQuery("#my-collections-ul").sortable({
         revert: true
         });*/

        /*
         jQuery(".droppable").droppable({
         drop: function( event, ui ) {
         //$( this ).addClass( "ui-state-highlight" ).find( "p" ).html( "Dropped!" );
         console.log("event = " , event);
         console.log("ui = " , ui);
         },
         over: function (event, ui) {
         console.log("over....");
         },
         out: function (event, ui) {
         console.log("out ....");
         }
         });*/

    });
</script>


<style type="text/css">    
    #add-to-lib-alert-box button{width: 157px !important;float: none !important;}

    .mark-blod{font-weight: bold !important;}
    .mark-unblod{font-weight: normal !important;}

    .selected-resource{
        /* background: none repeat scroll 0 0 #99c736 !important;
          color: #ffffff !important;*/
    }
    .selected-resource:hover{
        /*background: none repeat scroll 0 0 #99c736 !important;color: #ffffff !important;*/
    }

    .selected-resource .fa-li{color: gray !important;}
    .my-library-folders ul li{
        /*
        cursor: pointer;
        margin-left: 20px !important;      
        */
    }
    .my-library-folders .toc-selection {      
        margin-left: 10px !important;      
    }
    .my-library-folders .groups-wrapper .toc-selection {      
        /*      margin-left: 20px !important;      */
    }

    .my-library-folders .groups-wrapper .groups-resources-wrapper .toc-selection {      
        margin-left: 28px !important;      
    }

    .my-library-folders .groups-wrapper .groups-resources-wrapper ul li{
        cursor: pointer;
        margin-left: 38px !important;      
    }


    .hide-collections{display: none;}
    .show-collections{display: initial;}
    .treeTopLevelClass
    {
        margin-right: 5px !important;
    } 

    .resource-drag-drop-wrapper
    {

        border: 0px solid red;
        padding: 6px;
    }
    #draggable-resource,.ui-draggable-dragging
    {
        border: 2px dashed #99c736;
        padding: 6px;

    }

    #draggable-resource p
    {
        font-size: 0.81em !important;
        margin-bottom: 0;
    }
    #draggable-resource .r-icon
    {
        margin-right: 5px !important;
    }

    .droppable
    {
        border: 0px solid red !important;
        min-width: 400px !important;
    }

    .new-droppable-resource
    {
        /*border: 2px dashed #99c736;*/
        color: #53830c !important;
    }
    .droppable-target-underline
    {
        border-bottom: 2px dashed gray !important; 
        margin-bottom: 3px !important;
    }

    .groups-wrapper
    {
        margin-top: 5px !important;
    }

    .toc-file {        
        margin-bottom: 0 !important;
        padding-bottom: 1px !important;
        padding-top: 0px !important;
    }

    /*  .my-library-folders .groups-wrapper .groups-resources-wrapper .droppable .toc-selection:hover {      
          color: inherit !important;
          border: 1px solid red !important;
      }*/
    .groups-wrapper .groups-resources-wrapper .droppable .toc-collection-folder:hover{      
        color: #70706e !important;
        background: none !important;      
    }

    /*  #draggable-resource:hover*/
    .rs-holder:hover
    {
        cursor: pointer !important;   
    }
    .rs-holder
    {  
        margin-right: 10px !important;
    }
    .ui-draggable
    {
        cursor: default !important;
    }

    .cls-cntr-1
    {
        border: 1px solid red !important;
        height: 5px !important;
    }
    /*
    #tree
    {
        border: 0px !important
    }
    */

    .droppable
    {
        border: 1px solid red !important;
    }

    ul.fancytree-container
    {
        border: 0px !important;
        background: none !important;
    }

    .icon-in-desc{ margin-top: 5px !important; }

    .fa-in-desc{ font-size: 15px; }

    .tree-root-node{font-size: 16px;}

    .add-to-lib-left {
        width:35%;
        float:left;
    }
    .add-to-lib-right {
        width:65%;
        float:left;
    }

    .rounded-borders-full {
        border-radius: 8px;
    }

    .border-grey {
        border: 1px solid #D1D1D1;
    }

</style>

<div id="app-container" ng-app="ngappmodal" ng-controller="libModalCtrl" ng-init="rid =<?php echo $resourceid; ?>" >
    <div id="add-to-lib-dialog">
        <div class="add-to-lib-left">

            <?php
            if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != 'en') {
                echo __('Select a "Collection" or "Group" in your library that you would like to place this resource. ', 'curriki');
            } else {
                ?>        
                Select a <span class="fancytree-ico-ef"> <span class="fancytree-icon icon-in-desc"></span> </span> <strong>Collection</strong> or <span class="fancytree-ico-ef"> <span class="fa fa-group icon-in-desc fa-in-desc"></span> </span> <strong>Group</strong> in your library that you would like to place this resource.
                <?php
            }
            ?>
            <!-- Select a location in your library that you would like to place this resource.-->
            <div class="resource-drag-drop-wrapper">
                <p style="margin-bottom: 0px;font-weight: bold;"><?php echo __('Click to drag and drop this:', 'curriki'); ?> </p>
                <div id="draggable-resource" class="ui-widget-content draggable">
                    <p><span class="rs-holder fa fa-arrows-alt"></span><span class="r-icon fa fa-file-image-o"></span> <span id="resource-title-modal"></span> </p>
                </div>
            </div>
        </div>
        <div class="add-to-lib-right">

            <div class="my-library-folders rounded-borders-full border-grey"> 
                <div class="scrollbar-wrapper">
                    <p style="margin-bottom: 5px;margin-left: 10px;"><strong><?php echo __('Expand Collections or Groups to add.', 'curriki'); ?></strong></p>
                    <div id="tree"></div>
                </div>            
            </div>
        </div>
    </div>
</div>