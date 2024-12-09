jQuery(document).on('elementor/popup/show', (event, popupId, popupDocument) => {
    if (load_add_to_lib_modal) {
        jQuery("#resource_title_mdl").val("");
        jQuery("#rid_mdl").val("");
        load_add_to_lib_modal();
        add_to_lib_pre_call_end_sr_page = false;
        //renderMyLibraryTree();
    }
});