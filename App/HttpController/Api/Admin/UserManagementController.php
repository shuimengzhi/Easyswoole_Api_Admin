<?php


    namespace App\HttpController\Api\Admin;


    use App\HttpController\Model\AdminUserModel;
    use EasySwoole\EasySwoole\Config;
    use EasySwoole\EasySwoole\Logger;
    use EasySwoole\Http\Message\Status;
    use EasySwoole\HttpAnnotation\AnnotationTag\Method;
    use EasySwoole\HttpAnnotation\AnnotationTag\Param;
    use EasySwoole\I18N\I18N;
    use EasySwoole\Jwt\Jwt;

    /**
     * Class UserManagementController
     * @package App\HttpController\Api\Admin
     */
    class UserManagementController extends ApiBase
    {
        //展示管理员列表
        /**
         * @Param(name="page",required="")
         * @return bool
         * @throws \EasySwoole\I18N\Exception\Exception
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function adminUserList()
        {
            if (!$this->checkAction('USER_LIST')) {
                return false;
            }
            $page = $this->request()->getRequestParam('page') ?? 1;          // 当前页码
            $limit = 10;        // 每页多少条数据
            $adminModel = AdminUserModel::create()->limit($limit * ($page - 1), $limit)->withTotalCount();
           //获取总页数
            $list = $adminModel->all();
            $total = $adminModel->lastQueryResult()->getTotalCount();
            $totalPage = ceil($total / $limit);

            foreach ($list as $value) {
                $v = $value->toArray();
                $message[] = [
                    'admin_id' => $v['admin_id'],
                    'admin_name' => $v['admin_name'],
                    'email' => $v['email'],
                    'group' => I18N::getInstance()->translate($v['admin_group']),
                    'create_time' => date('Y-m-d', $v['create_time'])
                ];
            }
            $this->writeJson(Status::CODE_OK, ['code' => 0, 'result' => $message, 'total_page' => $totalPage]);
            return true;
        }

        /**
         * @Method(allow={POST,GET})
         * @Param(name="adminName",required="")
         * @Param(name="password",required="")
         * @Param(name="email",required="",regex="/@/")
         * @Param(name="admin_group",required="")
         * @Param(name="menu_list",required="")
         * @Param(name="actionList",required="")
         * @return bool
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function adminUserAdd(): bool
        {
            if (!$this->checkAction('USER_ADD')) {
                return false;
            }
            $param = $this->request()->getRequestParam();
            $data = [
                'admin_name' => $param['adminName'],
                'password' => md5($param['password']),
                'email' => $param['email'],
                'admin_group'=>$param['admin_group'],
                'menu_list'=>$param['menu_list'],
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
                return true;
            } else {
                $this->writeJson(Status::CODE_BAD_REQUEST, $res, 'Add admin user fail.');
                return false;
            }
        }

        //删除用户

        /**
         * @Param(name="admin_id",required="",integer="")
         * @return bool
         * @throws \EasySwoole\Jwt\Exception
         */
        public function adminUserDelete(): bool
        {
            if (!$this->checkAction('USER_DEL')) {
                return false;
            }
            $adminId = $this->request()->getRequestParam('admin_id');
            //删除保护机制
            if (!$this->delProtectAdmin($adminId)) {
                return false;
            }
            //进行删除操作
            try {
                AdminUserModel::create()->destroy($adminId);
                $this->writeJson(Status::CODE_OK, ['code' => 0], 'Delete success:admin_id is ' . $adminId);
                return true;
            } catch (\Throwable $throwable) {
                $this->writeJson(Status::CODE_OK, ['code' => -1], 'Delete fail:admin_id is ' . $adminId);
                Logger::getInstance()->waring("Delete fail:admin_id is" . $adminId);
                Logger::getInstance()->waring($throwable);
                return false;
            }

        }

        //删除保护，不允许删除最高权限的admin

        /**
         * @param int $adminId
         * @return bool
         * @throws \EasySwoole\Jwt\Exception
         */
        protected function delProtectAdmin(int $adminId)
        {
            $token = $this->request()->getCookieParams('token');
            $jwtObject = Jwt::getInstance()->setSecretKey('easyswoole')->decode($token);
            $data = $jwtObject->getData();
            $adminIdSelf = $data['admin_id'];
            //判断删除的是不是自己
            if ($adminId === $adminIdSelf) {
                $this->writeJson(Status::CODE_OK, ['code' => -1],
                    'Delete fail:Are you kidding me ? Detelte yourself ??');
                return false;
            }
            //判断是不是删除最高权限的管理员
            if ($adminId === $GLOBALS['TOP_ADMIN_ID']) {
                $this->writeJson(Status::CODE_OK, ['code' => -1], 'Delete fail:This is very important Admin user.');
                return false;
            }
            //判断删除的用户是否存在
            $result = AdminUserModel::create()->where('admin_id', $adminId)->get();
            if (!$result) {
                $this->writeJson(Status::CODE_OK, ['code' => -1], "Delete fail:Admin user isn't exsit.");
                return false;
            }
            return true;
        }

    }