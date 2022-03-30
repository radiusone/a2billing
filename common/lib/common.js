$(function() {

    /*
    Standard popups
     */
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

    /*
    Calendar popups
     */
    const calendars = {};
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

    /*
    Search form dates
     */
    function setValidDay(monthEl, dayEl)
    {
        let ym = monthEl.val().split(/-/);
        if (!ym[1]) {
            return;
        }
        const year = parseInt(ym[0]);
        const month = parseInt(ym[1]);
        let limit;
        const days = ["31", "28", "31", "30", "31", "30", "31", "31", "30", "31", "30", "31"];
        limit = days[month - 1];
        if (month === 2 && year % 4 === 0 && year % 100 > 0) {
            limit = 29;
        }
        if (parseInt(dayEl.val()) > limit) {
            dayEl.val(limit.toString());
        }
    }

    const fromDay = $("#fromstatsday_sday");
    const fromMonth = $("#fromstatsmonth_sday");
    const toDay = $("#tostatsday_sday");
    const toMonth = $("#tostatsmonth_sday");

    fromDay.add(fromMonth).prop("disabled", true);
    $("#search_fromday").on("change", () => fromDay.add(fromMonth).prop("disabled", !this.checked));
    fromMonth.on("change", ev => setValidDay(fromMonth, fromDay));

    toDay.add(toMonth).prop("disabled", true);
    $("#search_today").on("change", () => toDay.add(toMonth).prop("disabled", !this.checked));
    toMonth.on("change", ev => setValidDay(toMonth, toDay));

    const fromDay2 = $("#fromstatsday_sday_bis");
    const fromMonth2 = $("#fromstatsmonth_sday_bis");
    const toDay2 = $("#tostatsday_sday_bis");
    const toMonth2 = $("#tostatsmonth_sday_bis");

    fromDay2.add(fromMonth2).prop("disabled", true);
    $("#search_fromday_bis").on("change", () => fromDay2.add(fromMonth2).prop("disabled", !this.checked));
    fromMonth2.on("change", ev => setValidDay(fromMonth2, fromDay2));

    toDay2.add(toMonth2).prop("disabled", true);
    $("#search_today_bis").on("change", () => toDay2.add(toMonth2).prop("disabled", !this.checked));
    toMonth2.on("change", ev => setValidDay(toMonth2, toDay2));
});
