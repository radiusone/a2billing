$(function() {
    let calendars;
    $("a.popup_trigger").on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        const uri = $(this).attr("href") || $(this).data("uri");
        const pu_sel = $(this).data("select") || "1";
        const pu_form = $(this).data("formName") || $(this).parents("form").attr("name");
        const pu_field = $(this).data("fieldName") || $(this).prev("input,select").attr("name");
        const uri_extra = $(this).data("uriExtra") || "";
        const pu_name = $(this).data("windowName") || "";
        const pu_options = $(this).data("popupOptions") || "scrollbars=1,width=550,height=330,top=20,left=100,scrollbars=1";
        window.open(
            `${uri}?popup_select=${pu_sel}&popup_formname=${pu_form}&popup_fieldname=${pu_field}${uri_extra}`,
            pu_name,
            pu_options
        );
    });
    $("a.calendar_trigger")
        .each(function() {
            let id, el;
            if ($(this).data("fieldName")) {
                id = $(this).data("fieldName");
                el = $(`input[name=${id}]`).first();
            } else {
                el = $(this).prev("input");
                id = el.attr("name");
            }
            if (!el.length) {
                return;
            }
            calendars[id] = new calendaronlyminutes(el[0]);
            calendars[id].year_scroll = false;
            calendars[id].time_comp = true;
            calendars[id].formatpgsql = true;
        })
        .on("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data("fieldName") || $(this).prev("input").attr("name");
            calendars[id].popup();
        });
});
