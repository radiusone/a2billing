document.addEventListener("DOMContentLoaded", function() {
    /**
     * event listeners for CSV imports
     */
    let resetHidden = function () {
        document.querySelectorAll("#selected_cols option, #unselected_cols option").forEach(function (el) {
            el.selected = false;
            if (el.value.match(/^\s*$/)) {
                el.remove();
            }
        });
        const source = document.querySelector("#search_sources");
        const selected = document.querySelectorAll("#selected_cols option");
        if (selected.length === 0) {
            const opt = document.createElement("option");
            opt.disabled = true;
            opt.value = "";
            opt.textContent = " ";
            document.querySelector("#selected_cols optgroup")?.append(opt);
            source.value = "nochange";
        } else {
            source.value = Array.from(selected).map(el => el.value).join("|");
        }
    };

    let swapSelects = function (/** @param {HTMLOptionElement} */ opt) {
        if (opt.closest("select#selected_cols")) {
            document.querySelector("#unselected_cols optgroup").append(opt);
        } else {
            document.querySelector("#selected_cols optgroup").append(opt);
        }
        resetHidden();
    };

    document.querySelectorAll("#unselected_cols option, #selected_cols option").forEach(function (el) {
        el.addEventListener("dblclick", function () {
            swapSelects(this);
        });
    });

    document.querySelector("#add_col")?.addEventListener("click", function () {
        const opts = document.querySelector("#unselected_cols").selectedOptions;
        if (opts.length) {
            Array.from(opts).forEach(function (el) {
                document.querySelector("#selected_cols optgroup").append(el);
            });
            resetHidden();
        }
    });

    document.querySelector("#remove_col")?.addEventListener("click", function () {
        const opts = document.querySelector("#selected_cols").selectedOptions;
        if (opts.length) {
            Array.from(opts).forEach(function (el) {
                document.querySelector("#unselected_cols optgroup").append(el);
            });
            resetHidden();
        }
    });

    document.querySelector("#move_col_up")?.addEventListener("click", function () {
        const selectedOption = document.querySelector("#selected_cols").selectedOptions.item(0);
        const prev = selectedOption?.previousElementSibling;
        if (selectedOption && prev) {
            prev.before(selectedOption);
            resetHidden();
            selectedOption.selected = true;
        }
    });

    document.querySelector("#move_col_down")?.addEventListener("click", function () {
        const selectedOption = document.querySelector("#selected_cols").selectedOptions.item(0);
        const next = selectedOption?.nextElementSibling;
        if (selectedOption && next) {
            next.after(selectedOption);
            resetHidden();
            selectedOption.selected = true;
        }
    });

    /**
     * Day/time split inputs for rate card properties
     */
    document.querySelectorAll("div.daytime select, div.daytime input[type=time]").forEach(function (el) {
        el.addEventListener("change", function () {
            let day = this.closest("div.daytime").querySelector("select").value;
            let time = this.closest("div.daytime").querySelector("input[type=time]").value;
            let hidden = this.closest("div.daytime").querySelector("input[type=hidden]");
            let [hour, min] = time.split(":");
            hidden.value = (parseInt(day, 10) * 1440) + (parseInt(hour, 10) * 60) + parseInt(min, 10);
        });
    });

    /**
     * Standard popups
     */
    document.querySelectorAll("a.popup_trigger").forEach(function (el) {
        el.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            const uri = this.href ?? this.dataset.uri;
            const pu_sel = this.dataset.select ?? "1";
            const pu_form = this.dataset.formName ?? this.closest("form")?.name;
            const pu_field = this.dataset.fieldName ?? this.parentNode.querySelector("input,select")?.name;
            const uri_extra = this.dataset.uriExtra ?? "";
            const pu_name = this.dataset.windowName ?? "";
            const pu_options = this.dataset.popupOptions ?? "scrollbars=1,width=750,height=450,top=50,left=100,scrollbars=1";
            window.open(
                `${uri}?popup_select=${pu_sel}&popup_formname=${pu_form}&popup_fieldname=${pu_field}${uri_extra}`,
                pu_name,
                pu_options
            );
        });
    });

    /**
     * Search form date enabling
     */
    document.querySelectorAll(".date-input-enabler").forEach(function (el) {
        el.addEventListener("change", function () {
            this.closest("div.input-group").querySelector("input, select").disabled = !this.checked;
            if (this.checked) {
                let checks;
                if (this.classList.contains("relative-date")) {
                    checks = this.closest("form").querySelectorAll(".date-input-enabler:not(.relative-date)");
                } else {
                    checks = this.closest("form").querySelectorAll(".date-input-enabler.relative-date");
                }
                checks.forEach(el => el.checked = false);
            }
        });
    });

    /**
     * Invoice/Receipt lock buttons
     */
    document.querySelectorAll(".lock").forEach(function (el) {
        el.addEventListener("click", function() {
            fetch(`A2B_entity_invoice.php?action=lock&id=${this.dataset.primaryKey}`)
                .then(() => location.reload());
        });
    });

    /**
     * "Has Many" edits (e.g. restricted numbers in card properties)
     */
    document.querySelectorAll("button.has-many-add").forEach(function (el) {
        el.addEventListener("click", function () {
            const index = this.dataset.index;
            const value = this.dataset.value;
            const form = document.getElementById("editForm");
            form.querySelector("input[name=form_action]").value = "del-content";
            form.querySelector("input[name=form_el_index]").value = index;
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "del-content-value";
            hidden.value = value;
            form.append(hidden);
            form.dispatchEvent(new SubmitEvent("submit"));
        });
    });

    document.querySelectorAll("button.has-many-delete").forEach(function (el) {
        el.addEventListener("click", function () {
            const index = this.dataset.index;
            const input_id = this.dataset.inputId;
            const value = document.getElementById(input_id).value;
            const form = document.getElementById("editForm");
            form.querySelector("input[name=form_action]").value = "add-content";
            form.querySelector("input[name=form_el_index]").value = index;
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "add-content-value";
            hidden.value = value;
            form.append(hidden);
            form.dispatchEvent(new SubmitEvent("submit"));
        });
    });

    /**
     * Confirm action; used on search page delete, all data archive
     */
    document.querySelectorAll("a.confirm-with-message").forEach(function (el) {
        el.addEventListener("click", function (e) {
            const msg = this.dataset.message;
            if (confirm(msg) !== true) {
                e.stopPropagation();
                e.preventDefault();
                return false;
            }
        });
    });

    /**
     * Page count display
     */
    document.getElementById("displaylimit")
        ?.addEventListener("change", function () {
            document.getElementById("displaylimit_form")?.requestSubmit();
        });

    /**
     * Used by <SELECT> buttons on popup windows to send a value back to the opener
     */
    window.sendValue = function(selvalue, othervalue, otherfield) {
        const table = document.querySelector("table.list-view-table");
        const formname = table.dataset.popupFormname;
        const fieldname = table.dataset.popupFieldname;
        window.opener.document.querySelector(`form[name=${CSS.escape(formname)}] [name=${CSS.escape(fieldname)}]`).value = selvalue;
        if (othervalue && otherfield) {
            // only used in A2B_entity_card popups called from A2B_entity_friend
            window.opener.document.querySelector(`form[name=${CSS.escape(formname)}] [name=${CSS.escape(otherfield)}]`).value = othervalue;
        }
        window.close();
    }
});
