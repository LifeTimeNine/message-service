<?php

use main\Route;

Route::get('auth/websocket', 'Auth@websocket', false);

Route::resource('message', 'Message');

Route::post('tag', 'Tag@save');
Route::delete('tag', 'Tag@delete');
Route::get('tag/:uid', 'Tag@read');

Route::resource('user', 'User');

Route::rule('/', 'Index@index');