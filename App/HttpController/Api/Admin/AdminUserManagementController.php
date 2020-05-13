<?php


    namespace App\HttpController\Api\Admin;


    use App\HttpController\Model\AdminUserModel;
    use EasySwoole\EasySwoole\Config;
    use EasySwoole\EasySwoole\Logger;
    use EasySwoole\Http\Message\Status;
    use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\Api;
    use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\ApiFail;
    use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\ApiRequestExample;
    use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\ApiSuccess;
    use EasySwoole\HttpAnnotation\AnnotationTag\DocTag\ResponseParam;
    use EasySwoole\HttpAnnotation\AnnotationTag\Method;
    use EasySwoole\HttpAnnotation\AnnotationTag\Param;
    use EasySwoole\I18N\I18N;
    use EasySwoole\Jwt\Jwt;

    /**
     * Class AdminUserManagementController
     * @package App\HttpController\Api\Admin
     * Function list:
     * adminUserList()
     * adminUserAdd()
     * adminUserDelete()
     * delProtectAdmin():Protect admin user when action is delete
     */
    class AdminUserManagementController extends ApiBase
    {
        //展示管理员列表
        /**
         * @Api(name=" List of admin user",group="Admin User Management",path="/User/list",description="展示管理员列表")
         * @Method(allow={POST})
         * @Param(name="page",required="",description="输入需要的页码",integer="")
         * @ApiRequestExample(https://sjs.ngrok.shuimengzhi.com/User/list)
         * @ApiSuccess({
        "code": 200,
        "result": {
        "res": [
        {
        "admin_id": 1,
        "admin_name": "admin",
        "email": "shui_jingshan@163.com",
        "group": "管理组",
        "create_time": "2020-05-05"
        },
        {
        "admin_id": 2,
        "admin_name": "useradmin",
        "email": "sss",
        "group": "管理用户组",
        "create_time": "1970-01-19"
        },
        {
        "admin_id": 3,
        "admin_name": "useradmin3",
        "email": "sss",
        "group": "管理用户组",
        "create_time": "1970-01-19"
        }
        ],
        "total_page": 1
        },
        "msg": null
         *     })
         * @ResponseParam(name="code",description="状态码200")
         * @ResponseParam(name="res",description="管理员内容")
         * @ResponseParam(name="total_page",description="总的页码")
         * @return bool
         * @throws \EasySwoole\I18N\Exception\Exception
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function adminUserList(): bool
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
                    'adminId' => $v['admin_id'],
                    'adminName' => $v['admin_name'],
                    'email' => $v['email'],
                    'group' => I18N::getInstance()->translate($v['admin_group']),
                    'createTime' => date('Y-m-d', $v['create_time'])
                ];
            }
            $this->writeJson(Status::CODE_OK, ['res' => $message, 'total_page' => $totalPage]);
            return true;
        }

        /**
         * @Api(name="Admin user add",group="Admin User Management",path="/User/add",description="管理用户添加接口")
         * @Method(allow={POST,GET})
         * @Param(name="adminName",required="",description="管理员名称不能重复命名")
         * @Param(name="password",required="",description="管理员密码")
         * @Param(name="email",required="",regex="/@/",description="管理员的邮箱带@就行")
         * @Param(name="adminGroup",required="",description="管理组，填写如:ADMIN_GROUP")
         * @Param(name="menuList",required="",description="可见菜单ID,填写如:1,2,3,4")
         * @Param(name="actionList",required="",description="权限，填写如'GEN_ACT,USER_MA,USER_ADD,USER_LIST,USER_DEL'")
         * @ApiRequestExample(https://sjs.ngrok.shuimengzhi.com/User/add)
         * @ApiSuccess(
         *      {
        "code": 200,
        "result": 6,
        "msg": "success"
        }
         * )
         * @ResponseParam(name="result",description="新增管理员的ID")
         * @ApiFail({
        "code": 501,
        "result": $res,
        "msg": "Add admin user fail."
         *     })
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
                'admin_group' => $param['adminGroup'],
                'menu_list' => $param['menuList'],
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
                $this->writeJson(Status::CODE_NOT_IMPLEMENTED, $res, 'Add admin user fail.');
                return false;
            }
        }

        //删除用户

        /**
         * @Api(name="Admin user delete",group="Admin User Management",path="/User/delete",description="删除管理员接口")
         * @Method(allow={POST})
         * @Param(name="adminId",required="",integer="")
         * @ApiRequestExample(https://sjs.ngrok.shuimengzhi.com/User/delete)
         * @ApiSuccess(
         *     {
        "code": 200,
        "result": null,
        "msg": "Delete success:admin ID is 6"
        }
         * )
         * @ApiFail(
         *     {
        "code": 501,
        "result": null,
        "msg": "Delete fail:admin ID is 6"
        }
         * )
         * @return bool
         * @throws \EasySwoole\Jwt\Exception
         */
        public function adminUserDelete(): bool
        {
            if (!$this->checkAction('USER_DEL')) {
                return false;
            }
            $adminId = $this->request()->getRequestParam('adminId');
            //删除保护机制
            if (!$this->delProtectAdmin($adminId)) {
                return false;
            }
            //进行删除操作
            try {
                AdminUserModel::create()->destroy($adminId);
                $this->writeJson(Status::CODE_OK, null, 'Delete success:admin ID is ' . $adminId);
                return true;
            } catch (\Throwable $throwable) {
                $this->writeJson(Status::CODE_NOT_IMPLEMENTED, null, 'Delete fail:admin ID is ' . $adminId);
                Logger::getInstance()->waring("Delete fail:admin ID is" . $adminId);
                Logger::getInstance()->waring($throwable);
                return false;
            }

        }

        //管理员详细信息
        public function adminUserDetail(): bool
        {
//            if (!$this->checkAction('USER_DETAIL')) {
//                return false;
//            }
            $adminId = $this->request()->getRequestParam('adminId');
            $res = AdminUserModel::create()->where('admin_id', $adminId)->get();
            $detail=[
                ['adminId']=>$res['admin_id'],
                ['adminName']=>$res['admin_name'],
                ['email']=>$res['email'],
                ['createTime']=>$res['create_time'],
            ];
            var_dump($res);
            return true;
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
                $this->writeJson(Status::CODE_BAD_REQUEST, null,
                    'Delete fail:Are you kidding me ? Detelte yourself ??');
                return false;
            }
            //判断是不是删除最高权限的管理员
            if ($adminId === $GLOBALS['TOP_ADMIN_ID']) {
                $this->writeJson(Status::CODE_BAD_REQUEST, null, 'Delete fail:This is very important Admin user.');
                return false;
            }
            //判断删除的用户是否存在
            $result = AdminUserModel::create()->where('admin_id', $adminId)->get();
            if (!$result) {
                $this->writeJson(Status::CODE_BAD_REQUEST, null, "Delete fail:Admin user isn't exsit.");
                return false;
            }
            return true;
        }

    }