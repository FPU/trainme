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

use mod_tutorialbooking\renderable\tutorialbooking;
use core\output\notification;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderer for tutorial bookings.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_tutorialbooking_renderer extends plugin_renderer_base {
    /**
     * Display a page with a link back to the page the user was on.
     *
     * @param string $message The message to be displayed to the user.
     * @param moodle_url $returnurl A url that the user will be sent to when they click on the return link.
     * @return void
     */
    public function back_to_session($message, moodle_url $returnurl) {
        echo $this->header();
        echo html_writer::div($message);
        $linktext = html_writer::empty_tag('br').'['.get_string('backtosession', 'tutorialbooking').']';
        echo html_writer::link($returnurl, $linktext);
        echo $this->footer();
    }

    /**
     * Function to display a confirmation screen on deletion of a tutorial/session (signup list).
     *
     * @global moodle_page $PAGE The page object.
     * @param int $courseid The course id - always a valid value.
     * @param int $tutorialid The tutorial id - also always a valid id.
     * @param int $sessionid The sessionid 0 if tutorial is being deleted - otherwise a valid id.
     * @param string $title Title to display on the page - usually the tutorial/session name.
     * @return void Outputs confirmation page.
     */
    public function deleteconfirm($courseid, $tutorialid, $sessionid, $title) {
        global $PAGE;

        if (!$sessionid) {
            $sessionid = 0;
        }

        $cancelaction = $PAGE->url->out();
        // Query string stripped then rebuilt to avoid double escaping.
        $confirmaction = new moodle_url(strip_querystring($PAGE->url),
            array (
                'courseid' => $courseid,
                'tutorialid' => $tutorialid,
                'sessionid' => $sessionid,
                'action' => 'confirmdelete'
            )
        );

        if ($sessionid) {
            $stats = mod_tutorialbooking_session::getsessionstats($sessionid);
        } else {
            $stats = mod_tutorialbooking_tutorial::getstatsfortutorial($tutorialid);
        }

        $statsline = html_writer::tag('p',
                        html_writer::tag('strong', get_string('statsline', 'tutorialbooking', $stats))
                        );

        echo $this->header();
        echo $this->heading(get_string('deletepageheader', 'tutorialbooking'), 2, 'helptitle', 'uniqueid');
        echo $this->confirm(get_string('deletewarningtext', 'tutorialbooking', $title).$statsline, $confirmaction, $cancelaction);
        echo $this->footer();
    }

    /**
     * Display a confirmation prompt for the user to remove their signup.
     *
     * @param stdClass $cm A course module record for a tutorialbooking activity.
     */
    public function delete_signup_confirm($cm) {
        $cancelaction = new moodle_url('/mod/tutorialbooking/view.php', array('id' => $cm->id, 'redirect' => 0));
        $confirmparams = array(
            'id' => $cm->id,
            'action' => 'confirmedremove',
            'redirect' => 0,
            'sesskey' => sesskey(),
        );
        $confirmaction = new moodle_url('/mod/tutorialbooking/view.php', $confirmparams);
        echo $this->header();
        echo $this->confirm(get_string('confirmremovefromslot', 'mod_tutorialbooking'), $confirmaction, $cancelaction);
        echo $this->footer();
    }

    /**
     * Displays messages sent via the activity.
     *
     * @param stdClass $messagestore Object containing a list of messages and other information to be rendered.
     * @return void
     */
    public function displaymessagelist(stdClass $messagestore) {
        // Page setup stuff.
        echo $this->header();
        echo $this->heading(get_string('sessionpagetitle', 'tutorialbooking'), 2);

        $this->display_filter_link($messagestore);

        echo $this->render_from_template('mod_tutorialbooking/messages', $messagestore->messages);

        $url = new moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', array(
                'action' => 'viewmessages',
                'tutorialid' => $messagestore->tutorialid,
                'courseid' => $messagestore->courseid,
                'messages' => $messagestore->maxrecords,
                'filter' => $messagestore->viewallmessages,
            ));

        // Display a paging bar.
        echo $this->paging_bar($messagestore->totalmessages,
                $messagestore->page,
                $messagestore->maxrecords,
                $url,
               'page');

        // Draw the page footer.
        echo $this->footer();
    }

    /**
     * Used by displaymessagelist() to generate a link to allow users to choose
     * between seeing all  messages and only their own, if they have the capability
     * to see all messages sent.
     *
     * @param stdClass $messagestore Object containing a list of messages and other information to be rendered.
     * @return void
     */
    protected function display_filter_link(stdClass $messagestore) {
        // Display a link to change the filter.
        if ($messagestore->can_view_all) {
            if ($messagestore->viewallmessages == mod_tutorialbooking_message::VIEWALLMESSAGES) {
                $url = new moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', array(
                    'action' => 'viewmessages',
                    'tutorialid' => $messagestore->tutorialid,
                    'courseid' => $messagestore->courseid,
                    'messages' => $messagestore->maxrecords,
                    'page' => 0,
                ));
                $filtertext = get_string('showmymessages', 'mod_tutorialbooking');
            } else {
                $url = new moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php', array(
                    'action' => 'viewmessages',
                    'tutorialid' => $messagestore->tutorialid,
                    'courseid' => $messagestore->courseid,
                    'messages' => $messagestore->maxrecords,
                    'page' => 0,
                    'filter' => mod_tutorialbooking_message::VIEWALLMESSAGES,
                ));
                $filtertext = get_string('showallmessages', 'mod_tutorialbooking');
            }
            echo html_writer::tag('p', html_writer::link($url, $filtertext));
        }
    }

    /**
     * Display the form to add users to a tutorial session,
     * unfortunatly the user select contral does not seem to
     * work with standard Moodle forms or I would have used them.
     *
     * @param stdClass $session The database recort for the tutorial slot the users are being added to.
     * @param stdClass $tutorial The database record for the tutorial booking activity the session is in.
     * @return void
     */
    public function display_addform($session, $tutorial) {
        $options = array('tutorialid' => $session->tutorialid, 'extrafields' => array('username', 'idnumber'), 'multiselect' => 1);
        $userselect = new mod_tutorialbooking_session_add_user('addtosession', $options);

        $returnurl = new moodle_url('/mod/tutorialbooking/tutorialbooking_sessions.php',
                array('tutorialid' => $tutorial->id,
                    'courseid' => $tutorial->course,
                    'id' => $session->id,
                    'action' => 'adduserconfirm'));

        $buffer = '';
        $buffer .= $userselect->display(true);
        // The submit button.
        $buffer .= html_writer::empty_tag('input',
                array('type' => 'submit',
                    'name' => 'addtosession_add',
                    'id' => 'addtosession_add',
                    'value' => get_string('addstudents', 'tutorialbooking')
                    ));

        echo $this->header();
        echo html_writer::tag('form', $buffer, array('method' => 'post', 'action' => $returnurl->out(false)));
        echo $this->footer();
    }

    /**
     * Generate the general information about the tutorial.
     *
     * @global moodle_page $PAGE
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking
     * @return void
     */
    protected function tutorial_information(tutorialbooking $tutorialbooking) {
        global $PAGE;
        echo $this->heading(format_string($tutorialbooking->tutorial->name, true, array()), 2, 'sectionname');
        echo $this->output->box(format_module_intro('tutorialbooking', $tutorialbooking->tutorial, $PAGE->cm->id),
                'generalbox', 'intro');

        // Tutorial statistics.
        if (!empty($tutorialbooking->tstats)) {
            if (!empty($tutorialbooking->cfg->blockingon)) {
                $notification = new notification(
                        get_string('statslineblocked', 'tutorialbooking', $tutorialbooking->tstats),
                        notification::NOTIFY_INFO
                );
                $notification->set_show_closebutton(false);
            } else {
                $notification = new notification(
                        get_string('statsline', 'tutorialbooking', $tutorialbooking->tstats),
                        notification::NOTIFY_INFO
                );
                $notification->set_show_closebutton(false);
            }
            echo $this->render($notification);
        }

        // Locked status line.
        if ($tutorialbooking->tutorial->locked) {
            $notification = new notification(
                    get_string('lockwarning', 'tutorialbooking', $tutorialbooking->tstats),
                    notification::NOTIFY_WARNING
            );
            $notification->set_show_closebutton(false);
            echo $this->render($notification);
        }
    }

    /**
     * Create an admin link for the tutorialbooking_session page.
     *
     * @param moodle_url $url The URL for the link.
     * @param string $text The text to be displayed to the user.
     * @param string $icon The name of a tutorial booking icon.
     * @param string $classes Classes to be added to the link.
     * @return void
     */
    protected function editing_link(moodle_url $url, $text, $icon = null, $classes = '') {
        if (is_null($icon)) {
            $link = '['.$text.']';
        } else {
            $link = $this->pix_icon($icon, $text, 'mod_tutorialbooking');
        }
        echo html_writer::link($url, $link, array('taget' => '_blank', 'class' => $classes));
        echo '&nbsp;';
    }

    /**
     * Display the editing links for the tutorial.
     *
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @return void
     */
    protected function tutorial_editinglinks(tutorialbooking $tutorialbooking) {
        echo $this->box_start('controls');
        if ($tutorialbooking->editsignuplists) {
            $this->editing_link($tutorialbooking->urledittutorial, get_string('edittutorialprompt', 'tutorialbooking'));
            $this->editing_link($tutorialbooking->urldeletetutorial, get_string('deletetutorialprompt', 'tutorialbooking'));
            $this->editing_link($tutorialbooking->urladdslot, get_string('newtimslotprompt', 'tutorialbooking'));
            // Link to lock/unlock the tutorial without going into the settings.
            if ($tutorialbooking->tutorial->locked == true) {
                $linktext = get_string('locked', 'tutorialbooking');
            } else {
                $linktext = get_string('unlocked', 'tutorialbooking');
            }
            $this->editing_link($tutorialbooking->urllock, $linktext);
        }
        if ($tutorialbooking->editmessage) {
            // Link to let the user view e-mails they sent to students.
            $this->editing_link($tutorialbooking->urlviewmessages, get_string('viewmessages', 'tutorialbooking'));
        }
        if ($tutorialbooking->editexport) {
            if ($tutorialbooking->exportall) {
                // Link to show all tutorial bookings on the course.
                $this->editing_link($tutorialbooking->urlalltutorials, get_string('showalltutorialbookings', 'tutorialbooking'));
            }
            $this->editing_link($tutorialbooking->urlexport, get_string('exportlistprompt', 'tutorialbooking'));
            $this->editing_link($tutorialbooking->urlexportcsv, get_string('exportcsvlistprompt', 'tutorialbooking'));
        }
        echo $this->box_end();
    }

    /**
     * Renders a tutorial booking page, for either the tutorialbooking_sessions or view page.
     *
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @return void
     */
    public function render_tutorialbooking(tutorialbooking $tutorialbooking) {
        echo $this->header();
        echo $this->box_start('tutorial');
        $this->tutorial_information($tutorialbooking);

        // Editing links.
        if ($tutorialbooking->editing) {
            $this->tutorial_editinglinks($tutorialbooking);
            $this->editing_javascript();
        }

        echo $this->box_end();
        $this->render_sessions($tutorialbooking);
        echo $this->footer();
    }

    /**
     * Renders the signup slots for a tutorial booking.
     *
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @return void
     */
    public function render_sessions(tutorialbooking $tutorialbooking) {
        if ($tutorialbooking->totalsessions == 0) {
            // No slots. Tell the user.
            $notification = new notification(get_string('noslots', 'mod_tutorialbooking'), notification::NOTIFY_ERROR);
            $notification->set_show_closebutton(false);
            echo $this->render($notification);
            return;
        } else if ($tutorialbooking->totalsessions == 1) {
            $position = mod_tutorialbooking_session::POSITION_ONLY;
        } else {
            $position = mod_tutorialbooking_session::POSITION_FIRST; // Keep track of 1st poisition.
        }

        $sessioncount = 0;

        echo $this->box_start('tutorial_sessions', 'tutorial-'.$tutorialbooking->tutorial->id);
        foreach ($tutorialbooking->allsessions as $session) {
            $sessioncount++;
            $session->locked = $tutorialbooking->tutorial->locked; // This set the locking status.
            $session->courseid = $tutorialbooking->tutorial->course; // Save this in the structure.

            if ($sessioncount === $tutorialbooking->totalsessions && $tutorialbooking->totalsessions !== 1) {
                $position = mod_tutorialbooking_session::POSITION_LAST; // Last session when there is more than 1 session in total.
            }

            $this->render_session($tutorialbooking, $session, $position);
            $position = mod_tutorialbooking_session::POSITION_NEXT;
        }
        echo $this->box_end();
    }

    /**
     * Renders the name and stats for a session.
     *
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @param stdClass $session The session being rendered.
     * @return void
     */
    protected function session_information(tutorialbooking $tutorialbooking, $session) {
        echo $this->heading(format_text($session->description, $session->descformat), 3, 'sectionname');
        echo $this->output->box(format_text($session->summary, $session->summaryformat), 'summary');

        // Stats line.
        $spacestotal = $session->spaces;
        $spacestaken = 0;
        if (isset($tutorialbooking->attendees[$session->id])) {
            $spacestaken = $tutorialbooking->attendees[$session->id]['total'];
        }

        $spacesleft = $spacestotal - $spacestaken;

        if ($spacesleft < 0) { // The slot is oversubscribed.
            $messagetype = notification::NOTIFY_ERROR;
            $numbersline = get_string('numbersline_oversubscribed', 'tutorialbooking', array('total' => $spacestotal,
                'taken' => $spacestaken, 'left' => abs($spacesleft)));
        } else { // Is full or under subscribed.
            $messagetype = notification::NOTIFY_INFO;
            $numbersline = get_string('numbersline', 'tutorialbooking', array('total' => $spacestotal, 'taken' => $spacestaken,
                'left' => $spacesleft));
        }

        $notification = new notification($numbersline, $messagetype);
        $notification->set_show_closebutton(false);
        echo $this->render($notification);
    }

    /**
     * Displays the users that have been enrolled onto a session.
     *
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @param stdClass $session The session being rendered.
     * @param moodle_url $actionurl The base URL for the session.
     * @return void
     */
    protected function display_session_attendees(tutorialbooking $tutorialbooking, $session,
            moodle_url $actionurl) {
        // Attendees and waiting lists.
        echo $this->box_start('session_attendees');

        if ($tutorialbooking->tutorial->privacy == mod_tutorialbooking_tutorial::PRIVACY_SHOWSIGNUPS
                || $tutorialbooking->viewadmin) {
            if ($tutorialbooking->editing && $tutorialbooking->cfg->blockingon) {
                echo html_writer::tag('strong', get_string('blockuserprompt', 'tutorialbooking')) . '<br/>';
            } else {
                echo html_writer::tag('strong', get_string('attendees', 'tutorialbooking') . '<br/>');
            }
        }

        foreach ($tutorialbooking->attendees[$session->id]['signedup'] as $signup) {
            $this->display_signedup_user($tutorialbooking, $session, $signup, $actionurl);
        }

        echo $this->box_end();
    }

    /**
     * Displays information about the person who has signed up to a tutorial booking slot.
     *
     * @global stdClass $USER The logged in user of Moodle.
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @param stdClass $session The session being rendered.
     * @param mixed[] $signup An array of information about the signup being rendered.
     * @param moodle_url $actionurl The base URL for the session.
     * @return void
     */
    protected function display_signedup_user(tutorialbooking $tutorialbooking, $session, $signup,
            moodle_url $actionurl) {
        global $USER;
        if ($tutorialbooking->tutorial->privacy == mod_tutorialbooking_tutorial::PRIVACY_SHOWSIGNUPS
                || $tutorialbooking->viewadmin) {
            echo html_writer::start_span('signedupuser');
            if ($USER->id != $signup['uid']) {
                if ($tutorialbooking->editing && $tutorialbooking->cfg->blockingon) {
                    $url = $actionurl->params(array('action' => 'toggleblock', 'userid' => $signup['uid']));
                    echo html_writer::link($url, $signup['fname']) . ', &nbsp;';
                } else if ($tutorialbooking->canremoveusers && $tutorialbooking->editing) {
                    // The user is able to remove students from the slot.
                    $alttext = get_string('removeuserfromslot', 'tutorialbooking');
                    $url = new moodle_url($actionurl,
                            array('action' => 'removesignup', 'user' => $signup['uid']));
                    $deleteicon = $this->pix_icon('delete', $alttext, 'mod_tutorialbooking');
                    echo html_writer::link($url, $signup['fname'] . $deleteicon, array('title' => $alttext)) . ', &nbsp;';
                } else {
                    echo $signup['fname'] . ', &nbsp;';
                }
            } else {
                echo html_writer::tag('strong', get_string('you', 'tutorialbooking')) . ', &nbsp;';
            }
            echo html_writer::end_span();
        } else if ($tutorialbooking->tutorial->privacy == mod_tutorialbooking_tutorial::PRIVACY_SHOWOWN
                && $USER->id == $signup['uid']) {
            echo html_writer::tag('strong', get_string('yousignedup', 'tutorialbooking'));
        }
    }

    /**
     * Displays the administration controls for a session.
     *
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @param stdClass $session The session being rendered.
     * @param moodle_url $actionurl The base URL for the session.
     * @param int $position The position of the session, used to determine which move buttons should be displayed.
     * @return void
     */
    protected function session_edit_controls(tutorialbooking $tutorialbooking, $session,
            moodle_url $actionurl, $position) {
        if ($tutorialbooking->editsignuplists) {
            // Edit the session.
            $this->editing_link(new moodle_url($actionurl, array('action' => 'edit')),
                    get_string('editsession', 'tutorialbooking'));
            // Deleting.
            $this->editing_link(new moodle_url($actionurl, array('action' => 'deletesession')),
                    get_string('deletesession', 'tutorialbooking'));
        }

        if ($tutorialbooking->canaddstudent) {
            // Add a student.
            $this->editing_link(new moodle_url($actionurl, array('action' => 'addusers')),
                    get_string('addstudents', 'tutorialbooking'));
        }

        if ($tutorialbooking->editregisters) {
            // Register by name.
            $this->editing_link(
                    new moodle_url('tutorial_register.php', array('sessionid' => $session->id, 'courseid' => $session->courseid)),
                    get_string('registerprintname', 'tutorialbooking'));
            // Register by signup date.
            $this->editing_link(
                    new moodle_url('tutorial_register.php',
                            array(
                                'sessionid' => $session->id,
                                'courseid' => $session->courseid,
                                'format' => mod_tutorialbooking_register::ORDER_DATE)),
                    get_string('registerprintdate', 'tutorialbooking'));
        }
        if ($tutorialbooking->editmessage) {
            // Email attendees.
            $this->editing_link(new moodle_url($actionurl, array('action' => 'emailgroup')),
                    get_string('emailgroupprompt', 'tutorialbooking'));
        }

        if ($tutorialbooking->editsignuplists) {
            // Move - change sequence.
            if ($position != mod_tutorialbooking_session::POSITION_ONLY) {
                if ($position != mod_tutorialbooking_session::POSITION_FIRST) {
                    // Move up.
                    $this->editing_link(
                            new moodle_url($actionurl, array('action' => 'moveup', 'currentpos' => $session->sequence)),
                            get_string('moveupsession', 'tutorialbooking'),
                            'MoveUp',
                            'moveup');
                }
                if ($position != mod_tutorialbooking_session::POSITION_LAST) {
                    // Move down.
                    $this->editing_link(
                            new moodle_url($actionurl, array('action' => 'movedown', 'currentpos' => $session->sequence)),
                            get_string('movedownsession', 'tutorialbooking'),
                            'MoveDown',
                            'movedown');
                }
            }
        }
    }

    /**
     * Display signup/remove slef links to users who are not on an admin page.
     *
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @param stdClass $session The session being rendered.
     * @param moodle_url $actionurl The base URL for the session.
     * @return void
     */
    protected function session_nonediting_controls(tutorialbooking $tutorialbooking, $session,
            moodle_url $actionurl) {
        // Work out the number of free spaces.
        $spacestaken = 0;
        if (isset($tutorialbooking->attendees[$session->id])) {
            $spacestaken = $tutorialbooking->attendees[$session->id]['total'];
        }
        $spacesleft = $session->spaces - $spacestaken;

        if (isset($tutorialbooking->signupdetails->id) && $tutorialbooking->signupdetails->id) {
            // Only if we are not editing and signup exists.
            $signedup = true;
        } else {
            $signedup = false;
        }
        if (!$session->locked && $tutorialbooking->cansignup) {
            if (!$signedup) {
                if ($spacesleft > 0) {
                    // Sign up icon.
                    $url = new moodle_url($actionurl, array('action' => 'signup'));
                    echo html_writer::link($url, get_string('signupforslot', 'tutorialbooking'));
                }
            } else {
                if ($tutorialbooking->signupdetails->sessionid == $session->id) {
                    $url = new moodle_url($actionurl, array('action' => 'remove'));
                    echo html_writer::link($url, get_string('removefromslot', 'tutorialbooking'));
                }
            }
        }
    }

    /**
     * Renders an individual tutorial booking session.
     *
     * @param \mod_tutorialbooking\renderable\tutorialbooking $tutorialbooking The tutorial booking to be rendered.
     * @param stdClass $session The session being rendered.
     * @param int $position The position of the session, used to determine which move buttons should be displayed.
     * @return void
     */
    protected function render_session(tutorialbooking $tutorialbooking, $session, $position) {
        // Default capability permissions.
        $viewadmin = $tutorialbooking->viewadmin;
        $editsignuplists = $tutorialbooking->editsignuplists;
        $editmessage = $tutorialbooking->editmessage;
        $editregisters = $tutorialbooking->editregisters;
        $canaddstudent = $tutorialbooking->canaddstudent;
        $canremoveusers = $tutorialbooking->canremoveusers;

        // URL to be used for links.
        if ($tutorialbooking->editing) {
            $actionurl = $tutorialbooking->urlbase;
            $actionurl->params(array('id' => $session->id));
        } else {
            $actionurl = $tutorialbooking->urlbase;
            $actionurl->params(array('sessionid' => $session->id));
        }

        // The id is used by AJAX to identify the slots.
        echo $this->box_start('tutorial_session', 'slot-'.$session->id);
        $this->session_information($tutorialbooking, $session);

        if (!empty($tutorialbooking->attendees[$session->id])) {
            $this->display_session_attendees($tutorialbooking, $session, $actionurl);
        }

        echo $this->box_start('controls');
        if ($tutorialbooking->editing) {
            $this->session_edit_controls($tutorialbooking, $session, $actionurl, $position);
        } else {
            $this->session_nonediting_controls($tutorialbooking, $session, $actionurl);
        }
        echo $this->box_end();

        echo $this->box_end();
    }

    /**
     * Load a script that will tell the browser to print the page.
     * Used by the tutorial booking register page, which does not load the normal page stuff.
     *
     * @return void
     */
    private function javascript_force_print() {
        echo html_writer::tag('script', "<!--\nwindow.print();\n-->", array('type' => 'text/javascript'));
    }

    /**
     * Renders the register for a signup slot.
     *
     * @param html_table $register The register table to be printed.
     * @return void
     */
    public function render_register(html_table $register) {
        echo html_writer::table($register);
        $this->javascript_force_print();
    }

    /**
     * Javascript used on the editing pages.
     *
     * @global moodle_page $PAGE
     */
    public function editing_javascript() {
        global $PAGE;
        $PAGE->requires->js_call_amd('mod_tutorialbooking/dragdrop', 'init');
    }
}
