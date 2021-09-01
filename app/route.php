<?php

use main\Route;

Route::get('auth/websocket', 'Auth@websocket');

Route::resource('message', 'Message', true);

Route::post('tag', 'Tag@save', true);
Route::delete('tag', 'Tag@delete', true);
Route::get('tag/:uid', 'Tag@read', true);

Route::resource('user', 'User', true);

Route::get('/', 'Index@index');