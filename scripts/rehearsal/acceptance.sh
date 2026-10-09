#!/usr/bin/env bash
# Acceptance pass for a rehearsal (docs/migration/phase-2-production-copy.md §4):
# the user-facing tickets that a machine can check, run against the copy that
# rehearse-upgrade.sh leaves on :8084. One PASS/FAIL line per check, and the
# screenshots a human still has to look at, kept next to the copy (they show
# real students). Creates a local student qa.alumno in the copy.
#
# Usage: scripts/rehearsal/acceptance.sh ~/richimath-prod-copy [--bbb]
#   --bbb  also opens a NEW, unrecorded room on the real BigBlueButton server
#          (VPS 6), joins it as teacher and as guest, and ends it.
set -uo pipefail   # no -e: one failing check must not hide the rest

COPY=$(realpath "${1:?usage: $0 <production copy dir> [--bbb]}")
BBB=${2:-}
B=http://localhost:8084
WORK=$COPY/work
SHOTS=$WORK/acceptance
mkdir -p "$SHOTS"
ADMINPW=$(sed -n 2p "$WORK/qa-admin.txt")

PASSED=0
FAILED=0
check() {   # check <ticket> <description> <actual> <expected regex>
    if [[ "$3" =~ $4 ]]; then
        PASSED=$((PASSED + 1)); printf 'PASS  %-6s %s\n' "$1" "$2"
    else
        FAILED=$((FAILED + 1)); printf 'FAIL  %-6s %s — got: %s\n' "$1" "$2" "${3:0:160}"
    fi
}
sql() { docker exec -e MYSQL_PWD=root rmrehearsal-db-1 mysql -uroot moodle -N -e "$1"; }
cli() { docker exec -i -u www-data rmrehearsal-web-1 php "$@" 2>/dev/null | grep -v '^\*\|^++'; }
ab() { local s=$1; shift; agent-browser --session "$s" "$@"; }
js() { ab "$1" eval --stdin <<< "$2" | tr -d '"'; }
go() { ab "$1" open "$B$2" >/dev/null; ab "$1" wait --load networkidle >/dev/null; }
login() {   # login <session> <user> <password>
    ab "$1" close >/dev/null 2>&1
    ab "$1" open "$B/login" >/dev/null && ab "$1" set viewport 1440 900 >/dev/null
    ab "$1" wait --load networkidle >/dev/null
    ab "$1" fill '#username' "$2" >/dev/null
    ab "$1" fill '#password' "$3" >/dev/null
    ab "$1" focus '#password' >/dev/null
    ab "$1" press Enter >/dev/null
    ab "$1" wait --load networkidle >/dev/null
}
shot() { ab "$1" screenshot "$SHOTS/$2.png" >/dev/null; }
theme() { js "$1" "[...document.querySelectorAll('link[rel=stylesheet]')].map(l => l.href.match(/styles\.php\/([a-z]+)/)?.[1]).filter(Boolean)[0]"; }

# Real data the checks lean on, found in the copy rather than hardcoded.
QUIZCM=$(sql "SELECT cm.id FROM mdl_quiz q JOIN mdl_course_modules cm ON cm.instance = q.id
    AND cm.module = (SELECT id FROM mdl_modules WHERE name = 'quiz')
    WHERE q.timeopen < UNIX_TIMESTAMP() AND (q.timeclose = 0 OR q.timeclose > UNIX_TIMESTAMP())
    AND q.timelimit > 0 AND q.attempts = 0 AND cm.visible = 1
    AND (SELECT COUNT(*) FROM mdl_quiz_slots s WHERE s.quizid = q.id) > 0 ORDER BY cm.id LIMIT 1")
COURSE=$(sql "SELECT course FROM mdl_course_modules WHERE id = $QUIZCM")
GRADERCOURSE=$(sql "SELECT ctx.instanceid FROM mdl_role_assignments ra JOIN mdl_context ctx ON ctx.id = ra.contextid
    AND ctx.contextlevel = 50 JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'student'
    GROUP BY ctx.instanceid ORDER BY COUNT(*) DESC LIMIT 1")
STUDENT=$(sql "SELECT ra.userid FROM mdl_role_assignments ra JOIN mdl_context ctx ON ctx.id = ra.contextid
    AND ctx.contextlevel = 50 AND ctx.instanceid = $COURSE JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'student'
    JOIN mdl_user u ON u.id = ra.userid AND u.username NOT LIKE 'qa.%'
    WHERE ra.userid NOT IN (SELECT ra2.userid FROM mdl_role_assignments ra2 JOIN mdl_role r2 ON r2.id = ra2.roleid
    AND r2.shortname IN ('editingteacher', 'manager')) ORDER BY ra.userid LIMIT 2")
STUDENT1=$(echo "$STUDENT" | sed -n 1p); STUDENT2=$(echo "$STUDENT" | sed -n 2p)
TEACHER=$(sql "SELECT ra.userid FROM mdl_role_assignments ra JOIN mdl_context ctx ON ctx.id = ra.contextid
    AND ctx.contextlevel = 50 AND ctx.instanceid = $COURSE JOIN mdl_role r ON r.id = ra.roleid
    AND r.shortname = 'editingteacher' LIMIT 1")
echo "Data: quiz cm $QUIZCM in course $COURSE · grader course $GRADERCOURSE · students $STUDENT1,$STUDENT2 · teacher $TEACHER"

# ---------------------------------------------------------------- anonymous
echo "== Public site and login"
S=anon
ab $S close >/dev/null 2>&1; ab $S open "$B/" >/dev/null; ab $S set viewport 1440 900 >/dev/null; ab $S reload >/dev/null
ab $S wait --load networkidle >/dev/null
check FUN-01 "brand in the tab, no Richie" "$(js $S "document.title + ' / ' + /Richie/i.test(document.body.innerText)")" 'Richi.* / false$'
check FUN-05 "five level planets" "$(js $S "document.querySelectorAll('.rm-planet').length")" '^([5-9]|1[0-9])$'
shot $S front-1440
ab $S set viewport 390 844 >/dev/null; ab $S wait 600 >/dev/null
check FUN-05 "front page fits 390" "$(js $S "document.scrollingElement.scrollWidth <= innerWidth")" '^true$'
shot $S front-390
go $S /login
check FUN-03 "login fits 390" "$(js $S "document.scrollingElement.scrollWidth <= innerWidth")" '^true$'
ab $S fill '#username' qa.admin >/dev/null; ab $S fill '#password' 'not-the-password-1A!' >/dev/null
ab $S focus '#password' >/dev/null; ab $S press Enter >/dev/null; ab $S wait --load networkidle >/dev/null
check FUN-03 "failed login stays on /login with its error" "$(js $S "location.pathname + ' ' + !!document.querySelector('#loginerrormessage, .loginerrors, .alert-danger')")" '^/login true$'
shot $S login-failed-390
ab $S set media light reduced-motion >/dev/null; ab $S reload >/dev/null; ab $S wait --load networkidle >/dev/null
check FUN-03 "glyphs stop with reduced motion" "$(js $S "[...document.querySelectorAll('*')].filter(e => { const c = getComputedStyle(e); return c.animationName !== 'none' && c.animationPlayState === 'running' && parseFloat(c.animationDuration) > 0.01; }).length")" '^0$'
ab $S set media light >/dev/null
ab $S close >/dev/null 2>&1

echo "== Clean URLs (both directions)"
for pair in students:local/richimath/students.php invitations:local/richimath/invite.php plans:local/richimath/plans.php \
    members:local/richimath/userplans.php login:login/index.php recover:login/forgot_password.php dashboard:my/index.php \
    courses:course/index.php calendar:calendar/view.php grades:grade/report/overview/index.php messages:message/index.php \
    profile:user/profile.php preferences:user/preferences.php mycourses:my/courses.php \
    notifications:message/output/popup/notifications.php search:search/index.php admin:admin/search.php logout:login/logout.php; do
    r=${pair%%:*}; p=${pair#*:}
    got="$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$B/$p") | $(curl -s -o /dev/null -w '%{http_code}' "$B/$r")"
    check FUN-04 "/$p ⇄ /$r" "$got" "^302 $B/$r \| (200|303)$"
done

# -------------------------------------------------------------------- admin
echo "== Admin"
S=adm
login $S qa.admin "$ADMINPW"
check FUN-01 "admin lands on the dashboard" "$(js $S "location.pathname")" '^/dashboard$'
go $S /admin/user.php
check FUN-22 "user list title" "$(ab $S get title)" '^Lista de usuarios'
go $S '/?redirect=0'
check FUN-25 "banner once, no empty band above it" "$(js $S "(() => { const i = [...document.querySelectorAll('#region-main img')].filter(x => /frontpage-banner/.test(x.src)); const h = document.querySelector('#region-main .course-section-header'); return i.length + ' ' + (h ? getComputedStyle(h).display : 'none'); })()")" '^1 none$'
shot $S home-banner-1440
go $S '/calendar/view.php?view=day'
check FUN-07 "day view draws the current-time line" "$(js $S "!!document.querySelector('.richimath-dayview-nowline')")" '^true$'
go $S '/admin/settings.php?section=themesettingrichimath'
check FUN-06 "theme settings page lists the sidebar style" "$(js $S "!!document.querySelector('select[name=s_theme_richimath_sidebarstyle]')")" '^true$'
go $S "/grade/report/grader/index.php?id=$GRADERCOURSE"
ab $S wait 1200 >/dev/null
js $S "document.scrollingElement.scrollLeft = 700; 1" >/dev/null; ab $S wait 1200 >/dev/null
check FUN-15 "grader names stay right of the sidebar when scrolled" "$(js $S "(() => { const n = [...document.querySelectorAll('#user-grades tr.userrow th.header')].find(e => e.getBoundingClientRect().width > 0); const sb = document.querySelector('.richimath-sidebar').getBoundingClientRect().right; return n ? Math.round(n.getBoundingClientRect().left) - Math.round(sb) : 'no grader'; })()")" '^(0|-?[1-9][0-9]?)$'
shot $S grader-scrolled-1440

# In batches: one evaluate per page list would outlive agent-browser's timeout.
BAD=""
for kind in folder forum; do
    IDS=$(sql "SELECT cm.id FROM mdl_course_modules cm JOIN mdl_modules m ON m.id = cm.module AND m.name = '$kind'")
    for batch in $(echo $IDS | xargs -n 25 | tr ' ' ','); do
        BAD+=$(js $S "(async () => { let bad = []; for (const id of [$batch]) { const r = await fetch('/mod/$kind/view.php?id=' + id); const t = await r.text(); if (r.status !== 200 || /class=\"errormessage\"/.test(t)) bad.push(id); } return bad.join(' '); })()")
    done
done
check DATA "every folder and forum opens" "${BAD:-ok}" '^ok$'
MISSING=$(cli <<'PHP'
<?php define('CLI_SCRIPT', true); require('/var/www/html/config.php');
$missing = 0;
foreach ($DB->get_recordset_select('files', "filename <> '.'") as $f) {
    $path = $CFG->dataroot . '/filedir/' . substr($f->contenthash, 0, 2) . '/' . substr($f->contenthash, 2, 2) . '/' . $f->contenthash;
    if (!is_readable($path) || filesize($path) != $f->filesize) { $missing++; }
}
echo $missing;
PHP
)
check DATA "every stored file is in moodledata with its size" "$MISSING" '^0$'

# Invitation → account → enrolment, through the plugin's own pages.
TOKEN=$(cli <<PHP
<?php define('CLI_SCRIPT', true); require('/var/www/html/config.php');
\core\session\manager::set_user(get_admin());
echo \local_richimath\invitation::create($COURSE, 'qa.invitado.' . time() . '@example.invalid')->record->token;
PHP
)
G=inv; ab $G close >/dev/null 2>&1; go $G "/join?token=$TOKEN"
PW="Inv-$(openssl rand -hex 5)-9"
ab $G fill '#id_firstname' QA >/dev/null; ab $G fill '#id_lastname' Invitado >/dev/null
ab $G fill '#id_password' "$PW" >/dev/null; ab $G fill '#id_password2' "$PW" >/dev/null
ab $G focus '#id_password2' >/dev/null; ab $G press Enter >/dev/null; ab $G wait --load networkidle >/dev/null
check FUN-18 "invited account is created, logged in and enrolled" "$(js $G "location.pathname + ' ' + /Deprecat|line [0-9]+ of/.test(document.body.innerText)") $(sql "SELECT IF(timeused > 0, 'used', 'unused') FROM mdl_local_richimath_invitation WHERE token = '$TOKEN'")" '^/course/view.php false used$'
ab $G close >/dev/null 2>&1
LINK=$(sql "SELECT token FROM mdl_local_richimath_courselink WHERE courseid = $COURSE LIMIT 1")
if [ -n "$LINK" ]; then
    G=shr; ab $G close >/dev/null 2>&1; go $G "/join?token=$LINK"
    ab $G fill '#id_email' 'nadie.en.la.lista@example.invalid' >/dev/null; ab $G focus '#id_email' >/dev/null
    ab $G press Enter >/dev/null; ab $G wait --load networkidle >/dev/null
    check FUN-18 "shared link refuses an email not on the list" "$(js $G "document.body.innerText")" 'no está en la lista'
    ab $G close >/dev/null 2>&1
fi

# Privacy, as a real student of the course, through "Log in as".
go $S "/course/loginas.php?id=1&user=$STUDENT1&sesskey=$(js $S "M.cfg.sesskey")"
ab $S find role button click --name Continuar >/dev/null 2>&1; ab $S wait --load networkidle >/dev/null
check FUN-21 "logged in as the student" "$(js $S "document.body.innerText.includes('identificado como')")" '^true$'
go $S "/user/index.php?id=$COURSE"
check FUN-21 "no participant list" "$(js $S "!!document.querySelector('.errormessage')")" '^true$'
go $S "/user/profile.php?id=$STUDENT2"
check FUN-21 "classmate's profile is not available" "$(js $S "document.body.innerText")" 'no están disponibles|No puede ver'
go $S "/grade/report/user/index.php?id=$COURSE&userid=$STUDENT2"
check FUN-21 "classmate's grades are not available" "$(js $S "!!document.querySelector('.errormessage')")" '^true$'
# Same web service the messaging search box calls; answers whether <id> came back.
found() { js $S "new Promise(r => require(['core/ajax'], A => A.call([{methodname: 'core_message_message_search_users', args: {userid: $STUDENT1, search: '${1//\'/\\\'}', limitnum: 20}}])[0].then(x => r(x.contacts.concat(x.noncontacts).some(u => u.id == $2) ? 'found' : 'hidden')).catch(() => r('error'))))"; }
check FUN-21 "messaging search hides the classmate" "$(found "$(sql "SELECT firstname FROM mdl_user WHERE id = $STUDENT2")" "$STUDENT2")" '^hidden$'
check FUN-21 "messaging search finds the teacher" "$(found "$(sql "SELECT firstname FROM mdl_user WHERE id = $TEACHER")" "$TEACHER")" '^found$'
login $S qa.admin "$ADMINPW"

# ------------------------------------------------------------------ student
echo "== Student (qa.alumno, a real login)"
ALUMNOPW="Al-$(openssl rand -hex 6)-7"
printf 'qa.alumno\n%s\n' "$ALUMNOPW" > "$WORK/qa-alumno.txt"; chmod 600 "$WORK/qa-alumno.txt"
cli <<PHP >/dev/null
<?php define('CLI_SCRIPT', true); require('/var/www/html/config.php');
\core\session\manager::set_user(get_admin());
\$u = \$DB->get_record('user', ['username' => 'qa.alumno']);
\$id = \$u ? \$u->id : \core\user::create_user((object) ['username' => 'qa.alumno', 'password' => '$ALUMNOPW', 'firstname' => 'QA',
    'lastname' => 'Alumno', 'email' => 'qa.alumno@example.invalid', 'auth' => 'manual', 'confirmed' => 1,
    'mnethostid' => \$CFG->mnet_localhost_id, 'lang' => 'es']);
if (\$u) { update_internal_user_password(\$u, '$ALUMNOPW'); }
\$manual = enrol_get_plugin('manual');
\$role = \$DB->get_field('role', 'id', ['shortname' => 'student']);
foreach (\$DB->get_records_select('course', 'id > 1', null, 'id', 'id', 0, 14) + [$COURSE => (object) ['id' => $COURSE]] as \$c) {
    if (\$i = \$DB->get_record('enrol', ['courseid' => \$c->id, 'enrol' => 'manual'])) { \$manual->enrol_user(\$i, \$id, \$role); }
}
\$plan = \$DB->get_field('local_richimath_plan', 'id', ['shortname' => 'secundaria']);
\$DB->delete_records('local_richimath_userplan', ['userid' => \$id]);
\$DB->insert_record('local_richimath_userplan', (object) ['userid' => \$id, 'planid' => \$plan, 'timemodified' => time(), 'usermodified' => 2]);
PHP
S=alu
login $S qa.alumno "$ALUMNOPW"
check FUN-17 "plan Secundaria gives rmsecundaria at login" "$(theme $S)" '^rmsecundaria$'
check FUN-06 "no catalog link for a student" "$(js $S "[...document.querySelectorAll('.richimath-sidebar a')].some(a => a.textContent.trim() === 'Cursos')")" '^false$'
check FUN-08 "more than 12 courses: 12 chips and 'see all'" "$(js $S "[...document.querySelectorAll('a[class*=chip], [class*=chips] a')].filter(e => e.getBoundingClientRect().height > 0).map(e => e.textContent.trim()).slice(-1)[0]")" 'Ver todos'
shot $S dashboard-secundaria-1440
go $S "/course/view.php?id=$COURSE"
ab $S set viewport 390 844 >/dev/null; ab $S reload >/dev/null; ab $S wait --load networkidle >/dev/null
js $S "document.querySelector('button[data-role=end]')?.click(); 1" >/dev/null
check FUN-10 "Consultas does not cover the block drawer toggler on a phone" "$(js $S "(() => { const f = document.querySelector('.richimath-support-fab'), t = document.querySelector('.drawer-right-toggle button'); if (!f || !t) return 'missing'; const a = f.getBoundingClientRect(), b = t.getBoundingClientRect(); return a.bottom <= b.top + 1 ? 'clear' : 'overlap'; })()")" '^clear$'
shot $S course-390

# Quiz: view, attempt (timer, navigation, watermark, no option lines, no
# floating buttons), summary, review (solution box, buttons back).
ab $S set viewport 1440 900 >/dev/null
go $S "/mod/quiz/view.php?id=$QUIZCM"
START=$(ab $S snapshot -i 2>/dev/null | grep -oE 'button "(INTENTO DE CUESTIONARIO|REINTENTAR EL CUESTIONARIO|CONTINUAR[^"]*)" \[ref=e[0-9]+' | grep -oE 'e[0-9]+$' | head -1)
ab $S click "@$START" >/dev/null; ab $S wait 1500 >/dev/null
CONFIRM=$(ab $S snapshot -i 2>/dev/null | grep -oE 'button "Comenzar intento" \[ref=e[0-9]+' | grep -oE 'e[0-9]+$' | head -1)
[ -n "$CONFIRM" ] && { ab $S click "@$CONFIRM" >/dev/null; ab $S wait --load networkidle >/dev/null; }
ab $S wait 2500 >/dev/null
check FUN-14 "attempt: timer, navigation, watermark, no lines between options" "$(js $S "(() => { const f = document.querySelector('.que .formulation'); const o = [...document.querySelectorAll('.que .answer > div')].slice(0, 2).map(x => getComputedStyle(x).borderTopWidth); return document.body.id + ' ' + !!document.querySelector('#quiz-timer, #quiz-time-left') + ' ' + document.querySelectorAll('#mod_quiz_navblock .qnbutton').length + ' ' + /examground/.test(getComputedStyle(f).backgroundImage) + ' ' + o.join('/'); })()")" '^page-mod-quiz-attempt true [1-9][0-9]* true 0px/0px$'
check FUN-10 "no floating buttons during the attempt" "$(js $S "!!document.querySelector('.richimath-support-fab, .richimath-whatsapp-fab')")" '^false$'
shot $S quiz-attempt-1440
ab $S set viewport 390 844 >/dev/null; ab $S wait 800 >/dev/null
check FUN-14 "timer stays visible on a phone" "$(js $S "(() => { const t = document.querySelector('#quiz-timer-wrapper, #quiz-timer'); return t ? getComputedStyle(t).position + ' ' + (t.getBoundingClientRect().height > 0) : 'none'; })()")" '^(sticky|fixed) true$'
shot $S quiz-attempt-390
ab $S set viewport 1440 900 >/dev/null
go $S "$(js $S "document.querySelector('#mod_quiz_navblock .endtestlink').getAttribute('href')" | sed "s#^$B##")"
check FUN-14 "summary, still without floating buttons" "$(js $S "document.body.id + ' ' + !!document.querySelector('.richimath-support-fab')")" '^page-mod-quiz-summary false$'
js $S "document.querySelector('#frm-finishattempt').submit(); 1" >/dev/null
ab $S wait --load networkidle >/dev/null; ab $S wait 4000 >/dev/null
check FUN-14 "review with the solution box styled and buttons back" "$(js $S "(() => { const o = document.querySelector('.que .outcome'); return document.body.id + ' ' + (o ? getComputedStyle(o).backgroundColor : 'no-outcome') + ' ' + !!document.querySelector('.richimath-support-fab'); })()")" '^page-mod-quiz-review (rgb\(244, 248, 255\)|no-outcome) true$'
check FUN-14 "the review speaks Spanish (5.3 strings the pack lacked)" "$(js $S "/Attempt submitted/.test(document.body.innerText)")" '^false$'
shot $S quiz-review-1440

# --------------------------------------------------- BigBlueButton (opt-in)
if [ "$BBB" = "--bbb" ]; then
    echo "== BigBlueButton against the real server (new room, no recording)"
    CMID=$(cli <<PHP
<?php define('CLI_SCRIPT', true); require('/var/www/html/config.php'); require_once(\$CFG->dirroot . '/course/modlib.php');
\core\session\manager::set_user(\$DB->get_record('user', ['username' => 'qa.admin']));
echo create_module((object) ['modulename' => 'bigbluebuttonbn', 'course' => $COURSE, 'section' => 0, 'visible' => 1,
    'name' => 'QA ensayo (borrar)', 'introeditor' => ['text' => '', 'format' => FORMAT_HTML, 'itemid' => 0], 'type' => 0,
    'record' => 0, 'wait' => 0, 'openingtime' => 0, 'closingtime' => 0, 'grade' => 0, 'cmidnumber' => '', 'guestallowed' => 1,
    'mustapproveuser' => 0, 'participants' => json_encode([['selectiontype' => 'all', 'selectionid' => 'all', 'role' => 'viewer']])])->coursemodule;
PHP
)
    S=adm
    go $S "/mod/bigbluebuttonbn/bbb_view.php?action=join&id=$CMID&bn=$(sql "SELECT instance FROM mdl_course_modules WHERE id = $CMID")"
    ab $S wait 8000 >/dev/null
    check FUN-24 "teacher opens the room on the BBB server" "$(ab $S get url)" '^https://clases\.richiacademy\.com/html5client/'
    UIDG=$(sql "SELECT b.guestlinkuid FROM mdl_bigbluebuttonbn b JOIN mdl_course_modules cm ON cm.instance = b.id WHERE cm.id = $CMID")
    G=guest; ab $G close >/dev/null 2>&1; go $G "/local/richimath/session.php?uid=$UIDG"
    ab $G fill '#id_firstname' QA >/dev/null; ab $G fill '#id_lastname' Visitante >/dev/null
    ab $G fill '#id_university' 'Universidad de Prueba' >/dev/null; ab $G fill '#id_email' 'qa.visitante@example.invalid' >/dev/null
    ab $G check '#id_marketingconsent' >/dev/null 2>&1; ab $G focus '#id_email' >/dev/null; ab $G press Enter >/dev/null
    ab $G wait --load networkidle >/dev/null
    js $G "document.forms[0].submit(); 1" >/dev/null; ab $G wait 8000 >/dev/null
    check FUN-19 "guest registers and enters the room" "$(ab $G get url) $(sql "SELECT joincount FROM mdl_local_richimath_lead WHERE email = 'qa.visitante@example.invalid'")" '^https://clases\.richiacademy\.com/html5client/.* 1$'
    ab $G close >/dev/null 2>&1
    go $S "/mod/bigbluebuttonbn/view.php?id=$CMID"
    js $S "new Promise(r => require(['core/ajax'], A => A.call([{methodname: 'mod_bigbluebuttonbn_end_meeting', args: {bigbluebuttonbnid: $(sql "SELECT instance FROM mdl_course_modules WHERE id = $CMID"), groupid: 0}}])[0].then(() => r('ended')).catch(() => r('error'))))" >/dev/null
fi

for s in anon adm alu inv shr guest; do ab $s close >/dev/null 2>&1; done
echo "== $PASSED passed, $FAILED failed · screenshots in $SHOTS"
echo "By eye: the screenshots above, and what no script sees (colours, spacing, the solar system's motion)."
[ "$FAILED" -eq 0 ]
