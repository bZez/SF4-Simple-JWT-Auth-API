<?php


namespace App\Controller\Back;


use App\Helper\DataParser;
use App\Repository\PartnerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiBackController
 * @package App\Controller\Back
 * @Route("/_secure/partners")
 */
class PartnerController extends AbstractController
{
    private $controllers;

    /**
     * PartnerController constructor.
     * @param KernelInterface $kernel
     * @throws \ReflectionException
     */
    public function __construct(KernelInterface $kernel)
    {
        $parser = new DataParser($kernel);
        $this->controllers = $parser->getControllers();
    }

    /**
     * @param PartnerRepository $repository
     * @return Response
     * @Route("/",name="api_back_partner")
     */
    public function partner(PartnerRepository $repository)
    {
        $partners = $repository->findAll();
        $em = $this->getDoctrine()->getManager();
        $request = Request::createFromGlobals();
        if ($request->isMethod('POST')) {
            $partner = $repository->find($request->get('partnerId'));
            if ($controllers = $request->get('controllers')) {
                $partner->setPrivileges($controllers);
                $sources = array_keys($controllers);
                $results = [];
                foreach ($sources as $source) {
                    $methods = array_keys($controllers[$source]);
                    foreach ($methods as $method) {
                        $ctls[] = array_unique(array_keys($controllers[$source][$method]));

                        foreach ($ctls as $ctl) {
                            $results = $results + $ctl;
                        }
                    }
                }

            } else {
                $partner->setPrivileges([]);
            }
            foreach ($results as $result) {
                $this->forward('App\Controller\Authentication\AccessController::generateAccessToken', [
                    'source' => 'VYV',
                    'controller' => ucfirst(substr($result, 0, -1)),
                    'user' => $partner->getAdmin()
                ]);
            }
            $em->persist($partner);
            $em->flush();
            $this->addFlash('notice', 'Access rights successfully edited.');
            return $this->render('back/partner.html.twig', [
                'partners' => $partners,
                'controllers' => $this->controllers
            ]);
        }
        return $this->render('back/partner.html.twig', [
            'partners' => $partners,
            'controllers' => $this->controllers
        ]);
    }
}