<?php


    namespace App\HttpController\Api\Admin;


    use App\HttpController\Model\AdminUserModel;
    use EasySwoole\Http\Message\Status;
    use EasySwoole\HttpAnnotation\AnnotationTag\Method;
    use EasySwoole\HttpAnnotation\AnnotationTag\Param;

    /**
     * Class UserManagementController
     * @package App\HttpController\Api\Admin
     */

    class UserManagementController extends ApiBase
    {


        /**
         * @Method(allow={POST,GET})
         * @Param(name="adminName",required="")
         * @Param(name="password",required="")
         * @Param(name="email",required="",regex="/@/")
         * @Param(name="actionList",required="")
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function userAdd()
        {

            $param = $this->request()->getRequestParam();
            $data = [
                'admin_name' => $param['adminName'],
                'password' => md5($param['password']),
                'email' => $param['email'],
                'create_time' => time(),
                'last_time' => 1,
                'last_ip' => 1,
                //            暂时直接接受权限，后期前端做出了要设置密钥解密
                'action_list' => $param['actionList']
            ];

            $model = new AdminUserModel($data);
            $res = $model->save();
            if ($res) {
                $this->writeJson(Status::CODE_OK, $res, "success");
            } else {
                $this->writeJson(Status::CODE_BAD_REQUEST, $res, 'Add admin user fail.');
            }
        }

    }