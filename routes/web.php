<?php

Route::get('/{any}', 'SpaController@index')->where('any', '.*')->name("index");