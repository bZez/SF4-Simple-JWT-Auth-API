<?php


namespace App\Controller\Front;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ActivityController
 * @package App\Controller\Front
 * @Route("/~private/activity")
 */
class ActivityController extends AbstractController
{
    /**
     * @param ActivityRepository $repository
     * @throws \Exception
     * @return Response
     * @Route("/",name="api_front_activity")
     */
    public function activity(ActivityRepository $repository)
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        $months = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        $year = (new \DateTime())->format('Y');
        $total = 0;
        foreach ($methods as $method) {
            $activities[$year][$method] = $repository->findCallsYear($this->getUser()->getPartner(), $method,"$year");
            $total = $total + $activities[$year][$method];
        }
        foreach ($months as $month) {
            $activities[$year]['months'][$month]['GET'] = $repository->findCallsMonth($this->getUser()->getPartner(),"GET","$month");
            $activities[$year]['months'][$month]['POST'] = $repository->findCallsMonth($this->getUser()->getPartner(),"POST","$month");
            $activities[$year]['months'][$month]['PUT'] = $repository->findCallsMonth($this->getUser()->getPartner(),"PUT","$month");
            $activities[$year]['months'][$month]['DELETE'] = $repository->findCallsMonth($this->getUser()->getPartner(),"DELETE","$month");
        }
        return $this->render('front/activity.html.twig', [
            'activities' => $activities,
            'totalCalls' => $total

        ]);
    }
}