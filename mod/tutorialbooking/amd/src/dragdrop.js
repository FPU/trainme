// This file is part of the tutorial booking activity plugin.
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
 * A javascript module that allows tutorial booking slots to
 * be moved by dragging and dropping them.
 *
 * @module     mod_tutorialbooking/dragdrop
 * @package    mod_tutorialbooking
 * @copyright  2018 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'mod_tutorialbooking/dragdrop-base', 'core/notification', 'core/ajax',
        'mod_tutorialbooking/dragdrop-datastore'],
    function($, log, dragdrop, notification, ajax, data) {
    var SELECTORS = {
        DATANODE: '.tutorial_sessions',
        DRAGGABLE: '.tutorial_session',
        DRAGAREA: '.tutorial_sessions',
        DROPTARGET: '.tutorial_session[draggable=true]',
        HANDLEELEMENT: '.tutorial_session .sectionname',
        MOVEDOWNCONTROL: '.tutorial_session .controls .movedown',
        MOVEUPCONTROL: '.tutorial_session .controls .moveup',
    };

    var HANDLE = {
        STRING: {
            name: 'moveslot',
            component: 'mod_tutorialbooking'
        }
    };

    /**
     * Sets up the tutorial booking slots so that they can be dragged and dropped.
     *
     * @returns {undefined}
     */
    var init = function() {
        log.debug('Setting up', 'mod_tutorialbooking/dragdrop');
        // Remove the move controls.
        $(this.SELECTORS.MOVEUPCONTROL).remove();
        $(this.SELECTORS.MOVEDOWNCONTROL).remove();
        this.globalSetup();
        log.debug('Setup completed', 'mod_tutorialbooking/dragdrop');
    };

    /**
     * Handles a drop event.
     *
     * Moves the slot to it's new position.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var drop = function(e) {
        log.debug('Dropped element', 'mod_tutorialbooking/dragdrop');
        // Find the element being dragged.
        var tutorial = $(e.target).closest(e.data.selectors.DRAGAREA);
        // Make the AJAX call to move the slot.
        var moveslot = {
            methodname: 'mod_tutorialbooking_moveslot',
            args: {
                tutorial: parse_tutorial_id(tutorial),
                slot: parse_slot_id(data.getDragged()),
                target: parse_slot_id(data.getTarget())
            }
        };
        var calls = ajax.call([moveslot]);
        calls[0].done(function(response) {
            if (response.success) {
                // Move the slot.
                if (response.where === 'before') {
                    var message = 'Slot ' + moveslot.args.slot + ' moved before slot' + moveslot.args.target;
                    log.debug(message, 'mod_tutorialbooking/dragdrop');
                    $('#' + data.getTarget()).before($('#' + data.getDragged()));
                } else {
                    var message = 'Slot ' + moveslot.args.slot + ' moved after slot' + moveslot.args.target;
                    log.debug(message, 'mod_tutorialbooking/dragdrop');
                    $('#' + data.getTarget()).after($('#' + data.getDragged()));
                }
            }
        }).fail(notification.exception);
    };

    /**
     * Gets the id of the tutorial based on it's css id.
     *
     * They will always have a css id in the form: tutorial-<number>
     *
     * @param {DOMElement} element
     * @returns {Number}
     */
    var parse_tutorial_id = function(element) {
        return $(element).attr('id').substr(9);
    };

    /**
     * Gets the id of the slot based on it's css id.
     *
     * They will always have a css id in the form: slot-<number>
     *
     * @param {string} id
     * @returns {Number}
     */
    var parse_slot_id = function(id) {
        return id.substr(5);
    };

    /** @type {Object} Holds the methods that will override the base drag drop object. */
    var overrides = {
        SELECTORS: $.extend({}, dragdrop.SELECTORS, SELECTORS),
        HANDLE: $.extend({}, dragdrop.HANDLE, HANDLE),
        drop: drop,
        init: init
    };

    // Returns an extended drag and drop method.
    return $.extend({}, dragdrop, overrides);
});
