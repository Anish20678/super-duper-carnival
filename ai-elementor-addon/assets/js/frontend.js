(function($){
    var STORAGE_PREFIX = 'AIElementorSession::';
    var DEFAULT_SESSION_TIMEOUT = (window.AIElementorAddon && parseInt(window.AIElementorAddon.timeout, 10)) || (7 * 60);

    function sendRequest(options) {
        var settings = $.extend({
            mode: 'chat',
            model: '',
            payload: '',
            message: '',
            sessionId: '',
            temperature: null,
            beforeSend: function(){},
            onSuccess: function(){},
            onError: function(){},
            afterSend: function(){}
        }, options || {});

        if (!window.AIElementorAddon || !AIElementorAddon.ajaxUrl) {
            settings.onError('AI endpoint not available.');
            return $.Deferred().reject('missing-endpoint').promise();
        }

        settings.beforeSend();

        return $.ajax({
            url: AIElementorAddon.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'ai_elementor_generate',
                nonce: AIElementorAddon.nonce,
                mode: settings.mode,
                model: settings.model,
                payload: settings.payload,
                session_id: settings.sessionId,
                message: settings.message,
                temperature: settings.temperature != null ? settings.temperature : ''
            }
        }).done(function(response){
            if (response && response.success && response.data) {
                var message = response.data.message || '';
                settings.onSuccess(message, response.data);
            } else {
                var message = response && response.data && response.data.message ? response.data.message : 'Unknown response from AI service.';
                settings.onError(message, response && response.data ? response.data : null);
            }
        }).fail(function(xhr){
            var data = xhr && xhr.responseJSON ? xhr.responseJSON.data : null;
            var message = data && data.message ? data.message : 'Request failed.';
            settings.onError(message, data);
        }).always(function(){
            settings.afterSend();
        });
    }

    function startSessionRequest(params) {
        if (!window.AIElementorAddon || !AIElementorAddon.ajaxUrl) {
            return $.Deferred().reject('missing-endpoint').promise();
        }

        return $.ajax({
            url: AIElementorAddon.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: $.extend({
                action: 'ai_elementor_start_session',
                nonce: AIElementorAddon.nonce
            }, params || {})
        });
    }

    function scrollToBottom($container, immediate) {
        var el = $container.get(0);
        if (!el) {
            return;
        }

        if (immediate) {
            el.scrollTop = el.scrollHeight;
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

    function appendAssistantMessage($window, message, opts) {
        var options = $.extend({ skipScroll: false, extraClass: '', isHtml: true }, opts || {});
        var content = options.isHtml ? message : formatAssistantMessage(message);
        var $bubble = createBubble('assistant', content, { extraClass: options.extraClass, isHtml: true });
        $window.append($bubble);

        if (!options.skipScroll) {
            scrollToBottom($window);
        }

        return $bubble;
    }

    function appendUserMessage($window, message, opts) {
        var options = $.extend({ skipScroll: false }, opts || {});
        var $bubble = createBubble('user', message, { isHtml: false });
        $window.append($bubble);

        if (!options.skipScroll) {
            scrollToBottom($window);
        }

        return $bubble;
    }

    function renderHistory($widget, $window, settings, history) {
        $window.empty();

        var displayHistory = Array.isArray(history) ? history : [];
        var normalised = [];

        if (!displayHistory.length && settings.welcomeMessage) {
            appendAssistantMessage($window, settings.welcomeMessage, { skipScroll: true, isHtml: false });
        }

        $.each(displayHistory, function(_, entry){
            if (!entry || !entry.role) {
                return;
            }

            if (entry.role === 'assistant') {
                appendAssistantMessage($window, entry.content, { skipScroll: true, isHtml: true });
                normalised.push({ role: 'assistant', content: normaliseMessage(entry.content) });
            } else if (entry.role === 'user') {
                appendUserMessage($window, entry.content, { skipScroll: true });
                normalised.push({ role: 'user', content: entry.content });
            }
        });

        $widget.data('history', normalised);
        scrollToBottom($window, true);
    }

    function getStoredSession(storageKey) {
        if (!storageKey) {
            return null;
        }

        try {
            return window.localStorage.getItem(storageKey);
        } catch (e) {
            return null;
        }
    }

    function storeSession(storageKey, sessionId) {
        if (!storageKey) {
            return;
        }

        try {
            if (sessionId) {
                window.localStorage.setItem(storageKey, sessionId);
            } else {
                window.localStorage.removeItem(storageKey);
            }
        } catch (e) {
            // Ignore storage failures.
        }
    }

    function initialiseChatWidget($widget) {
        if ($widget.data('aiChatInitialised')) {
            return;
        }

        var rawSettings = $widget.attr('data-settings') || '{}';
        var parsedSettings = {};

        try {
            parsedSettings = JSON.parse(rawSettings);
        } catch (e) {
            parsedSettings = {};
        }

        var settings = $.extend({
            model: '',
            temperature: 0.6,
            prompt: '',
            enterToSend: true,
            quickPrompts: [],
            autoSend: true,
            typingText: 'Assistant is thinking…',
            enableTypingIndicator: true,
            persistSession: true,
            emptyInputMessage: '',
            displayQuickPrompts: true,
            widgetId: '',
            welcomeMessage: '',
            sessionTimeout: DEFAULT_SESSION_TIMEOUT,
            sessionExpiredText: 'The chat session expired due to inactivity. Starting a new conversation.',
        }, parsedSettings || {});

        if (!Array.isArray(settings.quickPrompts)) {
            settings.quickPrompts = [];
        }

        var persistSession = settings.persistSession !== false;
        var storageKey = persistSession ? STORAGE_PREFIX + (settings.widgetId || $widget.closest('.elementor-element').data('id') || 'global') : null;
        var $window = $widget.find('.ai-chat-window');
        var $textarea = $widget.find('textarea');
        var $sendButton = $widget.find('.ai-chat-send');
        var currentSessionId = null;
        var inactivityTimer = null;
        var pendingSessionRequest = null;

        function resetInactivityTimer() {
            if (inactivityTimer) {
                clearTimeout(inactivityTimer);
            }

            var timeout = parseInt(settings.sessionTimeout, 10);
            if (!timeout) {
                return;
            }

            inactivityTimer = setTimeout(function(){
                handleSessionExpired(true);
            }, timeout * 1000);
        }

        function handleSessionExpired(autoMessage) {
            storeSession(storageKey, '');
            currentSessionId = null;
            $widget.data('history', []);
            if (autoMessage) {
                appendAssistantMessage($window, settings.sessionExpiredText, { isHtml: false });
            }
        }

        function applySessionResponse(data) {
            currentSessionId = data.sessionId;
            storeSession(storageKey, currentSessionId);
            renderHistory($widget, $window, settings, data.history || []);
            resetInactivityTimer();
        }

        function initialiseSession(existingId) {
            if (pendingSessionRequest) {
                return pendingSessionRequest;
            }

            pendingSessionRequest = startSessionRequest({
                session_id: existingId || '',
                widget_id: settings.widgetId || '',
                prompt_context: settings.prompt || '',
                page_url: window.location.href,
                referrer: document.referrer || ''
            }).done(function(response){
                if (response && response.success && response.data) {
                    applySessionResponse(response.data);
                } else {
                    currentSessionId = null;
                    $widget.data('history', []);
                    renderHistory($widget, $window, settings, []);
                }
            }).fail(function(){
                currentSessionId = null;
            }).always(function(){
                pendingSessionRequest = null;
            });

            return pendingSessionRequest;
        }

        function ensureSession() {
            if (currentSessionId) {
                return $.Deferred().resolve(currentSessionId).promise();
            }

            var stored = getStoredSession(storageKey);
            return initialiseSession(stored).then(function(){
                return currentSessionId;
            });
        }

        function pushHistory(role, content) {
            var history = $widget.data('history');
            if (!Array.isArray(history)) {
                history = [];
            }

            history.push({ role: role, content: content });
            $widget.data('history', history);
        }

        function sendChatMessage(message) {
            var deferred = $.Deferred();

            ensureSession().done(function(sessionId){
                var typingBubble;

                sendRequest({
                    mode: 'chat',
                    model: settings.model,
                    sessionId: sessionId,
                    message: message,
                    temperature: settings.temperature,
                    beforeSend: function(){
                        $sendButton.prop('disabled', true).addClass('is-loading');
                        if (settings.enableTypingIndicator) {
                            typingBubble = appendAssistantMessage($window, settings.typingText || 'Assistant is thinking…', { extraClass: 'is-typing', isHtml: false });
                        }
                        scrollToBottom($window);
                    },
                    onSuccess: function(responseMessage, data){
                        if (typingBubble) {
                            typingBubble.remove();
                        }

                        appendAssistantMessage($window, responseMessage, { isHtml: true });
                        pushHistory('assistant', normaliseMessage(responseMessage));
                        if (data && data.history) {
                            renderHistory($widget, $window, settings, data.history);
                        }
                        resetInactivityTimer();
                        deferred.resolve(responseMessage, data);
                    },
                    onError: function(errorMessage, data){
                        if (typingBubble) {
                            typingBubble.remove();
                        }

                        if (data && data.code === 'session_expired') {
                            appendAssistantMessage($window, errorMessage, { isHtml: false });
                            handleSessionExpired(false);
                        } else {
                            appendAssistantMessage($window, errorMessage || 'Request failed.', { isHtml: false });
                        }

                        deferred.reject(errorMessage, data);
                    },
                    afterSend: function(){
                        $sendButton.prop('disabled', false).removeClass('is-loading');
                        scrollToBottom($window);
                    }
                });
            }).fail(function(){
                appendAssistantMessage($window, 'Unable to connect to the AI assistant. Please try again shortly.', { isHtml: false });
                deferred.reject();
            });

            return deferred.promise();
        }

        function handleSend() {
            var message = ($textarea.val() || '').trim();

            if (!message) {
                if (settings.emptyInputMessage) {
                    appendAssistantMessage($window, settings.emptyInputMessage, { isHtml: false });
                }
                $textarea.focus();
                return;
            }

            if ($sendButton.data('api-key') !== 'set') {
                appendAssistantMessage($window, 'OpenAI API key is missing. Please add it in the plugin settings.', { isHtml: false });
                return;
            }

            appendUserMessage($window, message);
            pushHistory('user', message);
            $textarea.val('');
            resetInactivityTimer();

            sendChatMessage(message);
        }

        $widget.off('.aiChat');

        $widget.on('click.aiChat', '.ai-chat-send', function(){
            handleSend();
        });

        $widget.on('keydown.aiChat', '.ai-chat-input textarea', function(event){
            if (event.key !== 'Enter' || event.shiftKey) {
                return;
            }

            if (settings.enterToSend) {
                event.preventDefault();
                handleSend();
            }
        });

        $widget.on('click.aiChat', '.ai-chat-quick-prompt', function(){
            var text = $.trim($(this).text());
            if (!text) {
                return;
            }

            $textarea.val(text);

            if (settings.autoSend) {
                handleSend();
            } else {
                $textarea.focus();
            }
        });

        var storedSession = getStoredSession(storageKey);
        initialiseSession(storedSession).always(function(){
            if (!currentSessionId) {
                // Ensure a session exists even if initial request failed.
                initialiseSession('');
            }
        });

        $widget.data('aiChatInitialised', true);
    }

    $(function(){
        $('.ai-chat-widget').each(function(){
            initialiseChatWidget($(this));
        });
    });

    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        elementorFrontend.hooks.addAction('frontend/element_ready/ai_chat.default', function($scope){
            $scope.find('.ai-chat-widget').each(function(){
                initialiseChatWidget($(this));
            });
        });
    }

    // Brainstorm widget handler remains delegated for simplicity.
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
