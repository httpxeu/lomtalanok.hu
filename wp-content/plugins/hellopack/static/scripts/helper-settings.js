! function(n) {
    "use strict";
    n.HELLOPACK_Conditional_Logic = function(e) {
        function t() {
            for (var e = [], t = document.querySelectorAll('#hellopack input:not([type="range"]):not(.wp-color-picker):not(.hellopack-range-slider-value):not(.hellopack-color-picker-input), #hellopack select, #hellopack textarea'), n = document.querySelectorAll('#hellopack [class^="hellopack"][data-field-condition]'), a = 0, i = t.length; a < i; a++) {
                var o = t[a];
                if (o.name) {
                    var r = o.name.match(/hellopack\[(.*)\]/) ? o.name.match(/hellopack\[(.*)\]/)[1].replace("][", "") : o.name,
                        l = e[r];
                    if ("checkbox" === o.type) c = o.checked ? o.value : 0;
                    else if ("radio" === o.type) c = o.checked ? o.value : "";
                    else if (o.multiple && "SELECT" === o.tagName)
                        for (var c = [], s = o.children, p = 0, u = s.length; p < u; p++) s[p].selected && s[p].value && c.push(s[p].value);
                    else c = o.value;
                    c && !o.multiple && -1 < o.name.indexOf("[]") ? (e[r] || (e[r] = []), e[r].push(c)) : l && !c || (e[r] = c)
                }
            }
            for (a = 0, i = n.length; a < i; a++) {
                var d, g = n[a];
                g.condition ? d = g.condition : (d = g.getAttribute("data-field-condition"), g.condition = d, g.setAttribute("data-field-condition", "")), d = JSON.parse(d), g.style.display = m(d, e) ? "block" : "none"
            }
        }
        var m = function(e, t) {
                if ("object" != typeof e) return 0;
                var n, a = 0,
                    i = 0;
                for (n in e) e.hasOwnProperty(n) && "object" == typeof(n = e[n]) && (a += (n.field ? o : m)(n, t), i++);
                return a === i || "OR" === (e.relation || "").toUpperCase() && 0 < a ? 1 : 0
            },
            o = function(e, t) {
                var n = t[e.field],
                    a = e.value;
                switch ((e.compare || "").toUpperCase()) {
                    case "CONTAINS":
                        return n && a && -1 < n.toString().indexOf(a.toString());
                    case "IN":
                        return -1 < a.indexOf(n);
                    case "NOT IN":
                        return a.indexOf(n) < 0;
                    case "==":
                        return n == a;
                    case "===":
                        return n === a;
                    case "!=":
                        return n != a;
                    case "!==":
                        return n !== a;
                    case ">":
                        return a < n;
                    case ">=":
                        return a <= n;
                    case "<":
                        return n < a;
                    case "<=":
                        return n <= a;
                    default:
                        return !1
                }
            };
        return t(), e && n(document).on("change", '#hellopack input:not([type="range"]):not(.wp-color-picker):not(.hellopack-range-slider-value):not(.hellopack-color-picker-input), #hellopack select, #hellopack textarea', t), this
    }
}(jQuery),
function(s) {
    "use strict";
    s.HELLOPACK_Conditional_Logic(!0), window.addEventListener("hellopack_builder.render_field", function() { s.HELLOPACK_Conditional_Logic() });

    function o(e) { var t; "function" == typeof Event ? (t = new Event("change", { bubbles: !0 }), e.dispatchEvent(t)) : document.createEvent ? ((t = document.createEvent("HTMLEvents")).initEvent("change", !0, !0), e.dispatchEvent(t)) : ((t = document.createEventObject()).eventType = "change", e.fireEvent("on" + t.eventType, t)) }

    function r(e, t, n) { var a, e = parseFloat(e.value.replace(",", ".")); return n && ("" === e || null === e || isNaN(e)) ? "" : (n = parseFloat(t.min || t.getAttribute("data-min")) || 0, a = parseFloat(t.max || t.getAttribute("data-max")) || 0, t = parseFloat(t.step) || 1, t = Math.floor(t) !== t ? t.toString().split(".")[1].length : 0, e = isNaN(e) ? 0 : parseFloat(e), e = Math.max(n, Math.min(a, e)), parseFloat(e.toFixed(t))) }

    function l(e, t) { var n, a, i = JSON.parse(t.getAttribute("data-units")); return Array.isArray(i) && i ? (a = i.filter(function(e) { return e }), n = new RegExp(a.join("|"), "gi"), e = e.value.toString().match(n), e = a.length === i.length || e ? e ? e.shift() : i[0] : "", t.hellopack_unit = e, n = t, a = e, t = i.indexOf(a) || 0, i = JSON.parse(n.getAttribute("data-steps")), a = Array.isArray(i) ? i[t] : 1, n.step = a || 1, e) : "" }

    function i(e) {
        var t = e.min,
            n = e.max,
            n = 50 < (n = 100 * (e.value - t) / (n - t)) ? n - .2 : .2 + n;
        e.style.backgroundSize = ("" !== e.nextSibling.value ? Math.min(100, hellopack_L10n.RTL ? 100 - n : n) : 0) + "% 100%"
    }

    function t() {
        s(".hellopack-code").each(function() {
            var e, t = s(this);
            t.next(".CodeMirror").length || (t.closest("div").add(t).css({ display: "block", width: "100%", height: t.data("height") + "px" }), e = CodeMirror.fromTextArea(t[0], { mode: t.data("mode"), value: t.val(), theme: "material", htmlMode: !0, matchClosing: !0, lineNumbers: !0, styleActiveLine: !0, matchBrackets: !0, nonEmpty: !1, indentWithTabs: !0, indentUnit: 4, scrollbarStyle: "simple", direction: hellopack_L10n.RTL ? "rtl" : "ltr" }), s(e.display.wrapper).find("textarea").attr("aria-label", "editor"), e.on("blur", function(e) { t.val(e.getValue()) }), e.on("change", function(e) { t.val(e.getValue()), o(t[0]) }))
        })
    }

    function e() {
        var e = s('[name="hellopack[type]"]:checked').val();
        s(".hellopack-table.hellopack-table-card-sizes [data-colname]:not(:first-child)").show(), s("justified" === e ? '.hellopack-table.hellopack-table-card-sizes [data-colname="columns"]' : '.hellopack-table.hellopack-table-card-sizes [data-colname="height"]').hide(), "masonry" !== e && "justified" !== e || s('.hellopack-table.hellopack-table-card-sizes [data-colname="ratio"]').hide()
    }

    function n() {
        var e = _.val(),
            t = s('[name="hellopack[transition]"]').val(),
            n = s('[name="hellopack[timing_function]"]').val();
        e && k.hasOwnProperty(e) && (e = k[e]).hasOwnProperty("hidden") && e.hasOwnProperty("visible") && (e.hidden.transition = "none", s(".hellopack-animation-placeholder").removeAttr("style"), s(".hellopack-animation-placeholder").css(e.hidden), clearTimeout(d), d = setTimeout(function() { "custom" === n && (n = s('[name="hellopack[cubic_bezier_function]"]').val()), e.visible.transition = "all " + parseInt(t) + "ms " + n, s(".hellopack-animation-placeholder").css(e.visible) }, 50))
    }

    function a(e) { x.css({ color: e, borderColor: e, backgroundColor: e }) }

    function c(e) { A.css({ transform: "scale(" + parseFloat(e) + ")" }) }

    function p(e) {
        e.find("tbody tr").each(function(n) {
            s(this).find("td").each(function() {
                var e = s(this).find("input, select"),
                    t = "hellopack-" + Math.random().toString(36).substr(2, 9);
                s(this).find("label").attr("for", t), e.each(function() {
                    var e = s(this)[0].name;
                    s(this)[0].name = e.replace(/\[(\d+)\]/g, "[" + n + "]"), "hidden" !== s(this).attr("type") && (s(this)[0].id = t)
                })
            })
        })
    }
    s(document).on("click", ".hellopack-settings-collapse", function() {
        var e = s(this).val();
        ("hellopack_settings_collapsed" === e ? s(".hellopack-settings").parent() : s(".hellopack-builder-header")).toggleClass("hellopack-settings-collapsed"), document.cookie = e + "=" + (s(this).prop("checked") ? 1 : 0)
    }), s(document).on("click", ".hellopack-image:first-child", function() {
        var r = s(this),
            l = r.is("li"),
            c = wp.media({ id: "hellopack-media-popup", frame: "select", library: { type: l ? r.parent(".hellopack-gallery").data("mime-type") : "image" }, multiple: !!l && "add" });
        c.on("open", function() {
            var e = [],
                t = c.state().get("selection"),
                n = (r.closest(".hellopack-field-input").find("input").each(function() { e.push(s(this).val()) }), wp.media.query({ posts_per_page: -1, post__in: e }));
            n.more().done(function() { t.add(n.models) })
        }).on("select", function() {
            var n, a, i, o = document.createDocumentFragment(),
                e = c.state().get("selection");
            r.closest(".hellopack-gallery").find("li").not(r).remove(), e.each(function(e, t) { i = r, l && e.id && ((i = r.clone(!0)).removeAttr("title"), i.removeAttr("mime-type"), i.find(".hellopack-plus-icon").remove(), i.find(".hellopack-image-filename").remove(), o.appendChild(i[0])), a = (a = e.get("sizes")) ? a.medium || a.thumbnail || a.full : null, a = a ? a.url : e.get("url"), "image" !== e.get("type") && (a = (a = e.get("thumb")) ? a.src : null, n = e.get("filename"), a = a || (!(a = e.get("icon")) || -1 < a.indexOf("images/media/") ? "" : a), n) && i.append('<span class="hellopack-image-filename">' + n + "</span>"), i.find("input").val(e.id), i.find(".hellopack-image-background").css("background-image", a ? "url(" + a + ")" : "") }), s(o).insertAfter(r), c.detach(), c.dispose(), r.find("input").first().trigger("change")
        }).on("close", function() { c.detach(), c.dispose() }).open()
    }), s(document).on("click", ".hellopack-image-delete", function(e) {
        e.preventDefault(), e.stopPropagation();
        var t, e = s(this).closest(".hellopack-image");
        e.is("li") ? (t = e.parent(), e.remove(), t.find("input").first().trigger("change")) : (e.find("input").val("").trigger("change"), e.find(".hellopack-image-background").removeAttr("style"))
    });
    var u, d, g, m = s(".hellopack-gallery"),
        b = (m.length && m.sortable({ items: "> li:not(:first-child)", placeholder: "hellopack-image", revert: 150 }).disableSelection(), s(document).on("click", ".hellopack-upload-media", function(e) {
            e.preventDefault(), e.stopPropagation(), e.stopImmediatePropagation();
            var t = s(this).prev("input"),
                n = wp.media({ library: { type: s(this).data("mime-type") } }).on("select", function() { t.val(n.state().get("selection").first().get("url")), o(t[0]), n.detach(), n.dispose() }).on("close", function() { n.detach(), n.dispose() }).open()
        }), s(document).on("focusout", ".hellopack-number", function(e) {
            e = e.target;
            e.value = r(e, e, !0)
        }), s(document).on("input", ".hellopack-text-number", function(e) {
            var i = e.target;
            clearTimeout(u), u = setTimeout(function() {
                var e = i.selectionEnd,
                    t = i.value.length,
                    n = l(i, i),
                    a = r(i, i, !0);
                i.value = "" !== a && 0 !== a ? a + n : a, i.selectionStart = i.selectionEnd = Math.max(0, e - Math.max(0, t - i.value.length)), o(i)
            }, 800)
        }), s(document).on("focusout", ".hellopack-text-number", function(e) {
            var e = e.target,
                t = l(e, e),
                n = r(e, e, !0);
            clearTimeout(u), e.value = "" !== n && 0 !== n ? n + t : n, o(e)
        }), s(document).on("input change", "input.hellopack-range-slider", function(e) {
            e.preventDefault(), e.stopPropagation(), e.stopImmediatePropagation();
            var e = e.target,
                t = e.nextSibling,
                n = e.hellopack_unit || l(t, e);
            i(e), t.value = "" === t.value && 0 === parseFloat(e.value) ? t.value : e.value + n, o(t)
        }), s(document).on("input", ".hellopack-range-slider-value", function(e) {
            var n = e.target,
                a = n.previousSibling;
            clearTimeout(u), a.value = r(n, a, !1), i(a), u = setTimeout(function() {
                var e = n.selectionEnd,
                    t = n.value.length;
                l(n, a), a.value = r(n, a, !1), s(a).trigger("change"), n.selectionStart = n.selectionEnd = Math.max(0, e - Math.max(0, t - n.value.length))
            }, 1e3)
        }), s(document).on("focusout", ".hellopack-range-slider-value", function(e) {
            var e = e.target,
                t = e.previousSibling;
            clearTimeout(u), l(e, t), t.value = r(e, t, !1), s(t).trigger("change")
        }), t(), window.addEventListener("hellopack_builder.render_field", function(e) { t() }), s('select[name="hellopack[post_type][]"]').on("change", function() { b() }), function() {
            var e = s('select[name="hellopack[post_type][]"]').val();
            s('select[name="hellopack[tax_query][]"]').attr("data-post", e || ["post"])
        }),
        f = (b(), s('select[name="hellopack[meta_key]"]').attr("data-post", "key"), s('select[name="hellopack[meta_key_upper]"]').attr("data-post", "key"), s(document).on("click", ".hellopack-add-relation", function(e) {
            e.preventDefault();
            e = 0 < (e = s(this).prev(".hellopack-meta-clauses")).length ? e.clone() : s(this).closest(".hellopack-meta-clauses");
            f(e.clone(), s(this))
        }), s(document).on("click", ".hellopack-add-metakey", function(e) { e.preventDefault(), f(s(this).prev().clone(), s(this)) }), s(document).on("click", ".hellopack-delete-metakey", function(e) { e.preventDefault(), s(this).parent().remove(), v() }), function(e, t) {
            e.find("input").val(""), e.find("select").each(function() {
                var e = s(this);
                e.val(e.find("option").first().val()), e.insertAfter(e.parent())
            }), e.find("label").each(function() {
                var e = s(this),
                    t = e.next().find("input, select"),
                    n = "hellopack-" + Math.random().toString(36).substr(2, 9);
                e.attr("for", n), t.attr("id", n)
            }), e.find(".hellopack-add-relation").remove(), e.find(".hellopack-meta-clauses").remove(), e.find(".hellopack-meta-clause").not(":eq(0), .hellopack-add-metakey").remove(), e.find(".hellopack-select-wrapper").remove(), e.insertBefore(t), HELLOPACK_Select_Init(), v()
        }),
        h = document.querySelector(".hellopack-meta-clauses"),
        v = function(e, t) {
            t = t || [0];
            for (var n = 0, a = (e = e || document.querySelector(".hellopack-meta-clauses")).children, i = 0, o = a.length; i < o; i++) {
                var r = a[i];
                r.classList.contains("hellopack-meta-clause") && !r.classList.contains("hellopack-add-metakey") ? (w(r, t, n), n++) : r.classList.contains("hellopack-meta-clauses") && (h === e ? t = [t[0] + 1, 0] : t.push(0), v(r, t))
            }
        },
        w = function(e, t, n) { for (var a = e.parentElement.querySelector('select[name*="relation"]'), i = a.name.match(/\[(.*?)\]/g), o = 0 < t.slice(0, -1).length ? "[" + t.slice(0, -1).join("][") + "]" : "", r = (a.name = "hellopack[meta_query]" + o + i.pop(), t[t.length - 1] = n, e.querySelectorAll("input, select")), l = 0, c = r.length; l < c; l++) i = (a = r[l]).name.match(/\[(.*?)\]/g), o = 0 < t.length ? "[" + t.join("][") + "]" : "", a.name = "hellopack[meta_query]" + o + i.pop(); return t },
        y = (s(document).on("change", '[name="hellopack[type]"]', function() { e() }), s('input[name="hellopack[card_sizes][0][browser]"]').prop("disabled", !0).val(9999), e(), s(".hellopack-layout-facets").length && s(".hellopack-layout-facets").sortable({
            items: ".hellopack-layout-facet",
            connectWith: ".hellopack-layout-facets",
            appendTo: s("#hellopack"),
            helper: "clone",
            revert: 150,
            start: function(e, t) {
                t.item.show();
                var n = parseFloat(getComputedStyle(t.item[0]).width);
                t.helper.css("width", n), t.placeholder.css("width", n), t.item.hide()
            },
            receive: function(e, t) {
                var n = t.item.find("input"),
                    a = n.closest("ul");
                a.closest(".hellopack-available-facets").length ? n.removeAttr("name") : n.attr("name", "hellopack[grid_layout][" + a.data("area") + "][facets][]"), t.item.removeAttr("style"), y()
            }
        }).disableSelection(), function() {
            s(".hellopack-layout .hellopack-layout-facets").each(function() {
                var e = s(this).find("li");
                e.first()[1 < e.length ? "fadeOut" : "fadeIn"](150)
            })
        }),
        _ = (y(), s('.hellopack-layout-buttons [data-button="settings"]').on("click", function(e) {
            var t = s(this).closest(".hellopack-layout-area").find("> .hellopack-layout-style");
            t.attr("style") ? t.removeAttr("style") : (s(".hellopack-layout-style").removeAttr("style"), t.show())
        }), s(document).on("click", function(e) { s(e.target).is('[data-button="settings"]') || s(e.target).closest(".hellopack-layout-style").length || s(e.target).closest(".hellopack-select-results").length || s(".hellopack-layout-style").removeAttr("style") }), s('.hellopack-layout-buttons [data-button^="flex-"], .hellopack-layout-buttons [data-button^="center"]').on("click", function() {
            var e = s(this),
                t = e.data("button").replace("text-", ""),
                n = e.closest(".hellopack-layout-area");
            n.find(".hellopack-layout-facets").css("justify-content", t || ""), n.find('[type="radio"][value="' + t + '"]').prop("checked", !0).trigger("change"), e.prevAll().add(e.nextAll()).removeClass("hellopack-layout-button-active"), e.addClass("hellopack-layout-button-active")
        }), s('select[name="hellopack[animation]"]')),
        k = 0 < _.length ? hellopack_animations_L10n : {},
        A = (s('[name="hellopack[animation]"], [name="hellopack[transition]"], [name="hellopack[timing_function]"], [name="hellopack[cubic_bezier_function]"]').on("change", function() { n() }), s(".hellopack-run-animation").on("click", function(e) { e.preventDefault(), n() }), s("." + s('[name="hellopack[loader_type]"]:checked').val())),
        x = A.find("div"),
        S = s('[name="hellopack[loader_color]"]'),
        m = s('[name="hellopack[loader_type]"]'),
        E = s('[name="hellopack[loader_size]"]'),
        m = (m.on("change", function() { A.removeAttr("style"), x.removeAttr("style"), A = s("." + s(this).val().trim()), x = A.find("div"), a(S.val()), c(E.val()) }), E.on("change", function() { c(s(this).val()) }), S.on("change", function() { a(s(this).val()) }), a(S.val()), c(E.val()), s(".hellopack-repeater tbody"));
    m.length && (g = s("<tr>"), m.sortable({ axis: "y", handle: ".hellopack-repeater-sort", revert: 150, start: function(e, t) { t.placeholder && t.placeholder.children().each(function(e, t) { 0 !== e && t.remove() }) }, sort: function(e, t) { s(this).find("tr").length < 3 ? (t.item.removeAttr("style"), t.item.removeAttr("class"), t.placeholder.css("display", "none")) : (s(".hellopack-repeater-sort").addClass("hellopack-dragging"), g.insertAfter(t.item)) }, stop: function(e, t) { s(".hellopack-repeater-sort").removeClass("hellopack-dragging"), g.remove(), p(s(this).closest(".hellopack-repeater")) } }).disableSelection()), s(document).on("click", ".hellopack-add-row", function(e) {
        e.preventDefault();
        var e = s(this).prev(".hellopack-table-wrapper").find(".hellopack-repeater"),
            t = e.find("tbody"),
            n = t.find("tr");
        n.length >= e.attr("data-limit") - 1 && s(this).hide(), n.length >= e.attr("data-limit") || ((n = n.first().clone()).find(".ui-sortable-handle").show(), n.find("input").val(""), n.find("select").each(function() {
            var e = s(this);
            e.val(e.find("option").first().val()), e.insertAfter(e.parent())
        }), n.find(".hellopack-select-wrapper").remove(), n.find(".hellopack-color-holder").remove(), n.find(".hellopack-color-picker-preview").removeAttr("style"), t.append(n), n.find(".hellopack-range-slider-value").trigger("input"), n.find("input.hellopack-range-slider").trigger("input"), HELLOPACK_Select_Init(), HELLOPACK_Gradient_Init(), p(e))
    }), s(document).on("click", ".hellopack-delete-row", function(e) {
        var t = s(this).closest(".hellopack-repeater"),
            n = t.find("tbody").find("tr").length,
            a = s(this).closest("tr");
        t.next(".hellopack-add-row").show(), n < 2 || (a.remove(), p(t))
    })
}(jQuery);