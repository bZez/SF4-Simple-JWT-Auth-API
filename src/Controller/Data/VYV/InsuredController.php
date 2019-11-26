<?php

namespace App\Controller\Data\VYV;

use App\Helper\Api;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Insured is to be used for retrieve information
 */
class InsuredController extends AbstractController
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
            'controller' => 'AssureController',
            'action' => ''
        ];
    }

    /**
     * @param $id integer User(id)
     * @return JsonResponse
     * @api /insureds/:id
     * @method GET
     * @example Get full details of specified user
     */
    public function show($id)
    {
        $this->data['action'] = 'getAssure';
        if (!$this->request->isMethod('GET'))
            throw new MethodNotAllowedException(['GET'], 'Unallowed method...');
        $this->data['id'] = $id;
        $result = $this->api->get($this->data);
        return $this->json($result);
    }

    /**
     * @param $id integer User(id)
     * @param $address string Address
     * @param $add_address string Additional(address)
     * @param $zip string ZipCode(address)
     * @param $city string City(address)
     * @param $country string Country(address)
     * @return JsonResponse
     * @api /insureds/update
     * @method PUT
     * @example Update user information
     */
    public function update()
    {
        /**
         * TODO
         * @param $phone string Phone(number)
         * @param $mobile string Mobile(number)
         * @param $iban_levy string RIB(levy)
         * @param $iban_refund string RIB(refund)
         */
        $this->data['action'] = 'update';
        if (!$this->request->isMethod('PUT'))
            throw new MethodNotAllowedException(['PUT'], 'Unallowed method...');
        $this->data['id'] = $this->request->get('id');
        if($address = $this->request->get('address'))
            $this->data['address'] = $address;
        if($add_address = $this->request->get('add_address'))
            $this->data['addressComplementary'] = $add_address;
        if($zip = $this->request->get('zip'))
            $this->data['postalCode'] = $zip;
        if($city = $this->request->get('city'))
            $this->data['city'] = $city;
        if($country = $this->request->get('country'))
            $this->data['country'] = $country;
        $result = $this->api->get($this->data);
        return $this->json([$result]);
    }

    /**
     * @return JsonResponse
     * @api /insureds/contracts
     * @method GET
     * @example Get a list of user's contracts
     */
    public function contracts()
    {
        $this->data['controller'] = 'PoliceController';
        $this->data['action'] = 'list';
        if (!$this->request->isMethod('GET'))
            throw new MethodNotAllowedException(['GET'], 'Unallowed method...');
        $this->data['id'] = $this->request->get('id');
        $result = $this->api->get($this->data);
        return $this->json([$result]);
    }
}
