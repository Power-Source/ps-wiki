var wpCookies = { each: function(d, a, c) { var e, b; if (!d) { return 0 }
        c = c || d; if (typeof(d.length) != "undefined") { for (e = 0, b = d.length; e < b; e++) { if (a.call(c, d[e], e, d) === false) { return 0 } } } else { for (e in d) { if (d.hasOwnProperty(e)) { if (a.call(c, d[e], e, d) === false) { return 0 } } } } return 1 }, getHash: function(c) { var a = this.get(c),
            b; if (a) { this.each(a.split("&"), function(d) { d = d.split("=");
                b = b || {};
                b[d[0]] = d[1] }) } return b }, setHash: function(i, a, f, c, h, b) { var g = "";
        this.each(a, function(e, d) { g += (!g ? "" : "&") + d + "=" + e });
        this.set(i, g, f, c, h, b) }, get: function(h) { var g = document.cookie,
            f, d = h + "=",
            a; if (!g) { return }
        a = g.indexOf("; " + d); if (a == -1) { a = g.indexOf(d); if (a != 0) { return null } } else { a += 2 }
        f = g.indexOf(";", a); if (f == -1) { f = g.length } return decodeURIComponent(g.substring(a + d.length, f)) }, set: function(h, a, f, c, g, b) { document.cookie = h + "=" + encodeURIComponent(a) + ((f) ? "; expires=" + f.toGMTString() : "") + ((c) ? "; path=" + c : "") + ((g) ? "; domain=" + g : "") + ((b) ? "; secure" : "") }, remove: function(c, a) { var b = new Date();
        b.setTime(b.getTime() - 1000);
        this.set(c, "", b, a, b) } };

function getUserSetting(a, b) { var c = getAllUserSettings(); if (c.hasOwnProperty(a)) { return c[a] } if (typeof b != "undefined") { return b } return "" }

function setUserSetting(a, i, k) { if ("object" !== typeof userSettings) { return false } var h = "wp-settings-" + userSettings.uid,
        e = wpCookies.getHash(h) || {},
        g = new Date(),
        b, f = a.toString().replace(/[^A-Za-z0-9_]/, ""),
        j = i.toString().replace(/[^A-Za-z0-9_]/, ""); if (k) { delete e[f] } else { e[f] = j }
    g.setTime(g.getTime() + 31536000000);
    b = userSettings.url;
    wpCookies.setHash(h, e, g, b);
    wpCookies.set("wp-settings-time-" + userSettings.uid, userSettings.time, g, b); return a }

function deleteUserSetting(a) { return setUserSetting(a, "", 1) }

function getAllUserSettings() { if ("object" !== typeof userSettings) { return {} } return wpCookies.getHash("wp-settings-" + userSettings.uid) || {} };

jQuery(document).ready(function($) {
    $('.psource-wiki-revisions-form').each(function() {
        var $form = $(this);
        var $rows = $form.find('.post-revisions tbody tr');
        var $submit = $form.find('input[type="submit"]');
        var $status = $form.find('.psource-wiki-revisions-status');

        function selectedIndex(name) {
            return $rows.index($form.find('input[name="' + name + '"]:checked').closest('tr'));
        }

        function select(name, index) {
            if (index >= 0 && index < $rows.length) {
                $rows.eq(index).find('input[name="' + name + '"]').prop('checked', true);
            }
        }

        function normalizeSelection(changedName) {
            var leftIndex = selectedIndex('left');
            var rightIndex = selectedIndex('right');

            if ($rows.length < 2) {
                return;
            }

            if (leftIndex < 0 || rightIndex < 0) {
                select('right', 0);
                select('left', 1);
                return;
            }

            // Revisions are rendered newest first, so the older selection needs a larger row index.
            if (leftIndex <= rightIndex) {
                if (changedName === 'left') {
                    select('right', Math.max(0, leftIndex - 1));
                    if (leftIndex === 0) {
                        select('left', 1);
                    }
                } else {
                    select('left', Math.min($rows.length - 1, rightIndex + 1));
                    if (rightIndex === $rows.length - 1) {
                        select('right', rightIndex - 1);
                    }
                }
            }
        }

        function updateSelection() {
            var leftIndex = selectedIndex('left');
            var rightIndex = selectedIndex('right');
            var isValid = leftIndex > rightIndex && rightIndex >= 0;

            $rows.each(function(index) {
                $(this).find('input[name="left"]').prop('disabled', rightIndex >= 0 && index <= rightIndex);
                $(this).find('input[name="right"]').prop('disabled', leftIndex >= 0 && index >= leftIndex);
            });

            $rows.removeClass('is-revision-from is-revision-to');
            if (leftIndex >= 0) {
                $rows.eq(leftIndex).addClass('is-revision-from');
            }
            if (rightIndex >= 0) {
                $rows.eq(rightIndex).addClass('is-revision-to');
            }

            $submit.prop('disabled', !isValid);
            if (isValid) {
                var fromDate = $rows.eq(leftIndex).find('.revision-date').text().trim();
                var toDate = $rows.eq(rightIndex).find('.revision-date').text().trim();
                $status.text(Wiki.compareSelection.replace('%1$s', fromDate).replace('%2$s', toDate));
            } else {
                $status.text(Wiki.comparePrompt);
            }
        }

        function loadRevisionView(url, updateHistory) {
            var $view = $form.closest('.psource-wiki-revision-view');

            $view.attr('aria-busy', 'true').addClass('is-loading');
            $submit.prop('disabled', true);
            $status.text(Wiki.compareLoading);

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'html'
            }).done(function(html) {
                var responseDocument = new DOMParser().parseFromString(html, 'text/html');
                var responseView = responseDocument.querySelector('.psource-wiki-revision-view');

                if (!responseView) {
                    window.location.assign(url);
                    return;
                }

                ['.psource-wiki-revision-header', '.psource-wiki-revision-meta', '.psource-wiki-revision-fields'].forEach(function(selector) {
                    var currentElement = $view.children(selector).get(0);
                    var nextElement = responseView.querySelector(selector);

                    if (currentElement && nextElement) {
                        currentElement.replaceWith(nextElement);
                    } else if (!currentElement && nextElement) {
                        $view.children('.psource-wiki-revisions').before(nextElement);
                    } else if (currentElement) {
                        currentElement.remove();
                    }
                });

                if (responseDocument.title) {
                    document.title = responseDocument.title;
                }
                if (updateHistory) {
                    window.history.pushState({ psourceWikiRevision: true }, '', url);
                }

                var $heading = $view.find('.psource-wiki-revision-header h2').first();
                $heading.attr('tabindex', '-1').trigger('focus');
                updateSelection();
            }).fail(function() {
                window.location.assign(url);
            }).always(function() {
                $view.removeAttr('aria-busy').removeClass('is-loading');
            });
        }

        normalizeSelection();
        updateSelection();

        $form.on('change', 'input[name="left"], input[name="right"]', function() {
            normalizeSelection(this.name);
            updateSelection();
        });

        $form.on('submit', function(event) {
            if (!window.history || !window.DOMParser) {
                return;
            }

            event.preventDefault();
            loadRevisionView($form.attr('action') + '?' + $form.serialize(), true);
        });

        $(window).off('popstate.psourceWikiRevisions').on('popstate.psourceWikiRevisions', function() {
            var action = new URL(window.location.href).searchParams.get('action');
            if (action === 'diff' || action === 'history') {
                loadRevisionView(window.location.href, false);
            } else {
                window.location.reload();
            }
        });
    });

    $('.psource_wiki_revisions').find('.action-links').find('a').on("click", function(e) {
        if (!confirm(Wiki.restoreMessage)) {
            e.preventDefault();
        }
    });

    $('.psource_wiki_message').find('a.dismiss').on("click", function(e) {
        e.preventDefault();
        var $parent = $(this).parent();
        $parent.fadeOut(500, function() {
            $parent.remove();
        })
    });
});