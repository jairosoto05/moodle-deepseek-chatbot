<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Spanish language strings for OpenAI ChatBot Block
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Plugin info
$string['pluginname'] = 'ChatBot OpenAI';
$string['openai_chatbot'] = 'ChatBot OpenAI';
$string['openai_chatbot:addinstance'] = 'Agregar un nuevo bloque ChatBot OpenAI';
$string['openai_chatbot:myaddinstance'] = 'Agregar un nuevo bloque ChatBot OpenAI al Panel';

// Block interface
$string['chatbot_title'] = 'Tutor IA';
$string['welcome_message'] = '💡 Haz una pregunta para empezar a chatear con el tutor de IA';
$string['input_placeholder'] = 'Haz tu pregunta...';
$string['ask_button'] = 'Preguntar';
$string['response_header'] = 'Respuesta:';
$string['error_prefix'] = 'Error:';

// Configuration
$string['config_missing'] = 'Falta la configuración de la API de OpenAI. Por favor contacta a tu administrador.';

// JavaScript strings
$string['js_thinking'] = 'Pensando...';
$string['js_writing'] = 'Escribiendo...';
$string['js_assistant_thinking'] = 'El asistente está pensando';
$string['js_error_occurred'] = '¡Ocurrió un error!';
$string['js_try_again'] = 'Inténtalo de nuevo';
$string['js_writing_question'] = 'Escribiendo pregunta';

// Settings
$string['settings_general'] = 'Configuración General';
$string['settings_general_desc'] = 'Configura los ajustes de integración del ChatBot OpenAI.';
$string['settings_bot_name'] = 'Nombre del Bot';
$string['settings_bot_name_desc'] = 'Personaliza el nombre de tu chatbot como aparece a los usuarios.';
$string['settings_apikey'] = 'Clave API de OpenAI';
$string['settings_apikey_desc'] = 'Introduce tu clave API de OpenAI. Puedes obtener una en https://platform.openai.com/';
$string['settings_assistantid'] = 'ID del Asistente';
$string['settings_assistantid_desc'] = 'Introduce el ID de tu Asistente de OpenAI. Crea uno en https://platform.openai.com/assistants';
$string['settings_course_context'] = 'Habilitar Contexto del Curso';
$string['settings_course_context_desc'] = 'Cuando está habilitado, el chatbot recibirá información sobre el curso actual para proporcionar respuestas más relevantes.';
$string['settings_max_response_time'] = 'Tiempo Máximo de Respuesta';
$string['settings_max_response_time_desc'] = 'Tiempo máximo en segundos para esperar la respuesta de OpenAI (predeterminado: 30)';

// Privacy
$string['privacy:metadata'] = 'El bloque OpenAI ChatBot no almacena datos personales localmente. Sin embargo, las preguntas de los usuarios se envían a los servidores de OpenAI para su procesamiento.';
$string['privacy:metadata:openai'] = 'Las preguntas de los usuarios se envían a OpenAI para su procesamiento';
$string['privacy:metadata:openai:question'] = 'La pregunta hecha por el usuario';
$string['privacy:metadata:openai:course_context'] = 'Información del curso para proporcionar contexto a las respuestas';

// Additional strings
$string['no_config'] = 'Falta la configuración de la API de OpenAI. Por favor contacta a tu administrador.';
$string['no_answer'] = '[Sin respuesta]';
$string['timeout_error'] = 'La ejecución no terminó a tiempo. Estado: {$a}';
$string['create_thread_error'] = 'Error al crear thread. Respuesta de API: {$a}';
$string['create_run_error'] = 'Error al crear run. Respuesta de API: {$a}';
$string['openai_api_error'] = 'Error de API de OpenAI: {$a}';
$string['network_error'] = 'Ocurrió un error de red. Por favor inténtalo más tarde.';

// Block capabilities
$string['openai_chatbot:view'] = 'Ver bloque ChatBot OpenAI';

// Error validation messages
$string['invalid_context'] = 'Contexto inválido';
$string['invalid_block_instance'] = 'Instancia de bloque inválida';
$string['question_empty'] = 'La pregunta no puede estar vacía';
$string['error_message'] = 'Error: {$a}';
$string['strong_error'] = 'Error:';

// JavaScript fallback strings
$string['js_writing_fallback'] = 'Escribiendo...';
$string['js_thinking_fallback'] = 'Pensando...';
$string['js_assistant_thinking_fallback'] = 'El asistente está pensando';
$string['js_error_occurred_fallback'] = 'Ocurrió un error';
$string['js_network_error'] = 'Ocurrió un error de red';
$string['js_ask_button_fallback'] = 'Preguntar';
$string['js_writing_question_fallback'] = 'Escribiendo pregunta';