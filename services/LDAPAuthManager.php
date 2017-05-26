<?php namespace Bamboo\LDAP\Services;

use Backend\Classes\AuthManager;
use October\Rain\Auth\AuthException;
use Adldap\Laravel\Facades\Adldap;

class LDAPAuthManager extends AuthManager
{
    protected static $instance;
    public function authenticate(array $credentials, $remember = true)
    {
        /*
         * Default to the login name field or fallback to a hard-coded 'login' value
         */
        $loginName = $this->createUserModel()->getLoginName();
        $loginCredentialKey = (isset($credentials[$loginName])) ? $loginName : 'login';

        if (empty($credentials[$loginCredentialKey])) {
            throw new AuthException(sprintf('The "%s" attribute is required.', $loginCredentialKey));
        }

        if (empty($credentials['password'])) {
            throw new AuthException('The password attribute is required.');
        }

        /*
         * If the fallback 'login' was provided and did not match the necessary
         * login name, swap it over
         */
        if ($loginCredentialKey !== $loginName) {
            $credentials[$loginName] = $credentials[$loginCredentialKey];
            unset($credentials[$loginCredentialKey]);
        }

        /*
         * If throttling is enabled, check they are not locked out first and foremost.
         */
        if ($this->useThrottle) {
            $throttle = $this->findThrottleByLogin($credentials[$loginName], $this->ipAddress);
            $throttle->check();
        }

        /*
         * Look up the user by authentication credentials.
         */
        try {
            $username = $credentials[$loginName];
            $password = $credentials['password'];
            $user = $this->authenticateWithAD($username, $password);

        }
        catch (AuthException $ex) {
            if ($this->useThrottle) {
                $throttle->addLoginAttempt();
            }

            throw $ex;
        }

        if ($this->useThrottle) {
            $throttle->clearLoginAttempts();
        }

        $user->clearResetPassword();
        $this->login($user, $remember);

        return $this->user;
    }

    protected function authenticateWithAD($username, $password) {
        try {
            $loginSuccess = Adldap::auth()->attempt($username, $password);
            if($loginSuccess) {
                return $this->findUserByLogin($username);
            }
        } catch (\Exception $ex) {
            throw new AuthException('Invalid credential');
        }
    }


}