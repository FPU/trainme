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

namespace mod_tutorialbooking\renderable;

defined('MOODLE_INTERNAL') || die;

/**
 * Used by the tutorial booking renderer to display information about the tutorial.
 *
 * @package    mod_tutorialbooking
 * @copyright  2014 Nottingham University
 * @author     Neill Magill - neill.magill@nottingham.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tutorialbooking {
    /** @var stdClass[] $allsessions Stores information about the sessions for this tutorial booking activity. */
    public $allsessions;

    /** @var array $attendees Information about the state of everyone who has signed up to the tutorial booking. */
    public $attendees;

    /** @var bool Stores if the user can sign up to a tutorial booking activity. */
    public $cansignup = false;

    /** @var bool $canaddstudent Stores if the user is able to add students to a tutorial slot. */
    public $canaddstudent = false;

    /** @var bool $canremoveusers Stores if the user is able to remove students from a tutorial slot. */
    public $canremoveusers = false;

    /** @var stdClass $cfg Stored the config settings for the tutorialbooking plugin */
    public $cfg;

    /** @var bool $editing If true the tutorial booking activity is in admin mode. */
    public $editing;

    /** @var bool $editsignuplists Stores if the users should be able to edit signup lists. */
    public $editsignuplists = false;

    /** @var bool $editexport Stores if the user can export information from this tutorial booking activity. */
    public $editexport = false;

    /** @var bool $editmessage Stores if the the user can view messages sent by the activity. */
    public $editmessage = false;

    /** @var bool $editregisters Stores if the user is able to view and print registers. */
    public $editregisters = false;

    /** @var bool $exportall Stores if the user can export information for all tutorial bookings on the course. */
    public $exportall = false;

    /** @var stdClass|false $signupdetails Stores the database record of the slot the current user is signed up to. */
    public $signupdetails = false;

    /** @var int $totalsessions The total number of sessions on the tutorial booking. */
    public $totalsessions;

    /** @var int[][] $tstats An array of stats for each session. */
    public $tstats;

    /** @var stdClass $tutorial The tutorial booking database record. */
    public $tutorial;

    /** @var moodle_url $urladdslot The URL to add a new slot to this tutorial booking activity. */
    public $urladdslot;

    /** @var moodle_url $urlalltutorials The URL to see all the tutorials on the course. */
    public $urlalltutorials;

    /** @var moodle_url $urlbase The URL for the totorial booking page. */
    public $urlbase;

    /** @var moodle_url $urldeletetutorial The URL to delete this tutorial booking activity. */
    public $urldeletetutorial;

    /** @var moodle_url $urledittutorial The URL to edit this tutorial booking activities settings. */
    public $urledittutorial;

    /** @var moodle_url $urlexport The URL to generate an export file. */
    public $urlexport;

    /** @var moodle_url $urlexportcsv The URL to generate a csv export file. */
    public $urlexportcsv;

    /** @var moodle_url $urllock The URL to change the lock status of this tutorial booking. */
    public $urllock;

    /** @var moodle_url $urlviewmessages The URL to view messages sent by this tutorial booking activity. */
    public $urlviewmessages;

    /**
     * @var bool $viewadmin Stores if the user has the capability to edit.
     *                      If false many other permissions here will be set to false as well.
     */
    public $viewadmin = false;

    /**
     * The constructor gets all information needed to render a tutorial booking activity and checks a users permissions.
     *
     * @global moodle_page $PAGE Information about the current page.
     * @global moodle_database $DB The Moodle database connection object.
     * @global stdClass $USER The logged in Moodle user.
     * @param \stdClass $tutorial The database record of the tutorialbooking activity to be rendered.
     * @param bool $enableediting Should the user see the admin controls?
     *                            If true the user will be checked to see if they are able to access admin functionality,
     *                            if false the user will be assumed not to bbe able to do admin things.
     */
    public function __construct(\stdClass $tutorial, $enableediting = false) {
        global $PAGE, $DB, $USER;

        $this->tutorial = $tutorial;

        // Verify the privacy setting.
        switch($this->tutorial->privacy) { // Ensure there is a valid privacy value.
            case \mod_tutorialbooking_tutorial::PRIVACY_SHOWSIGNUPS:
            case \mod_tutorialbooking_tutorial::PRIVACY_SHOWOWN:
                // These are all valid so make no changes.
                break;
            default:
                // Default to show signups.
                $this->tutorial->privacy = \mod_tutorialbooking_tutorial::PRIVACY_SHOWSIGNUPS;
                break;
        }

        $this->cfg = get_config('tutorialbooking');
        $this->editing = $enableediting;

        $this->cansignup = has_capability('mod/tutorialbooking:submit', $PAGE->context);

        // Get information about the sessions.
        $this->allsessions = \mod_tutorialbooking_tutorial::gettutorialsessions($tutorial->id);
        $this->totalsessions = count($this->allsessions);
        $this->attendees = \mod_tutorialbooking_tutorial::gettutorialsignups($tutorial->id);
        $this->tstats = \mod_tutorialbooking_tutorial::gettutorialstats($this->allsessions, $this->attendees);

        $this->urlbase = new \moodle_url($PAGE->url);

        if ($enableediting) {
            // Required to edit.
            $this->viewadmin = has_capability('mod/tutorialbooking:viewadminpage', $PAGE->context);

            // Check and set permissions.
            $this->editsignuplists = has_capability('mod/tutorialbooking:editsignuplist', $PAGE->context) && $this->viewadmin;
            $this->editexport = has_capability('mod/tutorialbooking:export', $PAGE->context) && $this->viewadmin;
            $this->editmessage = has_capability('mod/tutorialbooking:message', $PAGE->context) && $this->viewadmin;
            $this->exportall = has_capability('mod/tutorialbooking:exportallcoursetutorials', $PAGE->context) && $this->viewadmin;

            // Session capabilities.
            $this->editregisters = has_capability('mod/tutorialbooking:printregisters', $PAGE->context) && $this->viewadmin;
            $this->canaddstudent = has_capability('mod/tutorialbooking:adduser', $PAGE->context) && $this->viewadmin;
            $this->canremoveusers = has_capability('mod/tutorialbooking:removeuser', $PAGE->context) && $this->viewadmin;

            // Generate link urls for the tutorial.
            $this->urledittutorial = new \moodle_url($PAGE->url, array('action' => 'edittutorial'));
            $this->urldeletetutorial = new \moodle_url($PAGE->url, array('action' => 'deletetutorial'));
            $this->urladdslot = new \moodle_url($PAGE->url, array('action' => 'edit', 'id' => 0));
            if ($tutorial->locked == true) {
                $this->urllock = new \moodle_url($PAGE->url, array('action' => 'unlock'));
            } else {
                $this->urllock = new \moodle_url($PAGE->url, array('action' => 'lock'));
            }
            $this->urlviewmessages = new \moodle_url($PAGE->url, array('action' => 'viewmessages'));
            $this->urlalltutorials = new \moodle_url('/mod/tutorialbooking/index.php', array('id' => $PAGE->course->id));
            $this->urlexport = new \moodle_url($PAGE->url, array('action' => 'export'));
            $this->urlexportcsv = new \moodle_url($PAGE->url, array('action' => 'exportcsv'));
        } else {
            // Just get your own details.
            $this->signupdetails = $DB->get_record('tutorialbooking_signups', array('userid' => $USER->id,
                'tutorialid' => $tutorial->id));
        }
    }
}
