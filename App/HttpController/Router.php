<?php


    namespace App\HttpController;

    use EasySwoole\Http\AbstractInterface\AbstractRouter;
    use FastRoute\RouteCollector;
    use EasySwoole\Http\Request;
    use EasySwoole\Http\Response;

    class Router extends AbstractRouter
    {
        function initialize(RouteCollector $routeCollector)
        {
            $this->setGlobalMode(true);
            $routeCollector->addRoute(['GET','POST'],'/user','/Api/Admin/UserManagementController/userAdd');
//            $routeCollector->get('/user', '/Api/Admin/UserManagementController/userAdd');
            $routeCollector->get('/index', '/Index/index');
            $routeCollector->addRoute(['GET','POST'],'/login','/Api/Admin/LoginController/login');
//            $routeCollector->get('/sql/user', '/Api/Admin/SqlController/userModel');
//            $routeCollector->get('/sql/action', '/Api/Admin/SqlController/actionModel');
        }
    }