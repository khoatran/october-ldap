<?php namespace KhoaTran\LDAP\Controllers;

use ApplicationException;
use Backend;
use Backend\Models\AccessLog;
use BackendAuth;
use LDAPBackendAuth;
use Flash;
use Mail;
use October\Rain\Auth\AuthException;
use Redirect;
use Session;
use Adldap\Laravel\Facades\Adldap;
use System\Classes\UpdateManager;
use ValidationException;
use Validator;

class LDAPAuth extends Backend\Classes\Controller
{

    protected $publicActions = ['signin'];
    /**
     * Displays the log in page.
     */
    public function signin()
    {
        try {
            return $this->authenticate();
        }
        catch (\Exception $ex) {
            Session::flash('message', $ex->getMessage());
            return Backend::redirect('backend/auth/signin');
        }
    }

    public function authenticate()
    {
        $rules = [
            'login'    => 'required|between:2,255',
            'password' => 'required|between:4,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $username = post('login');
        $password = post('password');
        $user = BackendAuth::findUserByLogin($username);
        if(empty($user)) {
            throw new AuthException(sprintf('User "%s" is not granted to access backend. Please contact your administrator', $username));
        }
        if($user->user_type === 'ldap') {
            $user = LDAPBackendAuth::authenticate([
                'login' => $username,
                'password' => $password
            ], true);
        } else {
            $user = BackendAuth::authenticate([
                'login' => $username,
                'password' => $password
            ], true);
        }

        UpdateManager::instance()->update();
        AccessLog::add($user);
        return Backend::redirectIntended('backend');
    }

}