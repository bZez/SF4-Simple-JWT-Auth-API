<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    public function get($id)
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
