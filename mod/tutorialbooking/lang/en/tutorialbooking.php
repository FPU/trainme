<?php
// This file is part of the Tutorial Booking activity.
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
 * English language localisation strings.
 *
 * @package    mod_tutorialbooking
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.ellis@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addstudents'] = 'Add Students';
$string['ajax_invalid_slots'] = 'The slots are not in the correct tutorial booking activity';
$string['ajax_slots_not_exist'] = 'The slots do not exist';
$string['adminblockingprompt'] = 'Switch on Blocking functionality';
$string['adminblockinghelp'] = 'Switching this on will allow blocking of signups for selected users';
$string['adminiconprompt'] = 'Use Icons?';
$string['adminiconhelp'] = 'If off, all links will be in text, no icons will be shown';
$string['adminlockprompt'] = 'Select to lock tutorials by default';
$string['adminlockhelp'] = 'Select to make all tutorials locked by default';
$string['adminnumbersprompt'] = 'Select default number of attendees';
$string['adminnumbershelp'] = 'Enter the default number of attendees for tutorials - can be overridden when configuring tutorials';
$string['adminserviceprompt'] = 'Mark this instance as a live Moodle service';
$string['adminservicehelp'] = 'NOTICE: Swiching this on will mean that emails/notifications will be sent to signups - when off only main admin (id=2) gets notifications';
$string['adminwaitingprompt'] = 'Switch on Waiting List functionality';
$string['adminwaitinghelp'] = 'Switching this on will allow waiting lists on timeslot sessions';
$string['after'] = 'After {$a->session}';
$string['alreadysignedup'] = 'You have already signed up to a session.';
$string['attendees'] = 'Currently signed up';
$string['attendcoltitle'] = 'Attendance';
$string['availabletoadd'] = 'Available to add';
$string['backtosession'] = 'Messages sent, click here to go back to Signup List';
$string['blockedtotal'] = 'includes {$a} blocked user/s';
$string['blockedusermessage'] = 'You have been blocked from signing up to these sessions - please see convenor for reasons';
$string['blockuserprompt'] = 'Signed Up - Click to block User';
$string['cancel'] = 'Cancel';
$string['completionsignedupgroup'] = 'Require signup';
$string['completionsignedup'] = 'Students must signup to a tutorial group in this activity to complete it.';
$string['confirm'] = 'Confirm';
$string['confirmmessage'] = 'Are you sure you wish to remove {$a->name} from {$a->timeslot}?';
$string['confirmremovefromslot'] = 'Are you sure you wish to remove your signup from the tutorial booking?';
$string['confirmusersignupremoval'] = 'Confirm signup removal';
$string['copysession'] = 'Copy Time Slot';
$string['coursefullname'] = 'CourseFullname';
$string['cronfixduplicates'] = 'Fix tutorial booking duplicate signups';
$string['defaultdescription'] = 'Slot {$a}';
$string['deletepageheader'] = 'Confirm Delete';
$string['deletesession'] = 'Delete';
$string['deletetutorialprompt'] = 'Delete this sign-up list';
$string['deletewarningtext'] = 'Are you sure you want to delete "{$a}"';
$string['editsession'] = 'Edit/Move/Copy';
$string['editsessionheading'] = 'Edit Existing Time Slot';
$string['editsessionhelp'] = 'To modify the time slot, please fill in the form below.';
$string['editspaceserror'] = 'ERROR: You cannot reduce the number of spaces ({$a->spaces}) to less that the number of signups ({$a->signedup})';
$string['edittutorialprompt'] = 'Edit this sign-up list';
$string['emailgroupprompt'] = 'Email group';
$string['emailpagetitle'] = 'Email Group';
$string['eventsessionadded'] = 'Session added';
$string['eventsessiondeleted'] = 'Session deleted';
$string['eventsessionmessage'] = 'Messaged users in session';
$string['eventsessionupdated'] = 'Session updated';
$string['eventsignupadded'] = 'Signup';
$string['eventsignupcapabilityremoved'] = 'Signup capability lost';
$string['eventsignupremoved'] = 'Signup removed';
$string['eventsignupteacheradded'] = 'Signup forced';
$string['eventsignupteacherremoved'] = 'Signup revoked';
$string['exportcsvlistallprompt'] = 'Export all tutorial signups on this course as CSV';
$string['exportcsvlistprompt'] = 'Export as CSV';
$string['exportlistprompt'] = 'Export this sign-up list';
$string['first'] = 'First';
$string['hidesession'] = 'Visible - Hide Time Slot';
$string['idnumber'] = 'IDNumber';
$string['indexnoid'] = 'A course id must be stipulated to view all tutorials';
$string['instancedesc'] = 'Sign-up List Notes';
$string['instancedeschelp'] = 'Information students need to know when signing up, such as duration of session.';
$string['instanceheading'] = 'General Settings';
$string['instancetitle'] = 'Sign-up List Title';
$string['instancenamehelp'] = 'e.g. Tutorial 1 or Computer Labs or Fortnightly Tutorials';
$string['last'] = 'Last';
$string['linktomanagetext'] = 'Manage Signup Lists';
$string['liveservicemsg'] = 'Live service recognised, notification being sent to all signups';
$string['locked'] = 'Unlock tutorial';
$string['lockederror'] = 'The tutorial is locked. You may not signup at this time.';
$string['lockedprompt'] = 'Locked';
$string['lockhelp'] = 'If locked students will not be able to signup (or unsignup) from any timeslot in this tutorial.
Locking it now will effectively freeze the signup lists in their current state.';
$string['lockwarning'] = 'This tutorial has been locked by the convenor. You cannot signup to (or remove yourself from) any slot.';
$string['messagessent'] = 'Messages sent';
$string['messageprompt'] = 'Message';
$string['messageprovider:notify'] = 'Tutorial Notification';
$string['messagewillbesent'] = 'Message to the student being removed';
$string['moduleadminname'] = 'Tutorial Booking';
$string['modulename'] = 'Tutorial Booking';
$string['modulename_help'] = 'The tutorial booking activity allows students to sign up to a single slot.

Teachers can:

* Set the names of other people who have signed up to a slot to be visible or hidden from students.
* Print registers of students signed up to slots.
* Generate a csv file of the signups.
* Manually add and remove students from slots.
* Lock and unlock the ability to sign up.
* Send a message to everyone signed up to a slot
';
$string['modulenameplural'] = 'Tutorial Bookings';
$string['movedownsession'] = 'Move Down';
$string['moveupsession'] = 'Move Up';
$string['moveslot'] = 'Drag this slot to move it.';
$string['newtimslotprompt'] = 'Add a Time Slot to this sign-up list';
$string['newsessionheading'] = 'New Time Slot';
$string['newsessionhelp'] = 'To create a new time slot in the above sign-up list, please fill in the form below.';
$string['noslots'] = 'There are no timeslots in this tutorial booking.';
$string['nosignup'] = 'You are not signed up to the tutorial booking.';
$string['notimeslotediting'] = 'Since this is a new tutorial, please save this page and return to create signup lists, remember to set visibility to hide this to avoid use before tutorial has been completely setup.';
$string['numbersline'] = '{$a->total} places available in total ({$a->taken} taken, {$a->left} free)';
$string['numbersline_oversubscribed'] = '{$a->total} places available in total ({$a->taken} taken, oversubscribed by {$a->left})';
$string['option_spaces_high'] = 'The number of spaces must be less than 65536';
$string['option_spaces_low'] = 'The number of spaces must be greater than 0';
$string['oversubscribed'] = 'There are {$a->freeslots} places left on {$a->timeslotname}. You tried to add {$a->numbertoadd} student.';
$string['pagecrumb'] = 'Time Slots';
$string['pagetitle'] = 'Tutorial Booking';
$string['pluginadministration'] = 'Tutorial Booking';
$string['pluginname'] = 'Tutorial Booking';
$string['positionfirst'] = 'Top of the Page';
$string['positionlast'] = 'Bottom of the Page';
$string['positionprompt'] = 'Position';
$string['privacy'] = 'Privacy';
$string['privacy_showall'] = 'Students can see all signups';
$string['privacy_showown'] = 'Students can only see their signup';
$string['realname'] = 'RealName';
$string['reasonrequired'] = 'You must provide the reason you are removing the student.';
$string['registerdateline'] = 'Please Enter Date of Tutorial (dd/mm/yy):&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_&nbsp;_&nbsp;&nbsp;_&nbsp;_&nbsp;&nbsp;_&nbsp; _';
$string['registerfooter'] = 'Please sign next to your name to indicate attendance. If your name is not on the list then please do
not add it without asking first.';
$string['registerprintdate'] = 'Print Register (By Signup)';
$string['registerprintname'] = 'Print Register (By Name)';
$string['removalreason'] = 'Reason for removal';
$string['remove'] = 'Confirm removal';
$string['removefromslot'] = 'Remove me from this slot';
$string['removalmessagesubject'] = 'You have been removed from {$a->timeslot}';
$string['removeuserfromslot'] = 'Remove from this slot';
$string['reset'] = 'Undo';
$string['save'] = 'Save';
$string['saveasnew'] = 'Save as new time slot';
$string['search:activity'] = 'Tutorial booking - activity information';
$string['search:session'] = 'Tutorial booking - time slot information';
$string['sendmessage'] = 'Send Message';
$string['sentby'] = 'Sender';
$string['senttime'] = 'Sent on';
$string['sentto'] = 'Recipients';
$string['sessiondescriptionhelp'] = 'Date, Time & Location e.g. 10:00am on Thursday 14th Aug in Room B35, Business School or 10:00am on Thursday 14th & 21st Aug, and 4th Sep in Room B35, Business School.';
$string['sessiondescriptionhelp2'] = 'Please ensure you have included the name of the building!<br/>
Module Convenors should make sure that they have booked the room!';
$string['sessiondescriptionprompt'] = 'Title';
$string['sessionerror'] = '{$a}';
$string['sessionfull'] = 'No spaces left, please pick another session.';
$string['sessionpagetitle'] = 'Time Slot Management';
$string['sessionsummaryprompt'] = 'Details';
$string['showallmessages'] = 'Show all messages';
$string['showalltutorialbookings'] = 'Tutorial booking index';
$string['showmymessages'] = 'Show my messages only';
$string['showsession'] = 'Hidden - Make Visible';
$string['signupforslot'] = 'Sign me up for this slot';
$string['signuprequired'] = 'Sign up to the tutorial';
$string['spacesprompt'] = 'Max Number of Students';
$string['statsline'] = '({$a->places} slots, {$a->signedup} taken)';
$string['statslineblocked'] = '({$a->places} slots, {$a->signedup} taken - {$a->blocked} blocked)';
$string['studentcoltitle'] = 'Student Name';
$string['subjecttitleprompt'] = 'Subject';
$string['testservicemsg'] = 'Non live service - notification being sent to Admin (id=2)';
$string['thereareno'] = 'There are no tutorials in this course';
$string['timeslotheading'] = 'Signup List Management';
$string['timeslottitle'] = 'TimeSlotTitle';
$string['tutorialbooking'] = 'TutorialBooking';
$string['tutorialbooking:addinstance'] = 'Allows a user to add this activity to a course (not used in Moodle 2.2)';
$string['tutorialbooking:adduser'] = 'Allows the user to add students to a signup group.';
$string['tutorialbooking:editsignuplist'] = 'Allow users to add, delete and edit the signup lists.';
$string['tutorialbooking:export'] = 'Allow the user to export tutorial signups';
$string['tutorialbooking:exportallcoursetutorials'] = 'Required to export list for all tutorial signups in a course.';
$string['tutorialbooking:message'] = 'Allows the user send messages to students via the tutorialbooking activity.';
$string['tutorialbooking:oversubscribe'] = 'Allows the user to add studets to a group even if this will take it over its user limit.';
$string['tutorialbooking:printregisters'] = 'Allows the user to print registers for the activity.';
$string['tutorialbooking:removeuser'] = 'Allows the removal of students from a signup group.';
$string['tutorialbooking:submit'] = 'Required to signup to a tutorial booking session.';
$string['tutorialbooking:viewadminpage'] = 'Allows the user to see the admin page of the activity.';
$string['tutorialbooking:viewallmessages'] = 'Required to view messages other users sent in a tutorial booking session.';
$string['unauthorised'] = 'You do not have permission to signup.';
$string['unblockuserprompt'] = 'Blocked Users - Click to unblock User';
$string['unlocked'] = 'Lock tutorial';
$string['useralreadysignedup'] = 'User {$a->id} is already signed up to a session.';
$string['userdisplay'] = '{$a->name} ({$a->username})';
$string['username'] = 'UserName';
$string['viewmessages'] = 'View sent messages';
$string['you'] = 'You';
$string['yousignedup'] = 'You are signed up to this slot';
