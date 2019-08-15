## Laravel 5.5 - Saml2

This package offers Saml2 integration as a Service Provider. It uses [OneLogin](https://github.com/onelogin/php-saml) APIs to connect with IPD and retreive parsed data. The code was tested on Laravel Homestead Virtual machine with PHP 7.1 and Laravel 5.5.28.

## Installation

You can install this project using composer command

```
composer require BasvanH/simplesaml
```

## Laravel Configuration

You need to update the below code to execute this package

1. First make sure you run the `php artisan vendor:publish` command. This command will copy the `saml2_settings.php` file to config folder. 

2. Next, you want to update settings inside this folder or add environment variables to .env file for idp_host, sp_entityid, ipd_entityid, and idp_x509. Here are the sample settings:
```
    #SAML2 Settings
    SAML2_IDP_HOST=https://developer.oktapreview.com
    SAML2_SP_ENTITYID=myapp
    SAML2_IDP_URI="/saml2/idp/ssoservice.php"
    SAML2_IDP_ENTITYID=http://www.okta.com/exkd9nlyw4oshZ4U80h8
    SAML2_IDP_x509="..."
```
3. Update `config\app.php` with the following:
```php
    'aliases' => [
        ....
        'Saml2' => BasvanH\SimpleSaml\Facades\Saml2Auth::class,
    ];
    'providers' => [
        ....
        BasvanH\SimpleSaml\Providers\SimpleSamlServiceProvider::class,
    ];
```

4. Inside the `Kernel.php`, you would want to setup few things for saml to work as follows:
    Update middlewaregroup block:
```php    
        protected $middlewareGroups = [
            .....
            'saml2group' => [
                \App\Http\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ],
        ];
    Also, add the following line to the routeMiddleware block:
        protected $routeMiddleware = [
            ....
            'saml2' => \BasvanH\SimpleSaml\Middleware\Saml2Middleware::class,
        ];
```

5. Update `EventServiceProvider.php` with the following:
```php
    protected $listen = [
            ....
            'BasvanH\SimpleSaml\Events\Saml2LoginEvent' => [
                'App\Listeners\UserLoggedIn'],  
        ];
```

6. Finally, create Listener class inside /Listeners folder as follows:
```php
    <?php

namespace App\Listeners;

use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use BasvanH\SimpleSaml\Events\Saml2LoginEvent;

class UserLoggedIn
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Saml2LoginEvent  $event
     * @return void
     */
    public function handle(Saml2LoginEvent $event)
    {
        if (!$event->getSaml2Auth()->isAuthenticated()) {
            Log::info('The user is not authenticated');
            return redirect(config('saml2_settings.logoutRoute'));
        }

        $samlUser = $event->getSaml2User();
       
        $attributes = $samlUser->getAttributes();
        
        //check if email already exists and fetch user
        $user = \App\User::where('email', $attributes['email'][0])->first();
        
        //if email doesn't exist, create new user
		if ($user === null)
		{		
			$user = new \App\User;
            $user->email = $attributes['email'][0];
            $user->firstname = $attributes['firstname'][0];
            $user->lastname = $attributes['lastname'][0];
			$user->save();
		}

        if (count($attributes) >= 4) {
            //Add values to PHP and Laravel Session
            session()->put('email', $attributes['email'][0]);
            session()->put('firstname', $attributes['firstname'][0]);
            session()->put('lastname', $attributes['lastname'][0]);
            
            //The below block is useful if your application host both laravel and non-larvel code in one domain.
            session_start();
            $_SESSION['email'] = $user->email;
            $_SESSION['shortname'] = $user->shortname;
            $_SESSION['firstname'] = $user->firstname;
            $_SESSION['lastname'] = $user->lastname;
        }
        
        session()->save();

        Auth::login($user, true);
    }
}
```
### Credits: This project was based on aacotrnoeo/laravel-saml2 package.