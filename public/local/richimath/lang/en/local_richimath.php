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
 * Language strings (base).
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['studentstitle'] = 'Courses for students';
$string['prettyurls'] = 'Clean URLs';
$string['prettyurls_desc'] = 'Serve the short addresses of local/richimath/routes.php (/login, /dashboard, /students). Tick this only once Apache reads the site .htaccess — it needs mod_rewrite and AllowOverride FileInfo Indexes. While it is off, every link is the plain Moodle URL and the site works as usual.';
$string['pluginname'] = 'Richi Math tools';
$string['richimath:invite'] = 'Invite students by email';
$string['privacy:metadata:local_richimath_invitation'] = 'Invitations sent to email addresses, and who redeemed them.';
$string['privacy:metadata:local_richimath_invitation:email'] = 'The invited email address.';
$string['privacy:metadata:local_richimath_invitation:createdby'] = 'The user who created the invitation.';
$string['privacy:metadata:local_richimath_invitation:userid'] = 'The user who redeemed the invitation.';
$string['privacy:metadata:local_richimath_invitation:timecreated'] = 'When the invitation was created.';
$string['privacy:metadata:local_richimath_invitation:timeused'] = 'When the invitation was redeemed.';
$string['privacy:metadata:local_richimath_courselink'] = 'The shared invitation link of each course.';
$string['privacy:metadata:local_richimath_courselink:createdby'] = 'The user who created the shared link.';

// Invite page.
$string['invitations'] = 'Invitations';
$string['invitestudents'] = 'Invite students';
$string['invitesite'] = 'Invite to the site';
$string['inviteintro_course'] = 'One link for the whole course, filtered by the invited emails below. Whoever opens it names their email, types their own name, creates their account (or signs in) and is enrolled at once — emails not on the list stay out.';
$string['inviteintro_site'] = 'One link for the whole platform, filtered by the invited emails below. Whoever opens it names their email, creates their account (or signs in) and gets in; enrol them in courses afterwards.';
$string['sendinvitation'] = 'Invite this email';
$string['courselink'] = 'Course link — one for everyone';
$string['courselinksite'] = 'Platform link — one for everyone';
$string['courselinkintro'] = 'Share this single link with all your students (WhatsApp group, board, projector). It only works for the emails on the list below; whoever opens it names their email and continues. Adding an email below also sends them this link.';
$string['courselinkwhatsapp'] = 'Hi! Join {$a->target} at Richi Math with this link: {$a->url} — it only works for invited emails.';
$string['courseentry'] = 'Join {$a}';
$string['courseentryintro'] = 'Enter the email address that was invited.';
$string['emailnotinvited'] = 'This email is not on the invited list. Ask your teacher to add it.';
$string['sharesent'] = 'Invitation created and emailed to {$a}. You can also share the link yourself.';
$string['sharenotsent'] = 'Invitation created for {$a}, but this server could not email it. Share the link yourself.';
$string['sharehint'] = 'Personal link, single use, valid until {$a}.';
$string['invitationlist'] = 'Invitations sent';
$string['expireson'] = 'Expires {$a}';
$string['confirmdelete'] = 'Delete the invitation for {$a}? Its link will stop working.';
$string['pendinginvitationexists'] = 'There is already a pending invitation for this email here — copy its link from the list below.';
$string['sharelink'] = 'Invitation link';
$string['copylink'] = 'Copy link';
$string['copied'] = 'Copied!';
$string['sharewhatsapp'] = 'Send by WhatsApp';
$string['whatsappmessage'] = 'Hi! Here is your access to {$a->target} at Richi Math: {$a->url} — the link is personal for {$a->email} and expires on {$a->expires}.';
$string['status'] = 'Status';
$string['statuspending'] = 'Pending';
$string['statusaccepted'] = 'Accepted';
$string['statusexpired'] = 'Expired';
$string['acceptedby'] = 'Accepted by';
$string['invitationdeleted'] = 'Invitation deleted.';
$string['noinvitations'] = 'No invitations yet.';
$string['hidehiddencategories'] = 'Hide hidden categories from everyone';
$string['hidehiddencategories_desc'] = 'A category hidden with the eye in Courses > Manage courses and categories is still shown, dimmed, to anyone who may see hidden categories — every admin and manager. With this on it disappears from the course index and the front page for them too. Visibility itself stays where it always was: one eye per category.';
$string['expirydays'] = 'Days until an invitation expires';
$string['expirydays_desc'] = 'A link older than this stops working; the teacher creates a new one.';

// Email.
$string['emailsubject'] = 'Your invitation to {$a->target}';
$string['emailbody'] = 'Hello,

{$a->inviter} invites you to {$a->target} at {$a->site}.

Open this link, confirm your email address and create your account (or sign in if you already have one) to get in right away:
{$a->url}

Only invited email addresses can enter, and yours is valid until {$a->expires}.';

// Accept page.
$string['acceptinvitation'] = 'Accept invitation';
$string['invitationinvalid'] = 'This invitation link is not valid.';
$string['invitationexpired'] = 'This invitation has expired. Ask your teacher for a new one.';
$string['invitationused'] = 'This invitation has already been used. Sign in with your account.';
$string['wrongaccount'] = 'This invitation is for {$a->email}, but you are signed in as {$a->current}. Sign out and open the link again.';
$string['loginexisting'] = 'There is already an account for {$a}. Sign in with it to finish.';
$string['createaccount'] = 'Create your account';
$string['createaccountintro'] = 'You were invited to {$a->target}. Your account will use the email {$a->email}.';
$string['passwordagain'] = 'Password (again)';
$string['passwordsdiffer'] = 'The passwords do not match.';
$string['createandenter'] = 'Create account and enter';
$string['welcomeredeemed'] = 'Welcome! You now have access to {$a}.';

// Appearances, plans and the users on them.
$string['appearance'] = 'Appearance';
$string['appearance_help'] = 'The design this plan wears. Appearances are defined in code — one per design system under docs/design/, each implemented by its own theme — so the list here is fixed and every entry is real.';
$string['plan'] = 'Plan';
$string['plans'] = 'Plans';
$string['plansmanage'] = 'Plans and appearances';
$string['plansintro'] = 'A plan is the level a user belongs to. It carries no price: it groups people and decides the appearance they see. Users are assigned to plans on the next screen.';
$string['planadd'] = 'Add plan';
$string['planedit'] = 'Edit plan';
$string['planname'] = 'Name';
$string['planshortname'] = 'Short name';
$string['planshortname_help'] = 'Stable key for code and imports. Letters, digits, - and _ only; it does not change when the display name does.';
$string['planshortnametaken'] = 'Another plan already uses this short name.';
$string['plansortorder'] = 'Order';
$string['plandefault'] = 'Default plan';
$string['plandefault_help'] = 'The plan a user without one falls back to. Exactly one plan is the default, and it cannot be deleted.';
$string['plandefaultbadge'] = 'Default';
$string['planusers'] = 'Users';
$string['plansaved'] = 'Plan saved.';
$string['plandeleted'] = 'Plan deleted. Its users moved to the default plan.';
$string['plandeletedefault'] = 'The default plan cannot be deleted: its users would have nowhere to fall back to. Make another plan the default first.';
$string['plandeleteconfirm'] = 'Delete the plan {$a}? Its users move to the default plan.';
$string['noplansyet'] = 'There are no plans yet. Create one first.';
$string['userplans'] = 'Users and plans';
$string['userplansintro'] = 'One plan per user, and the appearance that comes with it. A user without a plan is treated as {$a}.';
$string['userplansaved'] = 'Plan assigned.';
$string['allplans'] = 'All plans';
$string['searchusers'] = 'Name or email';

// Guest registration for a BigBlueButton session, and the leads it leaves.
$string['leadintro'] = 'Fill this in to join the session. It takes a moment and you will not need a password.';
$string['leadfirstname'] = 'First name';
$string['leadlastname'] = 'Surname';
$string['leaduniversity'] = 'University';
$string['leaduniversity_help'] = 'The university or school you are studying at. Write "None" if you are not studying right now.';
$string['leademail'] = 'Email address';
$string['leadinvalidemail'] = 'That address does not look right.';
$string['leadconsent'] = 'I agree that Richi Academy may write to me about courses and free sessions.';
$string['leadsubmit'] = 'Continue';
$string['leadready'] = 'All set, {$a}. The room is waiting for you.';
$string['leadjoin'] = 'Join the class';
$string['leadstitle'] = 'Open session attendees';
$string['leadscount'] = '{$a->total} registered, {$a->contactable} of them agreed to be contacted.';
$string['leadsexport'] = 'Download the mailing list (CSV)';
$string['leadsempty'] = 'Nobody has registered for an open session yet.';
$string['leadssessions'] = 'Sessions';
$string['leadsconsent'] = 'Contactable';
$string['leadsfirstseen'] = 'Registered';
$string['sessionlinktitle'] = 'Invite guests to this session';
$string['sessionlinkintro'] = 'Send this link, not the one in the activity settings: this one asks visitors who they are before letting them in, and records it.';
$string['sessionlinkemails'] = 'Email addresses, separated by commas';
$string['sessionlinksend'] = 'Send the invitation';
$string['sessionlinksent'] = 'Sent to {$a} address(es).';
$string['sessionlinkdisabled'] = 'This session does not allow guests yet. Turn on "Allow guest access" in the activity settings first.';
$string['sessionlinksubject'] = 'You are invited to the session: {$a}';
$string['sessionlinkbody'] = 'You have been invited to the live session "{$a->session}" at Richi Academy.

Register here and you go straight into the room:

{$a->link}

See you in class.';
$string['privacy:metadata:local_richimath_lead'] = 'People who registered to attend an open BigBlueButton session without having an account on the site.';
$string['privacy:metadata:local_richimath_lead:firstname'] = 'The first name they gave.';
$string['privacy:metadata:local_richimath_lead:lastname'] = 'The surname they gave.';
$string['privacy:metadata:local_richimath_lead:university'] = 'The university or school they said they attend.';
$string['privacy:metadata:local_richimath_lead:email'] = 'The email address they gave.';
$string['privacy:metadata:local_richimath_lead:marketingconsent'] = 'Whether they agreed to be contacted about courses.';

// The teacher's WhatsApp button.
$string['whatsappcategory'] = 'WhatsApp';
$string['whatsappnumber'] = 'WhatsApp number';
$string['whatsappnumber_help'] = 'With the country code and no spaces, for example 51987654321. Students in your courses will see a button that opens a chat with this number.';
$string['whatsappenabled'] = 'Show my WhatsApp in my courses';
$string['whatsappenabled_help'] = 'Turns the button on in every course you teach. A single course can still override this.';
$string['whatsappcourse'] = 'WhatsApp button';
$string['whatsappcourse_help'] = 'Overrides what the teacher chose in their profile, for this course only.';
$string['whatsappcourseoptions'] = 'What the teacher decides
Always show
Never show';
$string['whatsappwrite'] = 'Write to {$a} on WhatsApp';
