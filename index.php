<?php
require __DIR__.'/config.php';
if (user()) redirect(home(user()['role']));
redirect('login.php');
