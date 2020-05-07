<?php


    namespace App\HttpController\Api\Admin;


    use App\HttpController\Model\AdminUserModel;
    use EasySwoole\EasySwoole\Config;
    use EasySwoole\EasySwoole\ServerManager;
    use EasySwoole\Http\Message\Status;
    use EasySwoole\HttpAnnotation\AnnotationTag\Param;
    use mysql_xdevapi\Exception;
    use EasySwoole\Jwt\Jwt;

    /**
     * Class LoginController
     * @package App\HttpController\Api\Admin
     */
    class LoginController extends ApiBase
    {
        /**
         * @Param(name="adminName",required="")
         * @Param(name="password",required="")
         * @throws \EasySwoole\Mysqli\Exception\Exception
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function login()
        {
            $data = $this->request()->getRequestParam();
            $userModel = AdminUserModel::create();
            //            获取用户信息
            $res = $userModel->where('admin_name', $data['adminName'])->where('password',
                md5($data['password']))->get();
            //            登录失败执行
            if ($res === null) {
                $this->writeJson(Status::CODE_OK, ['code' => -1], 'login fail');
                $this->response()->end();
                return false;
            }

            //            Token生成
            $jwtObject = Jwt::getInstance()
                ->setSecretKey('easyswoole') // 秘钥
                ->publish();

            $jwtObject->setAlg('HMACSHA256'); // 加密方式
            $jwtObject->setAud($res->admin_name); // 用户
            $jwtObject->setExp(time() + 3600); // 过期时间
            $jwtObject->setIat(time()); // 发布时间
            $jwtObject->setIss('easyswoole-Admin'); // 发行人
            $jwtObject->setJti(md5(time())); // jwt id 用于标识该jwt
            $jwtObject->setNbf(time() + 60 * 5); // 在此之前不可用
            $jwtObject->setSub('Admin Login'); // 主题

            // 自定义数据
            $jwtObject->setData([
                'admin_id' => $res->admin_id,
                'admin_name' => $res->admin_name,
                'action_list' => $res->action_list
            ]);

            // 最终生成的token
            $token = $jwtObject->__toString();
            $domain = Config::getInstance()->getConf('FRONT_END_DOMAIN');
            $this->response()->setCookie('token', $token, time() + 3600, '/', $domain, false, true);
            //            ip部署到服务器的时候再验证一下
            $ipInfo = ServerManager::getInstance()->getSwooleServer()->connection_info($this->request()->getSwooleRequest()->fd);
            $ip = $ipInfo['remote_ip'];
            $lastTime = $ipInfo['last_time'];
            //            更新登录者的IP和登录时间
            $userModel->update(
                ['last_time' => $lastTime, 'last_ip' => $ip, 'token' => $token],
                ['admin_name' => $data['adminName']]
            );
            $this->writeJson(Status::CODE_OK, ['code' => 0, 'token' => $token], 'login success');

        }


        public function test()
        {
            $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE1ODg4MjIxODYsInN1YiI6IuS4u-mimCIsIm5iZiI6MTU4ODgxODg4NiwiYXVkIjoidXNlciIsImlhdCI6MTU4ODgxODU4NiwianRpIjoiZGVkZGYwMDczYmEzNjczYWVmNmQ4OGUzYzM5NWUxMWEiLCJzdGF0dXMiOjEsImRhdGEiOlsib3RoZXJfaW5mbyJdfQ.KKu3JypTr70I4667izc4d7S8Yv8cW6KZ34Y0EvkGNN8";

            try {
                $jwtObject = Jwt::getInstance()->setSecretKey('easyswoole')->decode($token);
//$jwtObject->setSecretKey('easyswoole');
                $status = $jwtObject->getStatus();

                // 如果encode设置了秘钥,decode 的时候要指定
//                $status = $jwtObject->setSecretKey('easyswoole')->decode($token);

                switch ($status) {
                    case  1:
                        echo '验证通过';
                        $data['alg'] = $jwtObject->getAlg();
                        $data['aud'] = $jwtObject->getAud();
                        $data['data'] = $jwtObject->getData();
                        $data['exp'] = $jwtObject->getExp();
                        $data['iat'] = $jwtObject->getIat();
                        $data['iss'] = $jwtObject->getIss();
                        $data['nbf'] = $jwtObject->getNbf();
                        $data['jti'] = $jwtObject->getJti();
                        $data['sub'] = $jwtObject->getSub();
                        $data['signature'] = $jwtObject->getSignature();
                        $data['property'] = $jwtObject->getProperty('alg');
                        var_dump($data);
                        break;
                    case  -1:
                        echo '无效';
                        break;
                    case  -2:
                        echo 'token过期';
                        break;
                }
            } catch (\EasySwoole\Jwt\Exception $e) {
                var_dump($e);
            }
            die;
        }


    }