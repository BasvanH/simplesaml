<?php

Route::group([
    'prefix' => config('saml2_settings.routesPrefix'),
    'middleware' => config('saml2_settings.routesMiddleware'),
], function () {

    Route::get('/logout', array(
        'as' => 'saml_logout',
        'uses' => 'BasvanH\SimpleSaml\Controllers\SimpleSamlController@logout',
    ));

    Route::get('/login', array(
        'as' => 'saml_login',
        'uses' => 'BasvanH\SimpleSaml\Controllers\SimpleSamlController@login',
    ));

    Route::post('/acs', array(
        'as' => 'saml_acs',
        'uses' => 'BasvanH\SimpleSaml\Controllers\SimpleSamlController@acs',
    ));

    Route::get('/metadata', array(
        'as' => 'saml_metadata',
        'uses' => 'BasvanH\SimpleSaml\Controllers\SimpleSamlController@metadata',
    ));

    Route::get('/sls', array(
        'as' => 'saml_sls',
        'uses' => 'BasvanH\SimpleSaml\Controllers\SimpleSamlController@sls',
    ));
});