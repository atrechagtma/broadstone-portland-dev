var wordkeeper = {};
wordkeeper.page = {};
wordkeeper.page.timeout = null;
wordkeeper.page.interval = null;
wordkeeper.action = {};
wordkeeper.action.proceed = false;
wordkeeper.action.trigger = null;

(function($) {
    'use strict';
    $(function() {
        wordkeeper.page.dialog = function(opts) {
            var div = document.createElement('div');

            var content = document.createElement('img');
            content.src = wordkeeper_waiting;

            var text = document.createElement('p');
            text.setAttribute('class', 'wordkeeper-swal-text');
            text.innerHTML = opts.title;

            div.appendChild(content)
            div.appendChild(text);

            swal({
                button: false,
                content: div,
                closeOnClickOutside: false,
                closeOnEsc: false
            });
        };

        wordkeeper.page.confirm = function(opts) {
            opts  = $.extend( true, {
                title     : 'Are you sure?',
                message   : '',
                modal     : true,
                okButton  : 'OK',
                noButton  : 'Cancel',
                callback  : $.noop,
                type: 'html',
                input: opts.email ? {element: 'input', attributes: {placeholder:'Comma separated email addresses for notification.'}} : null
            }, opts || {} );

            swal({
                title: opts.title,
                text: opts.message,
                icon: 'warning',
                content: opts.input,
                buttons: {
                    cancel: {
                        text:  opts.noButton,
                        visible: true
                    },
                    confirm: {
                        text: opts.okButton
                    }
                }
            }).then(function(proceed) {
                if (proceed != null) {
                    wordkeeper.action.process({
                        title: wordkeeper.action.trigger.data('waiting'),
                        action: wordkeeper.action.trigger.data('action'),
                        formdata: wordkeeper.action.trigger.parents('form').serializeArray(),
                        emails: opts.email ? proceed : null
                    });
                }
            });
        }

        wordkeeper.action.timeout = function(){
            swal.close()
            wordkeeper.action.clear();
            clearInterval(wordkeeper.page.interval);
        };

        wordkeeper.action.process = function(opts) {
            wordkeeper.page.dialog({
                title: opts.title
            });

            var ajaxdata = {
                'action': opts.action,
                'wp_nonce': wordkeeper_nonce
            };

            $.each(opts.formdata, function(index, obj){
                ajaxdata[obj.name] = obj.value;
            });

            if(null != opts.emails){
                ajaxdata['emails'] = opts.emails;
            }

            $.post(ajaxurl, ajaxdata, wordkeeper.action.response, "json");
        }

        wordkeeper.action.close = function() {
            swal.close();
        };

        wordkeeper.action.status = function(){
            $.get('/wordkeeper/status', function(data){
                if(data.status != 'Pending') {
                    if(data.status == 'OK') {

                        swal({
                            text: data.message,
                            button: false,
                            icon: 'success'
                        });

                        setTimeout(wordkeeper.action.close, 1400);
                        clearInterval(wordkeeper.page.interval);
                        clearTimeout(wordkeeper.page.timeout);
                    }
                    else if(data.status == 'Error') {

                        swal({
                            text: data.message,
                            button: false,
                            icon: 'error'
                        });

                        setTimeout(wordkeeper.action.close, 1400);
                        clearInterval(wordkeeper.page.interval);
                        clearTimeout(wordkeeper.page.timeout);
                    }

                    wordkeeper.action.clear();
                }
            }, "json");
        };

        wordkeeper.action.clear = function(){
            $.get('/wordkeeper/status/clear');
        };

        wordkeeper.action.response = function(data) {
            if(data.status == 'OK') {
                wordkeeper_nonce = data.nonce;

                if(data.response != 'Success') {

                    swal({
                        text: data.message,
                        button: false,
                        icon: 'error'
                    });

                    setTimeout(wordkeeper.action.close, 1400);
                    wordkeeper.action.clear();
                    return;
                }
				else if(data.response == 'Success'){
					swal({
						text: data.message,
						button: false,
						icon: 'success'
					});

					setTimeout(wordkeeper.action.close, 1400);
					clearInterval(wordkeeper.page.interval);
					clearTimeout(wordkeeper.page.timeout);
				}
            }
        };

        $(".wordkeeper input[type=button]").click(function() {
            var button = $(this);
            wordkeeper.action.trigger = button;

            if(button.is('[data-type=confirm]')) {
                wordkeeper.page.confirm({
                    title: button.data('confirmation-title'),
                    message: button.data('confirmation-message'),
                    callback: wordkeeper.action.proceed,
                    email: button.data('email') != undefined ? true : false
                });
            }
            else {
				var data = button.parents('form').serializeArray();
				
				if(button.attr('id') == 'save-settings'){
					data = $('#settings-form, #bots-form').serializeArray();
				}
                wordkeeper.action.process({
                    title: button.data('waiting'),
                    action: button.data('action'),
                    formdata: data
                });
            }
        });
    });
})(jQuery);
