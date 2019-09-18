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
 * A javascript module that allows drag and drop.
 *
 * We should check Moodle core for jQuery based drag and drop modules during every upgrade.
 * I will try to get a version of this into core Moodle, or someone else will make something
 * that will replace the old YUI drag and drop module. When that occurs this module should
 * be redundant.
 *
 * @module     mod_tutorialbooking/dragdrop
 * @package    mod_tutorialbooking
 * @copyright  2018 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'core/templates', 'core/notification', 'core/str', 'core/modal_factory',
        'mod_tutorialbooking/dragdrop-datastore'],
    function($, log, template, notification, str, modal, data) {
    /** @type {Object} Stores CSS classes that will be attached to items. */
    var CSS = {
        /** @var {String} Defines css added to an element being dragged. */
        DRAGGED: 'dimmed',
        /** @var {String} Defines css to be added to a. */
        DRAGOVER: 'well',
        /** @var {string} Defines the class of the drag handler added to the page. */
        HANDLE: 'draghandle'
    };

    /** @type {Object} Stores CSS selectors for elements we care about. */
    var SELECTORS = {
        /** @var {string} Selector for the node that will store drag and drop data. */
        DATANODE: '.datanode',
        /** @var {String} Selector for the elements that can be dragged. */
        DRAGGABLE: '.draggable',
        /** @var {string} Selector for the area that contains the dragable elements. */
        DRAGAREA: '.dragarea',
        /** @var {string} Selector for the areas that the dragged element can be dropped into. */
        DROPTARGET: '.droppable',
        /** @var {string} Selector for the element that the drag handle is attached to. */
        HANDLEELEMENT: '.draggable .handle',
    };

    /** @type {object} Stores the string and icon for the drag handler. */
    var HANDLE = {
        ICON: {
            name: 'i/move_2d',
            component: 'core'
        },
        STRING: {
            name: 'moveslot',
            component: 'mod_tutorialbooking'
        }
    };

    /**
     * Attaches the menu to the page.
     *
     * @param {core/modal} menu
     * @returns {undefined}
     */
    var attachMenu = function(menu) {
        data.setKeyboardMenu(menu);
        menu.getRoot().on('click', 'a', globalKeyboardDrop);
        menu.getRoot().on('keydown', 'a', globalKeyboardDrop);
        menu.show();
        log.debug('Menu attached', 'mod_tutorialbooking/dragdrop-base');
        return;
    };

    /**
     * Closes the keyboard movement menu.
     *
     * @returns {undefined}
     */
    var closeMoveMenu = function() {
        log.debug('Close menu', 'mod_tutorialbooking/dragdrop-base');
        var menu = data.getKeyboardMenu();
        menu.getRoot().off('click', 'a', globalKeyboardDrop);
        menu.getRoot().off('keydown', 'a', globalKeyboardDrop);
        menu.hide();
        data.setKeyboardMenu(null);
        menu.destroy();
    };

    /**
     * Generates the handle for the item.
     *
     * @returns {Promise}
     */
    var createHandles = function() {
        // Make the cursor change to a move icon on the handler element.
        var handleelement = this.SELECTORS.HANDLEELEMENT;
        var classes = this.CSS.HANDLE;
        $(handleelement).css('cursor', 'move');
        var icon = this.HANDLE.ICON;
        return str.get_string(this.HANDLE.STRING.name, this.HANDLE.STRING.component).then(function(string) {
            return template.render('mod_tutorialbooking/dragicon', {icon: icon, text: string, classes: classes});
        }).then(function(html) {
            $(handleelement).prepend($(html));
            $('img', handleelement).css('cursor', 'move');
        }).fail(notification.exception);
    };

    /**
     * Creates a keyboard drag and drop menu.
     *
     * We will have a variable number of arguments passed to the function.
     * The first item is always the title string, the rest are the link objects,
     * we need to put these into an array that is sent to the template.
     *
     * @param {string} title The title of the menu.
     * @returns {Promise}
     */
    var createMenu = function(title) {
        var links = Array.prototype.slice.call(arguments, 1);
        var body = template.render('mod_tutorialbooking/keyboardmenu', {links: links});
        return modal.create({
            title: title,
            body: body
        });
    };

    /**
     * Handles a user click on the handle.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var globalClick = function (e) {
        var handle = $(e.target).closest(e.data.selectors.HANDLEELEMENT + ' .' + e.data.css.HANDLE);
        if (handle.length === 0) {
            return;
        }
        openMoveMenu(e);
    };

    /**
     * Called when a drag is ended.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var globalDragEnd = function(e) {
        log.debug('Drag ended', 'mod_tutorialbooking/dragdrop-base');
        var dragged = $('#' + data.getDragged());
        dragged.removeClass(e.data.css.DRAGGED);
        dragged.each(makeDraggable);
        $(e.data.selectors.DRAGGABLE).removeClass(e.data.css.DRAGOVER);
        e.data.handler.dragEnd(e);
    };

    /**
     * Handled dragleave events.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var globalDragLeave = function(e) {
        var droptarget = $(e.target).closest(e.data.selectors.DROPTARGET);
        droptarget.removeClass(e.data.css.DRAGOVER);
        e.data.handler.dragLeave(e);
    };

    /**
     * Called when a dragged object is over a target.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var globalDragOver = function(e) {
        var droptarget = $(e.target).closest(e.data.selectors.DROPTARGET);
        e.preventDefault();
        droptarget.addClass(e.data.css.DRAGOVER);
        e.data.handler.dragOver(e);
    };

    /**
     * Called when a drag is started.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var globalDragStart = function(e) {
        var draggable = $(e.target).closest(e.data.selectors.DRAGGABLE);
        log.debug('Drag started', 'mod_tutorialbooking/dragdrop-base');
        // Ensure we start with a clean data object.
        data.clearAll();
        // Save the id of the element being dragged.
        data.setDragged(draggable.attr('id'));
        draggable.each(removeDraggable);
        draggable.addClass(e.data.css.DRAGGED);
        e.data.handler.dragStart(e);
    };

    /**
     * Handles drop events.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var globalDrop = function(e) {
        var droptarget = $(e.target).closest(e.data.selectors.DROPTARGET);
        log.debug('Dropped element', 'mod_tutorialbooking/dragdrop-base');
        e.preventDefault();
        data.setTarget(droptarget.attr('id'));
        e.data.handler.drop(e);
    };

    /**
     * Handles keyboard navigation, and single clicks on the handle.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var globalKeyDown = function(e) {
        if (e.which !== 13 && e.which !== 32) {
            // Enter or space was not pressed.
            return;
        }
        openMoveMenu(e);
    };

    /**
     * Handles dropping via the keyboard.
     *
     * @param {Event} e
     * @returns {undefined}
     */
    var globalKeyboardDrop = function(e) {
        if (e.which === 27) {
            // Escape was pressed, treat this as a cancel.
            closeMoveMenu();
            return;
        }
        var link = $(e.target).closest('.dragdrop-keyboard-drag a');
        if (link.length === 0) {
            // A link was not clicked on.
            return;
        }
        if (e.which !== 13 && e.which !== 32 && e.type !== 'click') {
            // Not a valid key press.
            return;
        }
        data.setTarget($(e.target).data('drop-target'));
        log.debug('Menu link activated for ' + data.getTarget(), 'mod_tutorialbooking/dragdrop-base');
        closeMoveMenu();
        // Simulate a drop event.
        $('#' + data.getTarget()).trigger('drop');
    };

    /**
     * Sets up the page for drag and dropping.
     *
     * @returns {undefined}
     */
    var init = function() {
        log.debug('Setting up', 'mod_tutorialbooking/dragdrop-base');
        // Add the move icon.
        this.createHandles();
        // Set up listeners.
        var dragarea = $(this.SELECTORS.DRAGAREA);
        var eventdata = {
            css: this.CSS,
            selectors: this.SELECTORS,
            handler: this
        };
        dragarea.on('dragend', eventdata, this.globalDragEnd);
        dragarea.on('dragleave', this.SELECTORS.DROPTARGET, eventdata, this.globalDragLeave);
        dragarea.on('dragover', this.SELECTORS.DROPTARGET, eventdata, this.globalDragOver);
        dragarea.on('dragstart', this.SELECTORS.DRAGGABLE, eventdata, this.globalDragStart);
        dragarea.on('drop', this.SELECTORS.DROPTARGET, eventdata, this.globalDrop);
        dragarea.on('keydown', this.SELECTORS.HANDLEELEMENT + ' .' + this.CSS.HANDLE, eventdata, this.globalKeyDown);
        dragarea.on('click', this.SELECTORS.HANDLEELEMENT + ' .' + this.CSS.HANDLE, eventdata, this.globalClick);
        // Set the draggables.
        $(this.SELECTORS.DRAGGABLE).each(makeDraggable);
        log.debug('Setup completed', 'mod_tutorialbooking/dragdrop-base');
    };

    /**
     * Callback to make an element draggable.
     *
     * @param {Number} key
     * @param {DOMElement} element
     * @returns {undefined}
     */
    var makeDraggable = function(key, element) {
        $(element).attr('draggable', 'true');
    };

    /**
     * A place holder for methods that can be overriden by extending classes.
     *
     * An event will be passed as the first argument of this method.
     *
     * @returns {undefined}
     */
    var nullEventHandler = function() {};

    /**
     * Opens a keyboard move menu for the drag element.
     *
     * @param {type} e
     * @returns {undefined}
     */
    var openMoveMenu = function(e) {
        log.debug('Open menu', 'mod_tutorialbooking/dragdrop-base');
        // Ensure we start with a clean data object.
        data.clearAll();
        var promises = [];
        // Store the draggable element that was clicked on.
        var thisdraggable = $(e.target).closest(e.data.selectors.DRAGGABLE).first();
        data.setDragged(thisdraggable.attr('id'));
        // Generate the title of the menu.
        var draggedText = $(e.target).closest(e.data.selectors.HANDLEELEMENT).first().text();
        promises.push(str.get_string('movecontent', 'core', draggedText));
        // The details of the string that should be used for each link in the menu.
        var name = 'tocontent';
        var component = 'core';
        // Loop through all of the handles.
        $(e.data.selectors.HANDLEELEMENT).each(function(key, element) {
            // Get the draggable elment for this handle.
            var target = $(element).closest(e.data.selectors.DRAGGABLE);
            if (target.is(thisdraggable)) {
                // Do not include the clicked element.
                return;
            }
            // Get the text for the menu item for this possible target, and it's id.
            var promise = str.get_string(name, component, $(element).text()).then(function(string) {
                return {
                    name: string,
                    target: target.attr('id')
                };
            });
            promises.push(promise);
        });
        // Generate the modal.
        var wait = $.when.apply(this, promises);
        wait.then(createMenu).then(attachMenu);
    };

    /**
     * Stops an element being draggable.
     *
     * @param {Number} key
     * @param {DOMElement} element
     * @returns {undefined}
     */
    var removeDraggable = function(key, element) {
         $(element).attr('draggable', null);
    };

    return {
        // Override these variables to configure drag and dropping..
        CSS: CSS,
        SELECTORS: SELECTORS,
        HANDLE: HANDLE,
        // Do not override these.
        createHandles: createHandles,
        globalClick: globalClick,
        globalDragEnd: globalDragEnd,
        globalDragLeave: globalDragLeave,
        globalDragOver: globalDragOver,
        globalDragStart: globalDragStart,
        globalDrop: globalDrop,
        globalKeyDown: globalKeyDown,
        globalSetup: init,
        // Override these methods to action change the behaviour of dragging and dropping.
        dragEnd: nullEventHandler,
        dragLeave: nullEventHandler,
        dragOver: nullEventHandler,
        dragStart: nullEventHandler,
        drop: nullEventHandler
    };
});
