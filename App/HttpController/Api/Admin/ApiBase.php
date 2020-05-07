<?php

    namespace App\HttpController\Api\Admin;

    use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;
    use EasySwoole\Validate\Validate;

    class ApiBase extends \EasySwoole\HttpAnnotation\AnnotationController
    {
        protected function onRequest(?string $action): ?bool
        {
            $cookie = $this->request()->getCookieParams('user_cookie');
            //对cookie进行判断，比如在数据库或者是redis缓存中，存在该cookie信息，说明用户登录成功
            $isLogin = true;
            if($isLogin){
                //返回true表示继续往下执行控制器action
                return  true;
            }else{
                //这一步可以给前端响应数据，告知前端未登录
                $this->writeJson(401,null,'请先登录');
                //返回false表示不继续往下执行控制器action
                return  false;
            }
        }

    }