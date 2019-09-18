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
 * Tutorial booking unit tests.
 *
 * @package     mod_tutorialbooking
 * @category    test
 * @copyright   2016 University of Nottingham
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');

/**
 * Provides the unit tests for tutorial booking search.
 *
 * @package     mod_tutorialbooking
 * @category    test
 * @copyright   2016 University of Nottingham
 * @author      Neill Magill <neill.magill@nottingham.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group mod_tutorialbooking
 * @group uon
 */
class mod_tutorialbooking_search_testcase extends advanced_testcase {
    /**
     * @var string Area id
     */
    protected $tutorialareaid = null;

    /**
     * Ensure global search is on and that tutorial booking search is enabled.
     */
    public function setUp() {
        $this->resetAfterTest(true);
        set_config('enableglobalsearch', true);

        $this->tutorialareaid = \core_search\manager::generate_areaid('mod_tutorialbooking', 'session');

        // Set \core_search::instance to the mock_search_engine as we don't require the search engine to be working to test this.
        $search = testable_core_search::instance();
    }

    /**
     * Availability.
     *
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_search_enabled() {
        $searcharea = \core_search\manager::get_search_area($this->tutorialareaid);
        list($componentname, $varname) = $searcharea->get_config_var_name();

        // Enabled by default once global search is enabled.
        $this->assertTrue($searcharea->is_enabled());

        set_config($varname . '_enabled', false, $componentname);
        $this->assertFalse($searcharea->is_enabled());

        set_config($varname . '_enabled', true, $componentname);
        $this->assertTrue($searcharea->is_enabled());
    }

    /**
     * Indexing tutorial booking slots.
     *
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_session_indexing() {
        global $DB;

        // Returns the instance as long as the area is supported.
        $searcharea = \core_search\manager::get_search_area($this->tutorialareaid);
        $this->assertInstanceOf('\mod_tutorialbooking\search\session', $searcharea);

        $course1 = self::getDataGenerator()->create_course();
        $tutorial = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course1->id));

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_tutorialbooking');
        $generator->add_slot($tutorial, array('description' => 'Boom!', 'summary' => 'Headshot'));
        $generator->add_slot($tutorial, array('description' => 'This is a double whammy', 'summary' => 'so I cried'));

        // All records.
        $recordset = $searcharea->get_document_recordset(0);
        $this->assertTrue($recordset->valid());
        $nrecords = 0;
        foreach ($recordset as $record) {
            $this->assertInstanceOf('stdClass', $record);
            $doc = $searcharea->get_document($record);
            $this->assertInstanceOf('\core_search\document', $doc);

            // Static caches are working.
            $dbreads = $DB->perf_get_reads();
            $doc = $searcharea->get_document($record);
            $this->assertEquals($dbreads, $DB->perf_get_reads());
            $this->assertInstanceOf('\core_search\document', $doc);
            $nrecords++;
        }
        // If there would be an error/failure in the foreach above the recordset would be closed on shutdown.
        $recordset->close();
        $this->assertEquals(2, $nrecords);

        // The +2 is to prevent race conditions.
        $recordset2 = $searcharea->get_document_recordset(time() + 2);

        // No new records.
        $this->assertFalse($recordset2->valid());
        $recordset2->close();
    }

    /**
     * Document contents.
     *
     * @group mod_tutorialbooking
     * @group uon
     */
    public function test_check_access() {
        global $DB;

        // Returns the instance as long as the area is supported.
        $searcharea = \core_search\manager::get_search_area($this->tutorialareaid);
        $this->assertInstanceOf('\mod_tutorialbooking\search\session', $searcharea);

        $user1 = self::getDataGenerator()->create_user();
        $course1 = self::getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        $tutorial1 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course1->id, 'visible' => 1));
        $tutorial2 = $this->getDataGenerator()->create_module('tutorialbooking', array('course' => $course1->id, 'visible' => 0));
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_tutorialbooking');

        $details = array('description' => 'Boom!', 'summary' => 'Headshot');
        $slot1 = $generator->add_slot($tutorial1, $details);
        $slot2 = $generator->add_slot($tutorial2, $details);

        $this->setAdminUser();
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($slot1->id));
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($slot2->id));

        $this->setUser($user1);
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($slot1->id));
        $this->assertEquals(\core_search\manager::ACCESS_DENIED, $searcharea->check_access($slot2->id));

        // Ensure that the slot id cannot exist.
        $this->assertEquals(\core_search\manager::ACCESS_DELETED, $searcharea->check_access($slot1->id + $slot2->id));
    }
}
