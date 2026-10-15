// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * OpenAI ChatBot JavaScript module
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str'], function($, Ajax, Str) {
    'use strict';

    /**
     * Initialize the chatbot functionality
     */
    var init = function() {
        // Find all chatbot containers on the page
        $('.chatbot-container').each(function() {
            var container = $(this);
            var contextId = container.data('contextid');
            var formDiv = container.find('[id^="chatbot_form_"]');
            var instanceId = formDiv.attr('id').replace('chatbot_form_', '');
            var responseDiv = $('#chatbot_response_' + instanceId);
            var questionInput = $('#chatbot_question_' + instanceId);
            var submitButton = $('#chatbot_button_' + instanceId);

            // Handle button click
            submitButton.on('click', function(e) {
                e.preventDefault();
                handleQuestion();
            });

            // Handle Enter key press
            questionInput.on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    handleQuestion();
                }
            });

            function handleQuestion() {
                var question = questionInput.val().trim();
                if (!question) {
                    questionInput.focus();
                    return;
                }

                // FEEDBACK INMEDIATO - Mostrar "Escribiendo..." inmediatamente
                Str.get_string('js_writing', 'block_openai_chatbot').then(function(writingText) {
                    submitButton.text(writingText);
                }).catch(function() {
                    // Fallback to localized string
                    Str.get_string('js_writing_fallback', 'block_openai_chatbot').then(function(fallbackText) {
                        submitButton.text(fallbackText);
                    }).catch(function() {
                        submitButton.text('Escribiendo...');
                    });
                });
                
                questionInput.prop('disabled', true);
                submitButton.prop('disabled', true);
                
                // Mostrar pregunta y estado inicial inmediatamente
                var initialHtml = '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>';
                Str.get_string('js_writing_question', 'block_openai_chatbot').then(function(writingQuestion) {
                    initialHtml += '<div class="chatbot-loading">' +
                        '✍️ ' + writingQuestion +
                        '<span class="chatbot-dots">.</span>' +
                        '<span class="chatbot-dots">.</span>' +
                        '<span class="chatbot-dots">.</span>' +
                        '</div>';
                    responseDiv.html(initialHtml);
                }).catch(function() {
                    // Fallback to localized string
                    Str.get_string('js_writing_question_fallback', 'block_openai_chatbot').then(function(fallbackText) {
                        initialHtml += '<div class="chatbot-loading">' +
                            '✍️ ' + fallbackText +
                            '<span class="chatbot-dots">.</span>' +
                            '<span class="chatbot-dots">.</span>' +
                            '<span class="chatbot-dots">.</span>' +
                            '</div>';
                        responseDiv.html(initialHtml);
                    }).catch(function() {
                        initialHtml += '<div class="chatbot-loading">' +
                            '✍️ Escribiendo pregunta' +
                            '<span class="chatbot-dots">.</span>' +
                            '<span class="chatbot-dots">.</span>' +
                            '<span class="chatbot-dots">.</span>' +
                            '</div>';
                        responseDiv.html(initialHtml);
                    });
                });

                // Cambiar a "Pensando..." después de un breve momento
                setTimeout(function() {
                    Str.get_string('js_thinking', 'block_openai_chatbot').then(function(thinkingText) {
                        submitButton.text(thinkingText);
                    }).catch(function() {
                        // Fallback to localized string
                        Str.get_string('js_thinking_fallback', 'block_openai_chatbot').then(function(fallbackText) {
                            submitButton.text(fallbackText);
                        }).catch(function() {
                            submitButton.text('Pensando...');
                        });
                    });
                    
                    Str.get_string('js_assistant_thinking', 'block_openai_chatbot').then(function(assistantThinking) {
                        var thinkingHtml = '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>';
                        thinkingHtml += '<div class="chatbot-loading">' +
                            '🤖 ' + assistantThinking +
                            '<span class="chatbot-dots">.</span>' +
                            '<span class="chatbot-dots">.</span>' +
                            '<span class="chatbot-dots">.</span>' +
                            '</div>';
                        responseDiv.html(thinkingHtml);
                    }).catch(function() {
                        // Fallback to localized string
                        Str.get_string('js_assistant_thinking_fallback', 'block_openai_chatbot').then(function(fallbackText) {
                            var thinkingHtml = '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>';
                            thinkingHtml += '<div class="chatbot-loading">' +
                                '🤖 ' + fallbackText +
                                '<span class="chatbot-dots">.</span>' +
                                '<span class="chatbot-dots">.</span>' +
                                '<span class="chatbot-dots">.</span>' +
                                '</div>';
                            responseDiv.html(thinkingHtml);
                        }).catch(function() {
                            var thinkingHtml = '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>';
                            thinkingHtml += '<div class="chatbot-loading">' +
                                '🤖 El asistente está pensando' +
                                '<span class="chatbot-dots">.</span>' +
                                '<span class="chatbot-dots">.</span>' +
                                '<span class="chatbot-dots">.</span>' +
                                '</div>';
                            responseDiv.html(thinkingHtml);
                        });
                    });
                }, 800);

                // Call AJAX service
                var request = {
                    methodname: 'block_openai_chatbot_ask_question',
                    args: {
                        question: question,
                        blockinstanceid: parseInt(instanceId),
                        contextid: contextId
                    }
                };

                Ajax.call([request])[0].done(function(response) {
                    if (response.success) {
                        // Show the question and answer
                        var resultHtml = '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>';
                        resultHtml += response.html;
                        responseDiv.html(resultHtml);
                    } else {
                        var errorHtml = '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>';
                        Str.get_string('js_error_occurred', 'block_openai_chatbot').then(function(errorText) {
                            errorHtml += '<div class="alert alert-danger">' + errorText + ' ' + response.message + '</div>';
                            responseDiv.html(errorHtml);
                        }).catch(function() {
                            // Fallback to localized string
                            Str.get_string('js_error_occurred_fallback', 'block_openai_chatbot').then(function(fallbackText) {
                                errorHtml += '<div class="alert alert-danger">' + fallbackText + ': ' + response.message + '</div>';
                                responseDiv.html(errorHtml);
                            }).catch(function() {
                                errorHtml += '<div class="alert alert-danger">An error occurred: ' + response.message + '</div>';
                                responseDiv.html(errorHtml);
                            });
                        });
                    }
                }).fail(function(error) {
                    var errorHtml = '<div class="chatbot-question">📝 ' + escapeHtml(question) + '</div>';
                    Str.get_string('js_error_occurred', 'block_openai_chatbot').then(function(errorText) {
                        errorHtml += '<div class="alert alert-danger">' + errorText + '</div>';
                        responseDiv.html(errorHtml);
                    }).catch(function() {
                        // Fallback to localized string
                        Str.get_string('js_network_error', 'block_openai_chatbot').then(function(fallbackText) {
                            errorHtml += '<div class="alert alert-danger">' + fallbackText + '</div>';
                            responseDiv.html(errorHtml);
                        }).catch(function() {
                            errorHtml += '<div class="alert alert-danger">Network error occurred</div>';
                            responseDiv.html(errorHtml);
                        });
                    });
                }).always(function() {
                    // Re-enable form
                    questionInput.prop('disabled', false);
                    submitButton.prop('disabled', false);
                    
                    // Restore button text
                    var originalText = submitButton.data('original-text');
                    if (originalText) {
                        submitButton.text(originalText);
                    } else {
                        Str.get_string('ask_button', 'block_openai_chatbot').then(function(askText) {
                            submitButton.text(askText);
                        }).catch(function() {
                            // Fallback to localized string
                            Str.get_string('js_ask_button_fallback', 'block_openai_chatbot').then(function(fallbackText) {
                                submitButton.text(fallbackText);
                            }).catch(function() {
                                submitButton.text('Preguntar');
                            });
                        });
                    }
                    
                    // Clear the input and focus
                    questionInput.val('').focus();
                });
            }
        });
    };

    /**
     * Escape HTML to prevent XSS
     * @param {string} text Text to escape
     * @return {string} Escaped text
     */
    var escapeHtml = function(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    };

    return {
        init: init
    };
});