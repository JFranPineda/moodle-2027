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
 * Language strings (Spanish).
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['studentstitle'] = 'Cursos para estudiantes';
$string['prettyurls'] = 'URL limpias';
$string['prettyurls_desc'] = 'Sirve las direcciones cortas de local/richimath/routes.php (/login, /dashboard, /students). Actívalo solo cuando Apache lea el .htaccess del sitio: necesita mod_rewrite y AllowOverride FileInfo Indexes. Mientras esté apagado, todos los enlaces son la URL normal de Moodle y el sitio funciona igual que siempre.';
$string['pluginname'] = 'Herramientas Richi Math';
$string['richimath:invite'] = 'Invitar alumnos por correo';
$string['privacy:metadata:local_richimath_invitation'] = 'Invitaciones enviadas a correos electrónicos y quién las usó.';
$string['privacy:metadata:local_richimath_invitation:email'] = 'El correo invitado.';
$string['privacy:metadata:local_richimath_invitation:createdby'] = 'Usuario que creó la invitación.';
$string['privacy:metadata:local_richimath_invitation:userid'] = 'Usuario que usó la invitación.';
$string['privacy:metadata:local_richimath_invitation:timecreated'] = 'Cuándo se creó la invitación.';
$string['privacy:metadata:local_richimath_invitation:timeused'] = 'Cuándo se usó la invitación.';
$string['privacy:metadata:local_richimath_courselink'] = 'El enlace compartido de invitación de cada curso.';
$string['privacy:metadata:local_richimath_courselink:createdby'] = 'Usuario que creó el enlace compartido.';

// Invite page.
$string['invitations'] = 'Invitaciones';
$string['invitestudents'] = 'Invitar alumnos';
$string['invitesite'] = 'Invitar a la plataforma';
$string['inviteintro_course'] = 'Un solo enlace para todo el curso, filtrado por los correos invitados de abajo. Quien lo abre indica su correo, escribe su nombre, crea su cuenta (o inicia sesión) y queda matriculado al instante — los correos fuera de la lista no entran.';
$string['inviteintro_site'] = 'Un solo enlace para toda la plataforma, filtrado por los correos invitados de abajo. Quien lo abre indica su correo, crea su cuenta (o inicia sesión) y entra; los cursos se le asignan después.';
$string['sendinvitation'] = 'Invitar este correo';
$string['courselink'] = 'Enlace del curso — uno para todos';
$string['courselinksite'] = 'Enlace de la plataforma — uno para todos';
$string['courselinkintro'] = 'Comparte este único enlace con todos tus alumnos (grupo de WhatsApp, pizarra, proyector). Solo funciona para los correos de la lista de abajo; quien lo abre escribe su correo y continúa. Al agregar un correo abajo, también se le envía este enlace.';
$string['courselinkwhatsapp'] = '¡Hola! Únete a {$a->target} en Richi Math con este enlace: {$a->url} — solo funciona para correos invitados.';
$string['courseentry'] = 'Entrar a {$a}';
$string['courseentryintro'] = 'Escribe el correo electrónico que fue invitado.';
$string['emailnotinvited'] = 'Este correo no está en la lista de invitados. Pide a tu profesor que lo agregue.';
$string['sharesent'] = 'Invitación creada y enviada por correo a {$a}. También puedes compartir el enlace tú mismo.';
$string['sharenotsent'] = 'Invitación creada para {$a}, pero este servidor no pudo enviar el correo. Comparte el enlace tú mismo.';
$string['sharehint'] = 'Enlace personal, de un solo uso, válido hasta el {$a}.';
$string['invitationlist'] = 'Invitaciones enviadas';
$string['expireson'] = 'Vence el {$a}';
$string['confirmdelete'] = '¿Eliminar la invitación de {$a}? Su enlace dejará de funcionar.';
$string['pendinginvitationexists'] = 'Ya hay una invitación pendiente para este correo aquí: copia su enlace desde la lista de abajo.';
$string['sharelink'] = 'Enlace de invitación';
$string['copylink'] = 'Copiar enlace';
$string['copied'] = '¡Copiado!';
$string['sharewhatsapp'] = 'Enviar por WhatsApp';
$string['whatsappmessage'] = '¡Hola! Aquí tienes tu acceso a {$a->target} en Richi Math: {$a->url} — el enlace es personal para {$a->email} y vence el {$a->expires}.';
$string['status'] = 'Estado';
$string['statuspending'] = 'Pendiente';
$string['statusaccepted'] = 'Aceptada';
$string['statusexpired'] = 'Vencida';
$string['acceptedby'] = 'Aceptada por';
$string['invitationdeleted'] = 'Invitación eliminada.';
$string['noinvitations'] = 'Todavía no hay invitaciones.';
$string['hidehiddencategories'] = 'Ocultar las categorías ocultas para todos';
$string['hidehiddencategories_desc'] = 'Una categoría oculta con el ojo en Cursos → Gestionar cursos y categorías se le sigue mostrando, atenuada, a quien puede ver categorías ocultas: cualquier admin o gestor. Con esto activado también desaparece para ellos del catálogo y de la portada. La visibilidad se sigue configurando donde siempre: un ojo por categoría.';
$string['expirydays'] = 'Días hasta que vence una invitación';
$string['expirydays_desc'] = 'Un enlace más antiguo deja de funcionar; el profesor crea uno nuevo.';

// Email.
$string['emailsubject'] = 'Tu invitación a {$a->target}';
$string['emailbody'] = 'Hola:

{$a->inviter} te invita a {$a->target} en {$a->site}.

Abre este enlace, confirma tu correo y crea tu cuenta (o inicia sesión si ya tienes una) para entrar de inmediato:
{$a->url}

Solo pueden entrar los correos invitados, y el tuyo vale hasta el {$a->expires}.';

// Accept page.
$string['acceptinvitation'] = 'Aceptar invitación';
$string['invitationinvalid'] = 'Este enlace de invitación no es válido.';
$string['invitationexpired'] = 'Esta invitación ya venció. Pide una nueva a tu profesor.';
$string['invitationused'] = 'Esta invitación ya fue usada. Inicia sesión con tu cuenta.';
$string['wrongaccount'] = 'Esta invitación es para {$a->email}, pero has iniciado sesión como {$a->current}. Cierra sesión y vuelve a abrir el enlace.';
$string['loginexisting'] = 'Ya existe una cuenta para {$a}. Inicia sesión con ella para terminar.';
$string['createaccount'] = 'Crea tu cuenta';
$string['createaccountintro'] = 'Te invitaron a {$a->target}. Tu cuenta usará el correo {$a->email}.';
$string['passwordagain'] = 'Contraseña (otra vez)';
$string['passwordsdiffer'] = 'Las contraseñas no coinciden.';
$string['createandenter'] = 'Crear cuenta y entrar';
$string['welcomeredeemed'] = '¡Bienvenido! Ya tienes acceso a {$a}.';

// Appearances, plans and the users on them.
$string['appearance'] = 'Apariencia';
$string['appearance_help'] = 'El diseño que lleva este plan. Las apariencias se definen en código — una por sistema de diseño de docs/design/, cada una con su propio tema — así que la lista es fija y toda entrada existe de verdad.';
$string['plan'] = 'Plan';
$string['plans'] = 'Planes';
$string['plansmanage'] = 'Planes y apariencias';
$string['plansintro'] = 'Un plan es el nivel al que pertenece un usuario. No lleva precio: agrupa personas y decide la apariencia que ven. Los usuarios se asignan a los planes en la pantalla siguiente.';
$string['planadd'] = 'Agregar plan';
$string['planedit'] = 'Editar plan';
$string['planname'] = 'Nombre';
$string['planshortname'] = 'Nombre corto';
$string['planshortname_help'] = 'Clave estable para el código y las importaciones. Solo letras, dígitos, - y _; no cambia aunque cambie el nombre visible.';
$string['planshortnametaken'] = 'Otro plan ya usa ese nombre corto.';
$string['plansortorder'] = 'Orden';
$string['plandefault'] = 'Plan por defecto';
$string['plandefault_help'] = 'El plan al que cae un usuario que no tiene ninguno. Exactamente un plan es el de por defecto, y no se puede borrar.';
$string['plandefaultbadge'] = 'Por defecto';
$string['planusers'] = 'Usuarios';
$string['plansaved'] = 'Plan guardado.';
$string['plandeleted'] = 'Plan eliminado. Sus usuarios pasaron al plan por defecto.';
$string['plandeletedefault'] = 'El plan por defecto no se puede eliminar: sus usuarios se quedarían sin a dónde caer. Marca otro plan como el de por defecto primero.';
$string['plandeleteconfirm'] = '¿Eliminar el plan {$a}? Sus usuarios pasan al plan por defecto.';
$string['noplansyet'] = 'Todavía no hay planes. Crea uno primero.';
$string['userplans'] = 'Usuarios y planes';
$string['userplansintro'] = 'Un plan por usuario, y la apariencia que trae. Un usuario sin plan cuenta como {$a}.';
$string['userplansaved'] = 'Plan asignado.';
$string['allplans'] = 'Todos los planes';
$string['searchusers'] = 'Nombre o correo';

// Registro de invitados a una sesión de BigBlueButton y los leads que deja.
$string['leadintro'] = 'Completa estos datos para entrar a la sesión. Es un momento y no necesitas contraseña.';
$string['leadfirstname'] = 'Nombres';
$string['leadlastname'] = 'Apellidos';
$string['leaduniversity'] = 'Universidad';
$string['leaduniversity_help'] = 'La universidad o colegio donde estudias. Escribe «Ninguna» si ahora mismo no estás estudiando.';
$string['leademail'] = 'Correo electrónico';
$string['leadinvalidemail'] = 'Ese correo no parece correcto.';
$string['leadconsent'] = 'Acepto que Richi Academy me escriba sobre cursos y sesiones gratuitas.';
$string['leadsubmit'] = 'Continuar';
$string['leadready'] = 'Listo, {$a}. La sala te está esperando.';
$string['leadjoin'] = 'Entrar a la clase';
$string['leadstitle'] = 'Asistentes a sesiones abiertas';
$string['leadscount'] = '{$a->total} registrados, {$a->contactable} de ellos aceptaron que se les escriba.';
$string['leadsexport'] = 'Descargar la lista de correos (CSV)';
$string['leadsempty'] = 'Todavía no se ha registrado nadie a una sesión abierta.';
$string['leadssessions'] = 'Sesiones';
$string['leadsconsent'] = 'Contactable';
$string['leadsfirstseen'] = 'Se registró';
$string['sessionlinktitle'] = 'Invitar visitantes a esta sesión';
$string['sessionlinkintro'] = 'Envía este enlace, no el de los ajustes de la actividad: este pregunta al visitante quién es antes de dejarlo entrar, y lo guarda.';
$string['sessionlinkemails'] = 'Correos, separados por comas';
$string['sessionlinksend'] = 'Enviar la invitación';
$string['sessionlinksent'] = 'Enviado a {$a} correo(s).';
$string['sessionlinkdisabled'] = 'Esta sesión todavía no admite invitados. Enciende «Permitir acceso de invitados» en los ajustes de la actividad.';
$string['sessionlinksubject'] = 'Te invitamos a la sesión: {$a}';
$string['sessionlinkbody'] = 'Te han invitado a la sesión en vivo «{$a->session}» de Richi Academy.

Regístrate aquí y entras directo a la sala:

{$a->link}

Nos vemos en clase.';
$string['privacy:metadata:local_richimath_lead'] = 'Personas que se registraron para asistir a una sesión abierta de BigBlueButton sin tener cuenta en el sitio.';
$string['privacy:metadata:local_richimath_lead:firstname'] = 'Los nombres que indicaron.';
$string['privacy:metadata:local_richimath_lead:lastname'] = 'Los apellidos que indicaron.';
$string['privacy:metadata:local_richimath_lead:university'] = 'La universidad o colegio que indicaron.';
$string['privacy:metadata:local_richimath_lead:email'] = 'El correo que indicaron.';
$string['privacy:metadata:local_richimath_lead:marketingconsent'] = 'Si aceptaron que se les escriba sobre cursos.';

// El botón de WhatsApp del profesor.
$string['whatsappcategory'] = 'WhatsApp';
$string['whatsappnumber'] = 'Número de WhatsApp';
$string['whatsappnumber_help'] = 'Con código de país y sin espacios, por ejemplo 51987654321. Los alumnos de tus cursos verán un botón que abre un chat con este número.';
$string['whatsappenabled'] = 'Mostrar mi WhatsApp en mis cursos';
$string['whatsappenabled_help'] = 'Enciende el botón en todos los cursos que dictas. Un curso concreto puede cambiarlo.';
$string['whatsappcourse'] = 'Botón de WhatsApp';
$string['whatsappcourse_help'] = 'Cambia, solo para este curso, lo que el profesor eligió en su perfil.';
$string['whatsappcourseoptions'] = 'Lo que decida el profesor
Mostrar siempre
No mostrar';
$string['whatsappwrite'] = 'Escribir a {$a} por WhatsApp';
