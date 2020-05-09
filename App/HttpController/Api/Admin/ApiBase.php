<?php

    namespace App\HttpController\Api\Admin;

    use App\HttpController\Model\AdminUserModel;
    use EasySwoole\Http\Message\Status;
    use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;
    use EasySwoole\Jwt\Jwt;
    use EasySwoole\Validate\Validate;

    class ApiBase extends \EasySwoole\HttpAnnotation\AnnotationController
    {
        protected function onRequest(?string $action): ?bool
        {
            if ($action === 'login' || $action === 'test') {
                return true;
            }
//            判断token是否有效
            $token = $this->request()->getCookieParams('token');
            try {
                // 如果encode设置了秘钥,decode 的时候要指定
                $jwtObject = Jwt::getInstance()->setSecretKey('easyswoole')->decode($token);
                $status = $jwtObject->getStatus();

                switch ($status) {
                    case  1:
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
                        $userModel = new AdminUserModel();
                        $res = $userModel->where('token', $token)->get();
                        if (!$res) {
                            $this->writeJson(Status::CODE_OK, ['code' => -1], 'token无效,请重新登录');
                            return false;
                        }
                        //  验证成功
                        return true;
                        break;
                    case  -1:
                        $this->writeJson(Status::CODE_OK, ['code' => -1], 'token无效,请重新登录');
                        return false;
                        break;
                    case  -2:
                        $this->writeJson(Status::CODE_OK, ['code' => -1], 'token过期,请重新登录');
                        return false;
                        break;
                }
            } catch (\EasySwoole\Jwt\Exception $e) {
                var_dump($e);
                $this->writeJson(Status::CODE_OK, ['code' => -1], 'token验证时程序失败');
                return false;
            }
            return false;
        }

    }