<?php

namespace BasvanH\SimpleSaml\Providers;

use Illuminate\Auth\UserProviderInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class Saml2UserProvider implements UserProviderInterface {
    /**
     * @var Connection
     */
    private $connection;

    public function retrieveById($identifier) {

    }
    
    public function retrieveByToken($identifier, $token) {

    }
    
    public function updateRememberToken(Authenticatable $user, $token) {

    }

    public function retrieveByCredentials(array $credentials) {

    }

    public function validateCredentials(Authenticatable $user, array $credentials) {
        
    }
}