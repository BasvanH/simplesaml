<?php

namespace BasvanH\SimpleSaml\Providers;

use \OneLogin\Saml2\Auth;
use URL;

use Illuminate\Support\ServiceProvider;

class SimpleSamlServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/saml2_settings.php' => config_path('saml2_settings.php'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
        
        if (config('saml2_settings.proxyVars', false)) {
            \OneLogin\Saml2\Auth ::setProxyVars(true);
        }

        if (!defined('ONELOGIN_CUSTOMPATH')) {
            define('ONELOGIN_CUSTOMPATH', config_path('saml2_settings.php'));
        }
    }


    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->registerOneLoginInContainer();
        $this->app->singleton('Aacotroneo\Saml2\Saml2Auth', function ($app) {
            return new \BasvanH\SimpleSaml\Saml2Auth($app['\OneLogin\Saml2\Auth']);
        });
    }

    /**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
    }
    
    protected function registerOneLoginInContainer()
    {
        $this->app->singleton('OneLogin\Saml2\Auth', function ($app) {
            $config = config('saml2_settings');
            if (empty($config['sp']['entityId'])) {
                $config['sp']['entityId'] = URL::route('saml_metadata');
            }
            if (empty($config['sp']['assertionConsumerService']['url'])) {
                $config['sp']['assertionConsumerService']['url'] = URL::route('saml_acs');
            }
            if (!empty($config['sp']['singleLogoutService']) &&
                empty($config['sp']['singleLogoutService']['url'])) {
                $config['sp']['singleLogoutService']['url'] = URL::route('saml_sls');
            }
            if (strpos($config['sp']['privateKey'], 'file://')===0) {
                $config['sp']['privateKey'] = $this->extractPkeyFromFile($config['sp']['privateKey']);
            }
            if (strpos($config['sp']['x509cert'], 'file://')===0) {
                $config['sp']['x509cert'] = $this->extractCertFromFile($config['sp']['x509cert']);
            }
            if (strpos($config['idp']['x509cert'], 'file://')===0) {
                $config['idp']['x509cert'] = $this->extractCertFromFile($config['idp']['x509cert']);
            }
            return new \OneLogin\Saml2\Auth($config);
        });
    }
}