(function($){
    function sendRequest(options) {
        var settings = $.extend({
            mode: 'chat',
            model: '',
            payload: '',
            temperature: null,
            beforeSend: function(){},
            onSuccess: function(){},
            onError: function(){},
            afterSend: function(){}
        }, options || {});

        if (!window.AIElementorAddon || !AIElementorAddon.ajaxUrl) {
            settings.onError('AI endpoint not available.');
            return;
        }

        settings.beforeSend();

        $.ajax({
            url: AIElementorAddon.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'ai_elementor_generate',
                nonce: AIElementorAddon.nonce,
                mode: settings.mode,
                model: settings.model,
                payload: settings.payload,
                temperature: settings.temperature != null ? settings.temperature : ''
            }
        }).done(function(response){
            if (response && response.success && response.data && response.data.message) {
                settings.onSuccess(response.data.message);
            } else {
                var message = response && response.data && response.data.message ? response.data.message : 'Unknown response from AI service.';
                settings.onError(message);
            }
        }).fail(function(xhr){
            var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'Request failed.';
            settings.onError(message);
        }).always(function(){
            settings.afterSend();
        });
    }

    function scrollToBottom($container) {
        var el = $container.get(0);
        if (!el) {
            return;
        }

        $container.stop().animate({ scrollTop: el.scrollHeight }, 200);
    }

    function createBubble(type, content, opts) {
        var options = opts || {};
        var classes = 'ai-chat-bubble ai-chat-bubble--' + type;

        if (options.extraClass) {
            classes += ' ' + options.extraClass;
        }

        var $bubble = $('<div/>', { 'class': classes });

        if (options.isHtml) {
            $bubble.html(content);
        } else {
            $bubble.text(content);
        }

        return $bubble;
    }

    function formatAssistantMessage(message) {
        if (!message) {
            return '';
        }

        if (/<[a-z][\s\S]*>/i.test(message)) {
            return message;
        }

        return String(message).replace(/\n/g, '<br>');
    }

    function normaliseMessage(message) {
        return $('<div/>').html(formatAssistantMessage(message)).text();
    }

    function ensureHistory($widget) {
        var history = $widget.data('history');
        if (!Array.isArray(history)) {
            history = [];
            $widget.data('history', history);
        }

        return history;
    }

    function buildChatPayload(history, prompt) {
        var lines = [];

        if (prompt) {
            lines.push('System: ' + prompt);
        }

        $.each(history, function(_, entry){
            if (!entry || !entry.role || !entry.content) {
                return;
            }

            var role = entry.role.charAt(0).toUpperCase() + entry.role.slice(1);
            lines.push(role + ': ' + entry.content);
        });

        return lines.join('\n');
    }

    function appendAssistantMessage($window, message) {
        var formatted = formatAssistantMessage(message);
        $window.append(createBubble('assistant', formatted, { isHtml: true }));
        scrollToBottom($window);
    }

    function appendUserMessage($window, message) {
        $window.append(createBubble('user', message, { isHtml: false }));
        scrollToBottom($window);
    }

    $(document).on('click', '.ai-chat-send', function(){
        var $button = $(this);
        var $widget = $button.closest('.ai-chat-widget');
        var $textarea = $widget.find('textarea');
        var $window = $widget.find('.ai-chat-window');
        var settings = $widget.data('settings') || {};
        var message = ($textarea.val() || '').trim();

        if (!message) {
            $textarea.focus();
            return;
        }

        if ($button.data('api-key') !== 'set') {
            appendAssistantMessage($window, 'OpenAI API key is missing. Please add it in the plugin settings.');
            return;
        }

        var history = ensureHistory($widget);

        appendUserMessage($window, message);
        history.push({ role: 'user', content: message });
        $textarea.val('');

        var typingBubble;

        sendRequest({
            mode: 'chat',
            model: settings.model,
            payload: buildChatPayload(history, settings.prompt),
            temperature: settings.temperature,
            beforeSend: function(){
                $button.prop('disabled', true).addClass('is-loading');
                var typingText = settings.typingText || 'Assistant is thinking…';
                typingBubble = createBubble('assistant', typingText, { extraClass: 'is-typing', isHtml: false });
                $window.append(typingBubble);
                scrollToBottom($window);
            },
            onSuccess: function(responseMessage){
                if (typingBubble) {
                    typingBubble.remove();
                }

                appendAssistantMessage($window, responseMessage);
                history.push({ role: 'assistant', content: normaliseMessage(responseMessage) });
            },
            onError: function(errorMessage){
                if (typingBubble) {
                    typingBubble.remove();
                }

                appendAssistantMessage($window, errorMessage || 'Request failed.');
            },
            afterSend: function(){
                $button.prop('disabled', false).removeClass('is-loading');
                scrollToBottom($window);
            }
        });
    });

    $(document).on('keydown', '.ai-chat-input textarea', function(event){
        if (event.key !== 'Enter' || event.shiftKey) {
            return;
        }

        var $widget = $(this).closest('.ai-chat-widget');
        var settings = $widget.data('settings') || {};

        if (settings.enterToSend) {
            event.preventDefault();
            $widget.find('.ai-chat-send').trigger('click');
        }
    });

    $(document).on('click', '.ai-chat-quick-prompt', function(){
        var $chip = $(this);
        var $widget = $chip.closest('.ai-chat-widget');
        var settings = $widget.data('settings') || {};
        var text = $.trim($chip.text());
        var $textarea = $widget.find('textarea');

        if (!text) {
            return;
        }

        $textarea.val(text);

        if (settings.autoSend) {
            $widget.find('.ai-chat-send').trigger('click');
        } else {
            $textarea.focus();
        }
    });

    $(document).on('click', '.ai-brainstorm-generate', function(){
        var $button = $(this);
        var $widget = $button.closest('.ai-brainstorm-widget');
        var topic = ($widget.find('input[type="text"]').val() || '').trim();
        var settings = $widget.data('settings') || {};
        var $output = $widget.find('.ai-brainstorm-output');

        if (!topic) {
            $output.text('Please provide a topic to brainstorm.');
            return;
        }

        if ($button.data('api-key') !== 'set') {
            $output.text('OpenAI API key is missing. Please add it in the plugin settings.');
            return;
        }

        var payload = settings.template ? settings.template.replace('{{topic}}', topic) : topic;

        sendRequest({
            mode: 'brainstorm',
            model: settings.model,
            payload: payload,
            beforeSend: function(){
                $button.prop('disabled', true).addClass('is-loading');
                $output.text('Generating ideas…');
            },
            onSuccess: function(message){
                $output.html(formatAssistantMessage(message));
            },
            onError: function(errorMessage){
                $output.text(errorMessage);
            },
            afterSend: function(){
                $button.prop('disabled', false).removeClass('is-loading');
            }
        });
    });

    $(document).on('click', '.ai-image-prompt-generate', function(){
        var $button = $(this);
        var $widget = $button.closest('.ai-image-prompt-widget');
        var base = ($widget.find('.ai-image-prompt-base').val() || '').trim();
        var settings = $widget.data('settings') || {};
        var $output = $widget.find('.ai-image-prompt-output');

        if (!base) {
            $output.text('Describe your base scene first.');
            return;
        }

        if ($button.data('api-key') !== 'set') {
            $output.text('OpenAI API key is missing. Please add it in the plugin settings.');
            return;
        }

        var attributes = [];
        $widget.find('.ai-image-prompt-attributes input').each(function(){
            var $field = $(this);
            var label = $field.prev('label').text();
            var value = ($field.val() || '').trim();
            if (value) {
                attributes.push(label + ': ' + value);
            }
        });

        var payload = base;
        if (attributes.length) {
            payload += '\nAttributes:\n- ' + attributes.join('\n- ');
        }

        sendRequest({
            mode: 'image_prompt',
            model: settings.model,
            payload: payload,
            beforeSend: function(){
                $button.prop('disabled', true).addClass('is-loading');
                $output.text('Crafting a descriptive prompt…');
            },
            onSuccess: function(message){
                $output.html(formatAssistantMessage(message));
            },
            onError: function(errorMessage){
                $output.text(errorMessage);
            },
            afterSend: function(){
                $button.prop('disabled', false).removeClass('is-loading');
            }
        });
    });
})(jQuery);
