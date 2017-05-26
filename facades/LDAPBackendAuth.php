<?php namespace KhoaTD\LDAP\Facades;
use October\Rain\Support\Facade;

class LDAPBackendAuth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Resolves to:
     * - Backend\Classes\AuthManager
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'backend.ldap_auth';
    }
}