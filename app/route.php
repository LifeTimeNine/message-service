<?php

use main\Route;

Route::get('auth/websocket', 'Auth@websocket', false);

Route::resource('message', 'Message');

// Route::resource('tag', 'Tag');

Route::rule('/', 'Index@index');