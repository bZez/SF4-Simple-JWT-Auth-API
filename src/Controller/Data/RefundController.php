<?php

namespace App\Controller\Data;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class RefundController
 * @package App\Controller\Data
 */
class RefundController extends AbstractController
{
    /**
     * @param $id int
     * @return JsonResponse
     *@api /refunds/show
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
     * @param $id int
     * @return JsonResponse
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
     * @return JsonResponse
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
}
