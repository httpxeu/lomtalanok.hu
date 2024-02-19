jQuery(document).ready(function($) {


    const { __, _x } = wp.i18n;

    __('__', 'hellopack');
    _x('_x', '_x_context', 'hellopack');




    var $doc, $hp_check_api, $report_box, $hp_deactivate_api,
        $hp_cleanup_settings, $hellopack_settings_main, $save_api_settings;

    $doc = $(document);
    $hp_check_api = $doc.find('#hp_check_api');
    $report_box = $doc.find('#custom_errors_container');
    $save_api_settings = $doc.find('#save_api_settings');
    $hp_deactivate_api = $doc.find('#hp_deactivate_api');
    $hp_cleanup_settings = $doc.find('#hp_cleanup_settings');
    $hellopack_settings_main = $doc.find('#hellopack_settings_main');

    $report_box.on('click', 'button.notice-dismiss', function(e) {
        e.preventDefault();
        var $instance = $(this);

        $instance.closest('.is-dismissible').remove();
    });

    function showReport(payload, type) {
        type = type || 'success';
        var html = '',
            notice;

        notice = '<div class="notice notice-' + type + ' is-dismissible"><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
        if (type === 'success') {
            if ($.isArray(payload)) {
                payload.forEach(function(item) {
                    html += '<p>';
                    html += '<strong>' + item.label + ':</strong>' + "&nbsp;&nbsp;";
                    html += '<span>' + item.text + '</span>';
                    html += '</p>';
                });
            } else {
                html += '<p>' + payload + '</p>';
            }

            // notice = '<div class="notice notice-success is-dismissible"><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
            notice += html;
            notice += '</div>';

            return notice;
        }

        notice += '<p>' + payload + '</p>';
        notice += '</div>';

        return notice;

    }


    $save_api_settings.on('click', function(event) {
        event.preventDefault();
        var $form, $instance,
            $xhr, valid,
            $requiredInputs;

        $instance = $(this);
        $form = $instance.closest('form');
        valid = false;

        var api_key = $form.find('#api_key').val().trim();
        var product_id = $form.find('#product_id').val().trim();
        var nonce = $instance.data('nonce');

        $requiredInputs = $form.find('.hp_api_field');

        $requiredInputs.each(function($_, elem) {
            var elemValue = elem.value.trim();

            if (elemValue.length > 0) {
                valid = true;
                $(elem).removeClass('hp-error');
            } else {
                valid = false;
                $(elem).removeClass('hp-error').addClass('hp-error');
            }
        });

        if (api_key.length === 0 || product_id.length === 0) {
            $report_box.html(showReport('Az API kulcs megadása kötelező :)', 'error'));
            return false;
        }

        $instance.addClass('updating-message');
        $xhr = $.ajax(ajaxurl, {
            method: 'POST',
            data: {
                security: nonce,
                action: 'hp_activate_api',
                product_id: product_id,
                api_key: api_key,
            }
        });

        $xhr.done(function(response) {
            if (response.success) {
                $doc.trigger('hp.api_activated');
                $report_box.html(showReport(response.data.message));
            } else {
                $report_box.html(showReport(response.data.message, 'error'));
            }
        });

        $xhr.always(function() {
            $instance.removeClass('updating-message');
        });
    });

    // check api status
    $hp_check_api.on('click', function(evt) {
        evt.preventDefault();
        var nonce, action, xhr, $instance;
        $instance = $(this);
        nonce = $instance.data('nonce');
        action = $instance.data('action');

        $instance.addClass('updating-message');

        xhr = $.ajax(ajaxurl, {
            method: 'POST',
            data: {
                action: action,
                _ajax_nonce: nonce,
            }
        });

        xhr.done(function(response) {
            if (response.success) {
                var resp_data = response.data;
                var reportPayload = [
                    { label: wp.i18n.__('Status', 'hellopack'), text: resp_data.status },
                    { label: wp.i18n.__('Purchased Activations', 'hellopack'), text: resp_data.activations },
                    { label: wp.i18n.__('Total Activated', 'hellopack'), text: resp_data.used },
                    { label: wp.i18n.__('Remaining Activations', 'hellopack'), text: resp_data.remaining },
                    { label: wp.i18n.__('Activated?', 'hellopack'), text: resp_data.activated ? wp.i18n.__('Activated', 'hellopack') : wp.i18n.__('Deactivated', 'hellopack') },
                ];

                $report_box.html(showReport(reportPayload));
            } else {
                $report_box.html(showReport(response.data.error, 'error'));
            }
        });

        xhr.always(function() {
            $instance.removeClass('updating-message');
        })
    });

    // deactivate license
    $hp_deactivate_api.on('click', function(evt) {
        evt.preventDefault();
        var nonce, action, xhr, $instance;
        $instance = $(this);
        nonce = $instance.data('nonce');
        action = $instance.data('action');

        $instance.addClass('updating-message');

        xhr = $.ajax(ajaxurl, {
            method: 'POST',
            data: {
                action: action,
                _ajax_nonce: nonce,
            }
        });

        xhr.done(function(response) {
            if (response.success) {
                var resp_data = response.data;
                var reportPayload = [
                    { label: wp.i18n.__('Status', 'hellopack'), text: resp_data.status },
                    { label: wp.i18n.__('Purchased Activations', 'hellopack'), text: resp_data.activations },
                    { label: wp.i18n.__('Total Activated', 'hellopack'), text: resp_data.used },
                    { label: wp.i18n.__('Remaining Activations', 'hellopack'), text: resp_data.remaining },
                    { label: wp.i18n.__('Activated?', 'hellopack'), text: resp_data.activated ? wp.i18n.__('Activated', 'hellopack') : wp.i18n.__('Deactivated', 'hellopack') },
                ];

                $('#hp_api_status_text').html('Deactivated');
                $doc.trigger('hp.api_deactivated');

                $report_box.html(showReport(reportPayload));

            } else {
                $report_box.html(showReport(response.data.error, 'error'));
            }
        });

        xhr.always(function() {
            $instance.removeClass('updating-message');
        })
    });


    $doc.on('hp.api_deactivated', function(event) {
        $('#hp_api_status_text').html('Deactivated');
        location.reload();
        $hellopack_settings_main.find('.hp_api_field').val('');
        $hellopack_settings_main.find('.hp_mark_icon').removeClass('dashicons-yes').addClass('dashicons-no').css('color', '#ca336c');

        // disable button
        $hp_deactivate_api.prop('disabled', true);
        $hp_cleanup_settings.prop('disabled', true);
        $hp_check_api.prop('disabled', true);

        // enable submit button
        $save_api_settings.prop('disabled', false);
    });

    $doc.on('hp.api_activated', function(event) {

        $('#hp_api_status_text').html('Activated');
        location.reload();
        $hellopack_settings_main.find('.hp_mark_icon').removeClass('dashicons-no').addClass('dashicons-yes').css('color', '#66ab03');

        // enable buttons
        $hp_deactivate_api.prop('disabled', false);
        $hp_cleanup_settings.prop('disabled', false);
        $hp_check_api.prop('disabled', false);

        // disable submit button
        $save_api_settings.prop('disabled', true);
    });

    // clean up local settings
    $hp_cleanup_settings.on('click', function(evt) {
        evt.preventDefault();
        var nonce, action, xhr, $instance;
        $instance = $(this);
        nonce = $instance.data('nonce');
        action = $instance.data('action');

        $instance.addClass('updating-message');

        xhr = $.ajax(ajaxurl, {
            method: 'POST',
            data: {
                action: action,
                _ajax_nonce: nonce,
            }
        });

        xhr.done(function(response) {
            if (response.success) {
                var resp_data = response.data;

                $doc.trigger('hp.api_deactivated');
                // $('#hp_api_status_text').html('Deactivated');
                // $hellopack_settings_main.find('.hp_api_field').val('');
                // $hellopack_settings_main.find('.hp_mark_icon').removeClass('dashicons-yes').addClass('dashicons-no').css('color', '#ca336c');

                $report_box.html(showReport(resp_data.message));

            } else {
                $report_box.html(showReport(response.data.error, 'error'));
            }
        });

        xhr.always(function() {
            $instance.removeClass('updating-message');
        })
    });
});