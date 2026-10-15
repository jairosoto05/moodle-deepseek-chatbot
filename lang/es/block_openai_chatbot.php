<?php
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
//
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// but WITHOUT ANY WARRANTY; without even the implied warranty of
//
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Spanish language strings for the ChatBot Block (DeepSeek provider).
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['api_error_auth'] = 'La clave API de DeepSeek no es válida. Pide a un administrador que la revise en la configuración del bloque ChatBot.';
$string['api_error_balance'] = 'La cuenta de DeepSeek no tiene saldo disponible. Pide a un administrador que la recargue.';
$string['api_error_emptyresponse'] = 'DeepSeek devolvió una respuesta vacía. Intenta formular la pregunta de otra manera.';
$string['api_error_http'] = 'DeepSeek devolvió un error HTTP inesperado. Inténtalo de nuevo más tarde.';
$string['api_error_invalidjson'] = 'DeepSeek devolvió una respuesta que no se pudo procesar. Inténtalo de nuevo más tarde.';
$string['api_error_invalidrequest'] = 'DeepSeek rechazó la solicitud. Pide a un administrador que revise la configuración del bloque ChatBot.';
$string['api_error_network'] = 'No se pudo conectar con la API de DeepSeek. Comprueba la conexión de red e inténtalo de nuevo.';
$string['api_error_ratelimit'] = 'La API de DeepSeek está recibiendo demasiadas solicitudes. Espera un momento e inténtalo de nuevo.';
$string['api_error_server'] = 'El servicio de DeepSeek no está disponible temporalmente. Inténtalo de nuevo en unos momentos.';
$string['api_error_timeout'] = 'DeepSeek tardó demasiado en responder. Inténtalo de nuevo o pide a un administrador que aumente el tiempo máximo de respuesta.';
$string['api_error_unexpected'] = 'Ocurrió un error inesperado al contactar con DeepSeek. Inténtalo de nuevo más tarde.';
$string['apikey_missing'] = 'La clave API de DeepSeek no está configurada. Pide a un administrador que la añada en la configuración del bloque ChatBot.';
$string['ask_button'] = 'Preguntar';
$string['chatbot_title'] = 'Tutor IA';
$string['config_missing'] = 'La API de DeepSeek todavía no está configurada. Por favor contacta a tu administrador.';
$string['course_context_description'] = 'Descripción del curso';
$string['course_context_due'] = 'vence';
$string['course_context_general'] = 'Contexto general de Moodle.';
$string['course_context_indays'] = 'en {$a} días';
$string['course_context_instruction'] = 'Proporciona respuestas relacionadas específicamente con el contenido de este curso. Si la pregunta no está relacionada con este curso, indica amablemente que solo puedes ayudar con temas del curso. Cuando el alumnado pregunte por el curso, usa la estructura del curso indicada arriba y no inventes secciones, actividades ni plazos que no aparezcan; si algo no aparece, dilo.';
$string['course_context_name'] = 'Contexto del curso:';
$string['course_context_noactivities'] = '(sin actividades)';
$string['course_context_overdue'] = 'vencido';
$string['course_context_structure'] = 'Estructura del curso (solo lo que este usuario puede ver):';
$string['course_context_thisweek'] = 'esta semana';
$string['course_context_title'] = 'Nombre del curso';
$string['course_context_today'] = 'Fecha actual';
$string['course_context_truncated'] = '(Estructura del curso truncada a {$a->sections} secciones y {$a->activities} actividades.)';
$string['endpoint_label'] = 'Punto de acceso';
$string['error_admin_hint'] = '(Consulta la configuración del bloque ChatBot; los detalles técnicos están en el registro de depuración.)';
$string['error_message'] = 'Error: {$a}';
$string['error_prefix'] = 'Error:';
$string['input_placeholder'] = 'Haz tu pregunta...';
$string['invalid_block_instance'] = 'Instancia de bloque inválida';
$string['invalid_context'] = 'Contexto inválido';
$string['js_ask_button_fallback'] = 'Preguntar';
$string['js_assistant_thinking'] = 'El asistente está pensando';
$string['js_assistant_thinking_fallback'] = 'El asistente está pensando';
$string['js_error_occurred'] = '¡Ocurrió un error!';
$string['js_error_occurred_fallback'] = 'Ocurrió un error';
$string['js_network_error'] = 'Ocurrió un error de red';
$string['js_thinking'] = 'Pensando...';
$string['js_thinking_fallback'] = 'Pensando...';
$string['js_try_again'] = 'Inténtalo de nuevo';
$string['js_writing'] = 'Escribiendo...';
$string['js_writing_fallback'] = 'Escribiendo...';
$string['js_writing_question'] = 'Escribiendo pregunta';
$string['js_writing_question_fallback'] = 'Escribiendo pregunta';
$string['model_deepseek_flash'] = 'deepseek-flash (rápido, uso general)';
$string['model_deepseek_v4_pro'] = 'deepseek-v4-pro (el más capaz)';
$string['openai_chatbot'] = 'ChatBot (DeepSeek)';
$string['openai_chatbot:addinstance'] = 'Agregar un nuevo bloque ChatBot';
$string['openai_chatbot:myaddinstance'] = 'Agregar un nuevo bloque ChatBot al Panel';
$string['openai_chatbot:view'] = 'Ver el bloque ChatBot';
$string['pluginname'] = 'ChatBot (DeepSeek)';

$string['privacy:metadata'] = 'El bloque ChatBot no almacena datos personales localmente. Sin embargo, las preguntas de los usuarios se envían a los servidores de DeepSeek para su procesamiento.';
$string['privacy:metadata:deepseek'] = 'Las preguntas de los usuarios se envían a DeepSeek para su procesamiento';
$string['privacy:metadata:deepseek:course_context'] = 'Información del curso para proporcionar contexto a las respuestas';
$string['privacy:metadata:deepseek:course_deadlines'] = 'Las fechas y plazos de las actividades del curso visibles para el usuario';
$string['privacy:metadata:deepseek:course_structure'] = 'Las secciones del curso y las actividades visibles para el usuario';
$string['privacy:metadata:deepseek:question'] = 'La pregunta hecha por el usuario';
$string['question_empty'] = 'La pregunta no puede estar vacía';
$string['response_header'] = 'Respuesta:';
$string['response_success'] = 'Respuesta generada correctamente';
$string['settings_apikey'] = 'Clave API de DeepSeek';
$string['settings_apikey_desc'] = 'Introduce tu clave API de DeepSeek. Puedes crear una en https://platform.deepseek.com/. La clave se guarda en la configuración de Moodle y nunca se muestra a los usuarios.';
$string['settings_behaviour'] = 'Comportamiento del asistente';
$string['settings_behaviour_desc'] = 'Instrucciones y contexto que se envían al modelo con cada pregunta.';
$string['settings_bot_name'] = 'Nombre del bot';
$string['settings_bot_name_desc'] = 'Personaliza el nombre de tu chatbot como aparece a los usuarios.';
$string['settings_course_contents'] = 'Incluir la estructura del curso';
$string['settings_course_contents_dates'] = 'Incluir fechas y plazos';
$string['settings_course_contents_dates_desc'] = 'Incluye la fecha actual y los plazos de las actividades (fechas de entrega de tareas, cierre de cuestionarios, etc.).';
$string['settings_course_contents_desc'] = 'Envía las secciones del curso y las actividades que el usuario puede ver, para que el asistente pueda responder preguntas sobre qué hacer en el curso. Aumenta el número de tokens de entrada de cada solicitud.';
$string['settings_course_contents_max_activities'] = 'Máximo de actividades';
$string['settings_course_contents_max_activities_desc'] = 'Número máximo de actividades incluidas en la solicitud.';
$string['settings_course_contents_max_sections'] = 'Máximo de secciones';
$string['settings_course_contents_max_sections_desc'] = 'Número máximo de secciones del curso incluidas en la solicitud.';
$string['settings_course_context'] = 'Habilitar contexto del curso';
$string['settings_course_context_desc'] = 'Cuando está habilitado, el chatbot recibe información sobre el curso actual para proporcionar respuestas más relevantes.';
$string['settings_coursecontext'] = 'Contexto del curso';
$string['settings_coursecontext_desc'] = 'Controla cuánta información del curso actual se envía al modelo con cada pregunta.';
$string['settings_deepseek'] = 'API de DeepSeek';
$string['settings_deepseek_desc'] = 'Ajustes de conexión con la API de DeepSeek. El plugin llama a https://api.deepseek.com/chat/completions.';
$string['settings_effort_high'] = 'Alto (predeterminado)';
$string['settings_effort_low'] = 'Bajo';
$string['settings_effort_max'] = 'Máximo';
$string['settings_general'] = 'Configuración general';
$string['settings_general_desc'] = 'Configura cómo se presenta el bloque ChatBot a los usuarios.';
$string['settings_max_response_time'] = 'Tiempo máximo de respuesta';
$string['settings_max_response_time_desc'] = 'Tiempo máximo en segundos para esperar la respuesta de DeepSeek (predeterminado: 30).';
$string['settings_max_tokens'] = 'Máximo de tokens de salida';
$string['settings_max_tokens_desc'] = 'Límite superior de tokens generados (1 a 393216). Deja 0 para usar el valor predeterminado de DeepSeek según el modo seleccionado.';
$string['settings_model'] = 'Modelo';
$string['settings_model_desc'] = 'Modelo de DeepSeek utilizado para responder preguntas.';
$string['settings_reasoningeffort'] = 'Esfuerzo de razonamiento';
$string['settings_reasoningeffort_desc'] = 'Cuánto razona el modelo cuando el modo de razonamiento está activado. Se ignora si el modo de razonamiento está desactivado.';
$string['settings_systemprompt'] = 'Prompt de sistema';
$string['settings_systemprompt_default'] = 'Eres un asistente docente útil para un curso de Moodle. Responde de forma clara y concisa, y ayuda al alumnado a comprender el material del curso.';
$string['settings_systemprompt_desc'] = 'Instrucciones que definen el comportamiento del asistente. Se envían como mensaje de sistema en cada solicitud.';
$string['settings_temperature'] = 'Temperatura';
$string['settings_temperature_desc'] = 'Temperatura de muestreo entre 0 y 2. Los valores bajos hacen las respuestas más enfocadas y deterministas. No tiene efecto en el modo de razonamiento.';
$string['settings_thinking_disabled'] = 'Desactivado (más rápido y económico)';
$string['settings_thinking_enabled'] = 'Activado (razonamiento más profundo)';
$string['settings_thinkingmode'] = 'Modo de razonamiento';
$string['settings_thinkingmode_desc'] = 'Activa el modo de razonamiento de DeepSeek para un razonamiento más profundo. Desactivarlo hace las respuestas más rápidas y económicas.';
$string['status_add_block'] = 'Agrega el bloque ChatBot a cualquier curso para empezar a usarlo.';
$string['status_config_required'] = 'Configuración necesaria';
$string['status_config_required_desc'] = 'Para usar el bloque ChatBot, un administrador debe configurar:';
$string['status_configured'] = 'El bloque ChatBot está configurado y listo para usar.';
$string['status_how_to_use'] = 'Cómo usar';
$string['status_manage_settings'] = 'Gestionar la configuración del ChatBot';
$string['strong_error'] = 'Error:';
$string['welcome_message'] = '💡 Haz una pregunta para empezar a chatear con el tutor de IA';
