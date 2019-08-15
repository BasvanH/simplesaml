<?php

namespace BasvanH\SimpleSaml\Controllers;

use Illuminate\Http\Request;
use BasvanH\SimpleSaml\Saml2Auth;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use BasvanH\SimpleSaml\Events\Saml2LoginEvent;


class SimpleSamlController extends Controller {

    protected $saml2Auth;

    function __construct(Saml2Auth $saml2Auth)
    {
        $this->saml2Auth = $saml2Auth;
    }

    public function login(Request $request)
    {
        $this->saml2Auth->login(config('saml2_settings.loginRoute'));
    }

    public function logout(Request $request)
    {
        $returnTo = $request->query('returnTo');
        $sessionIndex = $request->query('sessionIndex');
        $nameId = $request->query('nameId');
        $this->saml2Auth->logout($returnTo, $nameId, $sessionIndex); //will actually end up in the sls endpoint 
    }

    /**
     * Generate local sp metadata
     * @return \Illuminate\Http\Response
     */
    public function metadata()
    {
        $metadata = $this->saml2Auth->getMetadata();
        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process an incoming saml2 assertion request.
     * Fires 'Saml2LoginEvent' event if a valid user is Found
     */
    public function acs(Request $request)
    {
        $errors = $this->saml2Auth->acs();
        if (!empty($errors)) {
            logger()->error('Saml2 error_detail', ['error' => $this->saml2Auth->getLastErrorReason()]);
            session()->flash('saml2_error_detail', [$this->saml2Auth->getLastErrorReason()]);
            logger()->error('Saml2 error', $errors);
            session()->flash('saml2_error', $errors);
            return redirect(config('saml2_settings.errorRoute'));
        }
        $user = $this->saml2Auth->getSaml2User();
        event(new Saml2LoginEvent($user, $this->saml2Auth));

        $redirectUrl = $user->getIntendedUrl();
        
        if (!isset($_SESSION['email'])) {
            session_start();
        }
        //The below code will redirect the user to the original request page url. 
        if (isset($_SESSION['REQUEST_URI']) && '/' != $_SESSION['REQUEST_URI']) {
            $redirectUrl = $_SESSION['REQUEST_URI'];
        }

        if ($redirectUrl !== null) {
            return redirect($redirectUrl);
        } else {
            return redirect(config('saml2_settings.loginRoute'));
        }
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     */
    public function sls()
    {
        $error = $this->saml2Auth->sls(config('saml2_settings.retrieveParametersFromServer'));
        if (!empty($error)) {
            throw new \Exception("Could not log out");
        }
        return redirect(config('saml2_settings.logoutRoute')); //may be set a configurable default
    }
}