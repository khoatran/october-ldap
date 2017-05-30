<?php namespace KhoaTD\LDAP;

use Adldap\Laravel\AdldapServiceProvider;
use Adldap\Laravel\Facades\Adldap;
use App;
use Backend\Controllers\Users;
use KhoaTD\LDAP\Facades\LDAPBackendAuth;
use KhoaTD\LDAP\Services\LDAPAuthManager;
use Illuminate\Foundation\AliasLoader;
use October\Rain\Support\Facades\Flash;
use System\Classes\PluginBase;
use Event;
use Session;
class Plugin extends PluginBase
{
    public $elevated = true;
    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        App::register(AdldapServiceProvider::class);
        AliasLoader::getInstance()->alias('Adldap', Adldap::class);
        AliasLoader::getInstance()->alias('LDAPBackendAuth', LDAPBackendAuth::class);
        App::singleton('backend.ldap_auth', function () {
            return LDAPAuthManager::instance();
        });

        Event::listen('backend.auth.extendSigninView', function($controller) {
            $this->hookSigninForm($controller);
        });

        Event::listen('backend.form.extendFields', function($widget) {
            $this->addFieldsToUserForm($widget);
        });

        Event::listen('backend.list.extendColumns', function($widget) {
            $this->addFieldsToUserList($widget);
        });

    }


    protected function hookSigninForm($controller) {
        $controller->addJs('/plugins/khoatd/ldap/assets/js/override-auth.js');
        $message = Session::get('message');
        if(!empty($message)) {
            Flash::error($message);
        }
    }

    protected function addFieldsToUserForm($widget) {
        if (!$widget->getController() instanceof Users) {
            return;
        }

        $widget->addFields([
            'khoatd_ldap_user_type' => [
                'label'   => 'User type',
                'comment' => '(LDAP user if you want to connect with LDAP, CMS user if you want the user is managed inside the CMS)',
                'type'    => 'dropdown',
                'options' => [
                    'ldap' => 'LDAP user',
                    'cms' => 'CMS user',
                ]
            ]
        ]);
    }

    protected function addFieldsToUserList($widget) {
        if (!$widget->getController() instanceof Users) {
            return;
        }

        $widget->addColumns([
            'khoatd_ldap_user_type' => [
                'label' => 'User type'
            ]
        ]);
    }

}
