$(function() {
    $("a.popup_trigger").on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        var uri = $(this).attr("href") || $(this).data("uri");
        var pu_sel = $(this).data("select") || "1";
        var pu_form = $(this).data("formName") || $(this).parents("form").attr("name");
        var pu_field = $(this).data("fieldName") || $(this).prev("input,select").attr("name");
        var uri_extra = $(this).data("uriExtra") || "";
        var pu_name = $(this).data("windowName") || "";
        var pu_options = $(this).data("popupOptions") || "scrollbars=1,width=550,height=330,top=20,left=100,scrollbars=1";
        window.open(
            `${uri}?popup_select=${pu_sel}&popup_formname=${pu_form}&popup_fieldname=${pu_field}${uri_extra}`,
            pu_name,
            pu_options
        );
    });
});
