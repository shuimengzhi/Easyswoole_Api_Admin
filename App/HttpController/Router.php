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
            //管理员用户列表
            $routeCollector->addRoute(['POST'],'/User/list','/Api/Admin/AdminUserManagementController/adminUserList');
            //添加管理员用户
            $routeCollector->addRoute(['POST'],'/User/add','/Api/Admin/AdminUserManagementController/adminUserAdd');
            //删除管理员用户
            $routeCollector->addRoute(['POST'],'/User/delete','/Api/Admin/AdminUserManagementController/adminUserDelete');
            //管理员用户详情
            $routeCollector->addRoute(['POST'],'/User/detail','/Api/Admin/AdminUserManagementController/adminUserDetail');
            //获取全部菜单
            $routeCollector->addRoute(['POST'],'/User/menu','/Api/Admin/AdminUserManagementController/adminMenuList');
            //获取所有权限列表
            $routeCollector->addRoute(['POST'],'/User/action','/Api/Admin/AdminUserManagementController/actionList');

            $routeCollector->get('/index', '/Index/index');
            //登录接口
            $routeCollector->addRoute(['POST'],'/login','/Api/Admin/LoginController/login');
            $routeCollector->get('/sql/user', '/Api/Admin/SqlController/userModel');
            //获取初始化菜单
            $routeCollector->addRoute(['POST'],'/init','/Api/Admin/LoginController/init');
            //测试接口
            $routeCollector->addRoute(['GET','POST'],'/test','/Api/Admin/LoginController/test');
        }
    }