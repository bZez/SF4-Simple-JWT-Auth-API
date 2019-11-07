<?php


namespace App\Controller\Front;


use App\Helper\DataParser;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use \ReflectionException;

/**
 * Class ApiFrontController
 * @package App\Controller\Front
 * @Route("/~private")
 */
class ApiFrontController extends AbstractController
{
    /**
     * @Route("/",name="api_front_dash")
     * @return Response
     */
    public function dashboard()
    {
        return $this->render('front/dash.html.twig');
    }

    /**
     * @Route("/users",name="api_front_users")
     */
    public function users(UserRepository $repository)
    {
        $users = $repository->findAll();
        return $this->render('front/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/datas",name="api_front_datas")
     * @throws ReflectionException
     */
    public function datas(KernelInterface $kernel)
    {
        $parser = new DataParser($kernel);
        $controllers = $parser->getControllers();
        return $this->render('front/data.html.twig', [
            'controllers' => $controllers
        ]);
    }

    public function generateRemotingApi()
    {

        $list = array();
        foreach ($this->remotingBundles as $bundle) {
            $bundleRef = new \ReflectionClass($bundle);
            $controllerDir = new Finder();
            $controllerDir->files()->in(dirname($bundleRef->getFileName()) . '/Controller/')->name('/.*Controller\\.php$/');
            foreach ($controllerDir as $controllerFile) {
                /** @var SplFileInfo $controllerFile */
                $controller = $bundleRef->getNamespaceName() . "\\Controller\\" . substr($controllerFile->getFilename(), 0, -4);
                $controllerRef = new \ReflectionClass($controller);
                foreach ($controllerRef->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    /** @var $methodDirectAnnotation Direct */
                    $methodDirectAnnotation = $this->annoReader->getMethodAnnotation($method, 'Tpg\\ExtjsBundle\\Annotation\\Direct');
                    if ($methodDirectAnnotation !== null) {
                        $nameSpace = str_replace("\\", ".", $bundleRef->getNamespaceName());
                        $className = str_replace("Controller", "", $controllerRef->getShortName());
                        $methodName = str_replace("Action", "", $method->getName());
                        $list[$nameSpace][$className][] = array('name' => $methodName, 'len' => count($method->getParameters()));
                    }
                }
            }
        }
        return $list;
    }
}