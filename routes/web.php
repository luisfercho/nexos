<?php

Route::get("/emails/send/test",'EmailController@index')->name("Emailstest");

Route::get('/{any}', 'SpaController@index')->where('any', '.*')->name("index");