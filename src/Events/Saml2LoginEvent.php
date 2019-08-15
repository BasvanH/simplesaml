<?php

namespace BasvanH\SimpleSaml\Events;

use BasvanH\SimpleSaml\Saml2User;
use BasvanH\SimpleSaml\Saml2Auth;

class Saml2LoginEvent {

    protected $user;
    protected $auth;

    function __construct(Saml2User $user, Saml2Auth $auth)
    {
        $this->user = $user;
        $this->auth = $auth;
    }

    public function getSaml2User()
    {
        return $this->user;
    }

    public function getSaml2Auth()
    {
        return $this->auth;
    }

}
