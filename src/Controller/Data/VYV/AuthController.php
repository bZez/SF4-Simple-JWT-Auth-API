<?php

namespace App\Controller\Data\VYV;

use App\Helper\Api;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Auths is to be used for authentication, credentials update or password recovery
 */
class AuthController extends AbstractController
{
    private $request;
    private $api;
    private $data;

    /**
     * CredentialController constructor.
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->request = Request::createFromGlobals();
        $this->api = $api;
        $this->data = [
            'organisation' => 'Vyv',
            'controller' => 'LoginController',
            'action' => ''
        ];
    }

    /**
     * @param $login string Login
     * @param $pwd string Password
     * @return JsonResponse
     * @api /auths/login
     * @method POST
     * @example Authenticate user
     */
    public function login()
    {
        $this->data['action'] = 'login';
        if (!$this->request->isMethod('POST'))
            throw new MethodNotAllowedException(['POST'], 'Unallowed method...');
        $login = $this->request->get('login');
        $pwd = $this->request->get('pwd');
        $this->data['user_login'] = $login;
        $this->data['user_pass'] = $pwd;
        $result = $this->api->get($this->data);
        return $this->json($result);

    }

    /**
     * @param $mobile string Mobile
     * @param $key integer Validation(key)
     * @param $old string Password(old)
     * @param $new string Password(new)
     * @param $user integer User(id)
     * @return JsonResponse
     * @api /auths/update
     * @method PUT
     * @example Update user password with Validation Key & Mobile <b class="text-green">OR</b> with User's id & Old Password
     */
    public function update()
    {
        $this->data['action'] = 'updatePassword';
        if (!$this->request->isMethod('PUT'))
            throw new MethodNotAllowedException(['PUT'], 'Unallowed method...');
        parse_str(file_get_contents("php://input"), $_PUT);
        if (isset($_PUT['key'])) {
            $mobile = $_PUT['mobile'];
            $this->data['validatePasswordKey'] = $_PUT['key'];
            $this->data['mobile'] = $mobile;
        } else {
            $oldPass = $_PUT['old'];
            $user = $_PUT['user'];
            $this->data['oldPassword'] = $oldPass;
            $this->data['id'] = $user;
        }
        $newPass = $_PUT['new'];
        $this->data['password'] = $newPass;
        $result = $this->api->get($this->data);
        return $this->json($result);

    }

    /**
     * @param $user integer User(id)
     * @param $mobile string Mobile(new)
     * @return JsonResponse
     * @api /auths/changemobile
     * @method PUT
     * @example Modify user phone number (<b class="text-green">ONLY</b> before the account is validated)
     */
    public function changemobile()
    {
        $this->data['action'] = 'updateMobileForAccountKey';
        if (!$this->request->isMethod('PUT'))
            throw new MethodNotAllowedException(['PUT'], 'Unallowed method...');
        parse_str(file_get_contents('php://input'), $_PUT);
        $user = $_PUT['user'];
        $mobile = $_PUT['mobile'];
        $this->data['id'] = $user;
        $this->data['mobile'] = $mobile;
        $result = $this->api->get($this->data);
        return $this->json($result);

    }

    /**
     * @param $user integer User(id)
     * @param $key integer Validation(key)
     * @return JsonResponse
     * @api /auths/validate
     * @method POST
     * @example Validate account with the key received by text
     */
    public function validate()
    {
        $this->data['action'] = 'validateAccount';
        if (!$this->request->isMethod('POST'))
            throw new MethodNotAllowedException(['POST'], 'Unallowed method...');
        $user = $this->request->get('user');
        $key = $this->request->get('key');
        $this->data['id'] = $user;
        $this->data['validateAccountKey'] = $key;
        $result = $this->api->get($this->data);
        return $this->json($result);

    }

    /**
     * @param $mobile string Mobile
     * @return JsonResponse
     * @api /auths/forgot
     * @method POST
     * @example Launch 'forgot password' procedure
     */
    public function forgot()
    {
        $this->data['action'] = 'forgotPassword';
        if (!$this->request->isMethod('POST'))
            throw new MethodNotAllowedException(['POST'], 'Unallowed method...');
        $mobile = $this->request->get('mobile');
        $this->data['mobile'] = $mobile;
        $result = $this->api->get($this->data);
        return $this->json(['Response' => $result]);

    }
}
