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
 * Trax Video for Moodle.
 *
 * @package    mod_traxvideo
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_traxvideo_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018050801) {

        // Add poster and sourcemp4 column.
        $table = new xmldb_table('traxvideo');

        $field = new xmldb_field('poster', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'introformat');
        $dbman->add_field($table, $field);

        $field = new xmldb_field('sourcemp4', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'poster');
        $dbman->add_field($table, $field);

        // Savepoint.
        upgrade_mod_savepoint(true, 2018050801, 'traxvideo');
    }

    if ($oldversion < 2022030700) {
        $table = new xmldb_table('traxvideo');

        $field = new xmldb_field('display', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'sourcemp4');
        $dbman->add_field($table, $field);
        $field = new xmldb_field('displayoptions', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'display');
        $dbman->add_field($table, $field);
        $field = new xmldb_field('popupwidth', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'displayoptions');
        $dbman->add_field($table, $field);
        $field = new xmldb_field('popupheight', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'popupwidth');
        $dbman->add_field($table, $field);
    }

    if ($oldversion < 2022030701) {
         set_config('displayoptions', '0,1,3,5,6');
         set_config('display', '0');
         set_config('popupwidth', '620');
         set_config('popupheight', '450');
    }

    return true;
}

