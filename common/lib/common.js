$(function() {
    /**
     * event listeners for CSV imports
     */
    let resetHidden = function() {
        $("#selected_cols option, #unselected_cols option")
            .prop("selected", false)
            .filter((i, el) => el.value.match(/^\s*$/))
            .remove();
        let selected = $("#selected_cols option");
        if (selected.length === 0) {
            $("#selected_cols optgroup").append("<option value='' disabled='disabled'>&nbsp;</option>");
            $("#search_sources").val("nochange");
        } else {
            $("#search_sources").val(selected.map((i, el) => el.value).get().join("|"));
        }
    };

    let swapSelects = function(/** @param {jQuery} */ opt) {
        opt.appendTo(
            opt.closest("select#selected_cols").length
                ? $("#unselected_cols optgroup")
                : $("#selected_cols optgroup")
        );
        resetHidden();
    };

    $("#unselected_cols option, #selected_cols option").on("dblclick", function() {
        swapSelects($(this));
    });

    $("#add_col").on("click", function () {
        let opts = $("#unselected_cols option:selected");
        if (opts.length) {
            opts.appendTo($("#selected_cols optgroup"));
            resetHidden();
        }
    });

    $("#remove_col").on("click", function () {
        let opts = $("#selected_cols option:selected");
        if (opts.length) {
            opts.appendTo($("#unselected_cols optgroup"));
            resetHidden();
        }
    });

    $("#move_col_up").on("click", function () {
        let selectedOption = $("#selected_cols option:selected").first();
        let prev = selectedOption.prev("option");

        if (selectedOption.length && prev.length) {
            selectedOption.insertBefore(prev);
            resetHidden();
            selectedOption.prop("selected", true);
        }
    });

    $("#move_col_down").on("click", function () {
        let selectedOption = $("#selected_cols option:selected").first();
        let next = selectedOption.next("option");

        if (selectedOption.length && next.length) {
            selectedOption.insertAfter(next);
            resetHidden();
            selectedOption.prop("selected", true);
        }
    });

    /**
     * Day/time split inputs
     */
    $("div.daytime select, div.daytime input[type=time]").on("change", function() {
        let day = $(this).closest("div.daytime").find("select").val();
        let time = $(this).closest("div.daytime").find("input[type=time]").val();
        let hidden = $(this).closest("div.daytime").find("input[type=hidden]");
        let [hour, min] = time.split(":");
        hidden.val((parseInt(day, 10) * 1440) + (parseInt(hour, 10) * 60) + parseInt(min, 10));
    });

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
        const pu_options = $(this).data("popupOptions") || "scrollbars=1,width=750,height=450,top=50,left=100,scrollbars=1";
        window.open(
            `${uri}?popup_select=${pu_sel}&popup_formname=${pu_form}&popup_fieldname=${pu_field}${uri_extra}`,
            pu_name,
            pu_options
        );
    });

    /***
    Search form dates
    ***/
    $(".date-input-enabler")
        .on("change", function() {
            const id = this.getAttribute("id").replace(/^enable_(.*?)(_months)?/, "$1");
            $(`#${id}`).prop("disabled", !this.checked);
            if ($(this).hasClass("months-ago-enabler") && this.checked) {
                $(".date-input-enabler:not(.months-ago-enabler)").prop("checked", false).change();
            } else if (this.checked) {
                $(".months-ago-enabler").prop("checked", false).change();
            }
        })
        .trigger("change");

    /* Old search form dates */

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

    const fromDayCheck = $("#search_fromday");
    const fromDay = $("#fromstatsday_sday");
    const fromMonth = $("#fromstatsmonth_sday");
    const toDayCheck = $("#search_today");
    const toDay = $("#tostatsday_sday");
    const toMonth = $("#tostatsmonth_sday");

    fromDay.add(fromMonth).prop("disabled", true);
    fromDayCheck.on("change", e => fromDay.add(fromMonth).prop("disabled", !e.target.checked));
    fromMonth.on("change", () => setValidDay(fromMonth, fromDay));

    toDay.add(toMonth).prop("disabled", true);
    toDayCheck.on("change", e => toDay.add(toMonth).prop("disabled", !e.target.checked));
    toMonth.on("change", () => setValidDay(toMonth, toDay));

    const fromDayCheck2 = $("#search_fromday_bis");
    const fromDay2 = $("#fromstatsday_sday_bis");
    const fromMonth2 = $("#fromstatsmonth_sday_bis");
    const toDayCheck2 = $("#search_today_bis");
    const toDay2 = $("#tostatsday_sday_bis");
    const toMonth2 = $("#tostatsmonth_sday_bis");

    fromDay2.add(fromMonth2).prop("disabled", true);
    fromDayCheck2.on("change", e => fromDay2.add(fromMonth2).prop("disabled", !e.target.checked));
    fromMonth2.on("change", () => setValidDay(fromMonth2, fromDay2));

    toDay2.add(toMonth2).prop("disabled", true);
    toDayCheck2.on("change", e => toDay2.add(toMonth2).prop("disabled", !e.target.checked));
    toMonth2.on("change", () => setValidDay(toMonth2, toDay2));

    fromDayCheck.trigger("change");
    toDayCheck.trigger("change");
    fromDayCheck2.trigger("change");
    toDayCheck2.trigger("change");
});
