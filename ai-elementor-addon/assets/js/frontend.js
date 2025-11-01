(function($){
    function sendRequest(mode, model, payload, $output, $button) {
        if (!window.AIElementorAddon || !AIElementorAddon.ajaxUrl) {
            $output.text('AI endpoint not available.');
            return;
        }

        $button.prop('disabled', true).addClass('is-loading');
        $output.text('Connecting to AI assistant…');

        $.ajax({
            url: AIElementorAddon.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'ai_elementor_generate',
                nonce: AIElementorAddon.nonce,
                mode: mode,
                model: model,
                payload: payload
            }
        }).done(function(response){
            if (response && response.success) {
                $output.html(response.data.message.replace(/\n/g, '<br>'));
            } else if (response && response.data && response.data.message) {
                $output.text(response.data.message);
            } else {
                $output.text('Unknown response from AI service.');
            }
        }).fail(function(xhr){
            $output.text(xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'Request failed.');
        }).always(function(){
            $button.prop('disabled', false).removeClass('is-loading');
        });
    }

    $(document).on('click', '.ai-chat-send', function(){
        var $button = $(this);
        var $widget = $button.closest('.ai-chat-widget');
        var $textarea = $widget.find('textarea');
        var $window = $widget.find('.ai-chat-window');
        var settings = $widget.data('settings') || {};
        var message = $textarea.val();

        if (!message) {
            $textarea.focus();
            return;
        }

        var bubble = $('<div/>', {
            'class': 'ai-chat-bubble ai-chat-bubble--user',
            'text': message
        });
        $window.append(bubble);
        $textarea.val('');

        var payload = settings.prompt ? settings.prompt + '\nUser: ' + message : message;
        sendRequest('chat', settings.model, payload, $window, $button);
    });

    $(document).on('click', '.ai-brainstorm-generate', function(){
        var $button = $(this);
        var $widget = $button.closest('.ai-brainstorm-widget');
        var topic = $widget.find('input[type="text"]').val();
        var settings = $widget.data('settings') || {};
        var $output = $widget.find('.ai-brainstorm-output');

        if (!topic) {
            $output.text('Please provide a topic to brainstorm.');
            return;
        }

        var payload = settings.template ? settings.template.replace('{{topic}}', topic) : topic;
        sendRequest('brainstorm', settings.model, payload, $output, $button);
    });

    $(document).on('click', '.ai-image-prompt-generate', function(){
        var $button = $(this);
        var $widget = $button.closest('.ai-image-prompt-widget');
        var base = $widget.find('.ai-image-prompt-base').val();
        var settings = $widget.data('settings') || {};
        var $output = $widget.find('.ai-image-prompt-output');

        if (!base) {
            $output.text('Describe your base scene first.');
            return;
        }

        var attributes = [];
        $widget.find('.ai-image-prompt-attributes input').each(function(){
            var label = $(this).prev('label').text();
            var value = $(this).val();
            if (value) {
                attributes.push(label + ': ' + value);
            }
        });

        var payload = base + '\nAttributes:\n- ' + attributes.join('\n- ');
        sendRequest('image_prompt', settings.model, payload, $output, $button);
    });
})(jQuery);
