<?php

require_once(__DIR__ . '/../../config.php');
require_login();

if (is_siteadmin()) {
    print_error('adminsarenotallowed', 'local_deletando');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/deletando/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_deletando'));
$PAGE->set_heading(get_string('pluginname', 'local_deletando'));

echo $OUTPUT->header();

echo $OUTPUT->box_start();

echo html_writer::tag('h3', get_string('confirmtitle', 'local_deletando'));

echo html_writer::tag('p', get_string('confirmmessage', 'local_deletando'));

$deleteurl = new moodle_url('/local/deletando/delete.php');
$cancelurl = new moodle_url('/');

echo $OUTPUT->single_button($deleteurl, get_string('deletebutton', 'local_deletando'), 'post');
echo $OUTPUT->single_button($cancelurl, get_string('cancelbutton', 'local_deletando'), 'get');

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
