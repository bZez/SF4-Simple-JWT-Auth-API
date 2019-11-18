<?php


namespace App\Controller\Back;


use App\Entity\AccessRequest;
use App\Entity\AccessToken;
use App\Entity\AuthToken;
use App\Helper\DataParser;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class RequestController
 * @package App\Controller\Back
 * @Route("/_secure/requests")
 */
class RequestController extends AbstractController
{
    /**
     * @param AccessRequest $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * @Route("/deny/{request}",name="api_back_request_deny")
     */
    public function denyRequest(AccessRequest $request,EntityManagerInterface $em)
    {
        $request->setStatus(false);
        $em->persist($request);
        $em->flush();
        return $this->json(['Access Denied !']);
    }

    /**
     * @param AccessRequest $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * @Route("/accept/{request}",name="api_back_request_accept")
     */
    public function acceptRequest(AccessRequest $request,EntityManagerInterface $em)
    {
        $req = Request::createFromGlobals();
        if($req->isMethod('POST'))
        {
            $partner = $request->getPartner();
            $controller = $request->getController();
            foreach ($req->request->get('actions') as $m => $a)
            {
                $partner->addPrivilege(strtolower($request->getController()).'s',[$m],$a);
            }
            /**
             * @var AuthToken $auth
             */
            $user = $partner->getAdmin();
            $this->forward('App\Controller\Authentication\AccessController::generateAccessToken',[
                'user' => $user,
                'controller' => $controller
            ]);
            $request->setStatus(true);
            $em->persist($request);
            $em->flush();
            return $this->json(['Access Granted !']);
        }
    }
}