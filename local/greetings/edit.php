<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Disply greetings plugin page.
 *
 * @package     local_greetings
 * @copyright   2022 Michael Pound <michael@brickfieldlabs.ie>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/edit_form.php');

require_login();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/greetings/edit.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'local_greetings'));
$PAGE->set_title($SITE->fullname);

$id = required_param('id', PARAM_TEXT);


$message = $DB->get_record('local_greetings_messages', array('id' => $id), '*', MUST_EXIST);

$customdata = [
    'message' => $message->message
];

$editform = new local_greetings_edit_form((new moodle_url('/local/greetings/edit.php?id=' . $id))->out(false), $customdata);

echo $OUTPUT->header();

if (has_capability('local/greetings:postmessages', $context)) {
    $editform->display();
}

if ($editform->is_cancelled()) {

    redirect(new moodle_url('/local/greetings/index.php'));

} else if ($data = $editform->get_data()) {
    require_capability('local/greetings:postmessages', $context);

    $newmessage = required_param('newmessage', PARAM_TEXT);

    if (!empty($newmessage)) {
        $message->message = $newmessage;

        $DB->update_record('local_greetings_messages', $message);

        redirect(new moodle_url('/local/greetings/index.php'));
    }
}

echo $OUTPUT->footer();
