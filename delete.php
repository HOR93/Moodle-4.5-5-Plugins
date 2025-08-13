<?php

require_once(__DIR__ . '/../../config.php');
require_login();

if (is_siteadmin()) {
    print_error('adminsarenotallowed', 'local_deletando');
}

require_sesskey();

require_once($CFG->dirroot . '/user/lib.php');

$user = $USER;

delete_user($user);

// Redireciona para logout com mensagem
redirect(new moodle_url('/login/logout.php'), get_string('deletedmessage', 'local_deletando'));

