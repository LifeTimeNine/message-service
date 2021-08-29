<?php

use main\Route;

Route::get('auth/websocket', 'Auth@websocket', false);

Route::resource('message', 'Message');

Route::post('tag', 'Tag@save');
Route::delete('tag', 'Tag@delete');

Route::rule('/', 'Index@index');