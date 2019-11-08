<?php

namespace App\Controller\Data;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum
 */
class RefundController extends AbstractController
{
    /**
     * @param $id int
     * @return JsonResponse
     * @api /refunds/show/:id
     * @method GET
     * @example Get details of specified refund
     */
    public function show($id)
    {
        if ($id == 1) {
            return $this->json([
                'Users' => [
                    '1' => [
                        'Name' => 'User 1'
                    ]
                ]
            ]);
        } else {
            return $this->json([
                'Users' => [
                    '0' => [
                        'Name' => 'User 0'
                    ]
                ]
            ]);
        }
    }

    /**
     * @return JsonResponse
     * @api /refunds/list
     * @method GET
     * @example Get a list of refund
     */
    public function list()
    {
        return $this->json([
            'Users' => [
                '0' => [
                    'Name' => 'User 1'
                ],
                '1' => [
                    'Name' => 'User 2'
                ]
            ]
        ]);
    }

    /**
     * @param $id int
     * @return JsonResponse
     * @api /refunds/edit/:id
     * @method PUT
     * @example Edit details of specified refund
     */
    public function edit($id)
    {
        if ($id == 1) {
            return $this->json([
                'Users' => [
                    '1' => [
                        'Name' => 'User 1'
                    ]
                ]
            ]);
        } else {
            return $this->json([
                'Users' => [
                    '0' => [
                        'Name' => 'User 0'
                    ]
                ]
            ]);
        }
    }

    /**
     * @param $id int
     * @param $amount float
     * @param $date datetime
     * @param $details [] array
     * @return JsonResponse
     * @api /refunds/create
     * @method POST
     * @example Create a new refund
     */
    public function create($id, $amount, $date, $details)
    {
        return $this->json([
            'Users' => [
                '0' => [
                    'Name' => 'User 1'
                ],
                '1' => [
                    'Name' => 'User 2'
                ]
            ]
        ]);
    }


    /**
     * @param $id int
     * @return JsonResponse
     * @api /refunds/delete/:id
     * @method DELETE
     * @example Delete specified refund
     */
    public function delete($id)
    {
        return $this->json([
            'Users' => [
                '0' => [
                    'Name' => 'User 1'
                ],
                '1' => [
                    'Name' => 'User 2'
                ]
            ]
        ]);
    }
}
