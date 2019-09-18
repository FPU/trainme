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
 * Stores information related to dragging and dropping.
 *
 * @module     mod_tutorialbooking/dragdrop-datastore
 * @package    mod_tutorialbooking
 * @copyright  2018 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    /** @var {string|null} dragged Stores the id of the element being dragged. */
    var dragged = null;
    /** @var {string|null} target Stores the id of the element being dropped into. */
    var target = null;
    /** @var {core/modal|null} target Stores the active keyboard menu instance. */
    var keyboardmenu = null;

    /**
     * Clear all of the stored data.
     *
     * @returns {undefined}
     */
    var clearAll = function() {
        this.setDragged(null);
        this.setKeyboardMenu(null);
        this.setTarget(null);
    };

    /**
     * Gets the id of the element being dragged.
     *
     * @returns {string|null}
     */
    var getDragged = function() {
        return dragged;
    };

    /**
     * Gets the active keyboard menu.
     *
     * @returns {core/modal|null}
     */
    var getKeyboardMenu = function() {
        return keyboardmenu;
    };

    /**
     * Gets the id of the element that is the target of the drop.
     *
     * @returns {string|null}
     */
    var getTarget = function() {
        return target;
    };

    /**
     * Stores the id of the item being dragged.
     *
     * @param {string|null} id
     * @returns {undefined}
     */
    var setDragged = function(id) {
        dragged = id;
    };

    /**
     * Sets the active keyboard menu.
     *
     * @param {core/modal|null} menu
     * @returns {undefined}
     */
    var setKeyboardMenu = function(menu) {
        keyboardmenu = menu;
    };

    /**
     * Stores the id of the element that is the target of the drop.
     *
     * @param {string|null} id
     * @returns {undefined}
     */
    var setTarget = function(id) {
        target = id;
    };

    return {
        clearAll: clearAll,
        getDragged: getDragged,
        getKeyboardMenu: getKeyboardMenu,
        getTarget: getTarget,
        setDragged: setDragged,
        setKeyboardMenu: setKeyboardMenu,
        setTarget: setTarget
    };
});

