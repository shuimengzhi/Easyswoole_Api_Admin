<?php


    namespace App\HttpController\Api\Admin;


    use App\HttpController\Model\AdminActionModel;
    use App\HttpController\Model\AdminMenuModel;
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
         * @ApiSuccess({"code":200,"result":{"res":[{"adminId":1,"adminName":"admin","email":"shui_jingshan@163.com","group":"管理组","createTime":"2020-05-05"},{"adminId":2,"adminName":"useradmin","email":"sss","group":"管理用户组","createTime":"1970-01-19"},{"adminId":3,"adminName":"useradmin3","email":"sss","group":"管理用户组","createTime":"1970-01-19"}],"total_page":1},"msg":null})
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
            if (!$this->checkAction('USER_LIST_A')) {
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
         * @Param(name="menuList",required="",description="可见菜单code,填写如:USUALLY_MENU,USER_MANAGEMENT,USER_LIST")
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
         * @ApiFail({"code": 501,"result": $res,"msg": "Add admin user fail."})
         * @return bool
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function adminUserAdd(): bool
        {
            if (!$this->checkAction('USER_ADD_A')) {
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
            if (!$this->checkAction('USER_DEL_A')) {
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

        /**
         * @Api(name="Detail about administrator",group="Admin User Management",path="/User/detail",description="管理员详情接口")
         * @Method(allow={POST})
         * @Param(name="adminId",required="")
         * @ApiRequestExample(https://sjs.ngrok.shuimengzhi.com/User/detail)
         * @ApiSuccess({"code":200,"result":{"adminId":2,"adminName":"useradmin","email":"sss","createTime":"1970-01-19","adminGroup":{"code":"USER_ADMIN","name":"管理用户组"},"actionList":[{"code":"GEN_ACT_A","name":"常用操作"},{"code":"USER_MA_A","name":"用户管理"},{"code":"USER_ADD_A","name":"添加用户"},{"code":"USER_LIST_A","name":"用户列表"},{"code":"USER_DEL_A","name":"删除用户"}],"menuList":[{"code":"USUALLY_MENU","name":"常用菜单"},{"code":"USER_MANAGEMENT","name":"用户管理"},{"code":"USER_LIST","name":"用户列表"}],"lastTime":"2020-05-12"},"msg":null})
         * @ApiSuccess({"code":200,"result":{"adminId":1,"adminName":"admin","email":"shui_jingshan@163.com","createTime":"2020-05-05","adminGroup":{"code":"ADMIN_GROUP","name":"管理组"},"actionList":[{"code":"all","name":null}],"menuList":[{"code":"all","name":null}],"lastTime":"2020-05-13"},"msg":null})
         * @return bool
         * @throws \EasySwoole\I18N\Exception\Exception
         * @throws \EasySwoole\Mysqli\Exception\Exception
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function adminUserDetail(): bool
        {
            if (!$this->checkAction('USER_DETAIL_A')) {
                return false;
            }
            $adminId = $this->request()->getRequestParam('adminId');
            $res = AdminUserModel::create()->where('admin_id', $adminId)->get();

            $actionListArray = explode(',', $res['action_list']);
            $actionList = array();
            if ($actionListArray == 'all') {
                $actionList[] = [
                    'code' => 'all',
                    'name' => 'all action'
                ];
            } else {
                foreach ($actionListArray as $v) {
                    $actionList[] = [
                        'code' => $v,
                        'name' => I18N::getInstance()->translate($v)
                    ];
                }
            }

            $menuListArray = explode(',', $res['menu_list']);
            $menuList = array();
            if ($menuListArray == 'all') {
                $menuList[] = [
                    'code' => 'all',
                    'name' => 'all action'
                ];
            } else {
                foreach ($menuListArray as $v) {
                    $menuList[] = [
                        'code' => $v,
                        'name' => I18N::getInstance()->translate($v),
                    ];
                }
            }

            $detail = [
                'adminId' => $res['admin_id'],
                'adminName' => $res['admin_name'],
                'email' => $res['email'],
                'createTime' => date('Y-m-d', $res['create_time']),
                'adminGroup' => [
                    'code' => $res['admin_group'],
                    'name' => I18N::getInstance()->translate($res['admin_group'])
                ],
                'actionList' => $actionList,
                'menuList' => $menuList,
                'lastTime' => date('Y-m-d', $res['last_time'])
            ];
            $this->writeJson(Status::CODE_OK, $detail);
            return true;
        }


        //获取全面的菜单信息

        /**
         * @Api(name="Menu",group="Base info",path="/User/menu",description="获取全部的菜单信息")
         * @Method(allow={POST})
         * @ApiSuccess({"code":200,"result":[{"menuId":1,"parentId":0,"menuName":"常用菜单","menuCode":"USUALLY_MENU","icon":"fa fa-bars","href":"","target":"_self","level":1},{"menuId":2,"parentId":1,"menuName":"用户管理","menuCode":"USER_MANAGEMENT","icon":"fa fa-bars","href":"","target":"_self","level":2},{"menuId":3,"parentId":2,"menuName":"用户列表","menuCode":"USER_LIST","icon":"fa fa-bars","href":"view/user/user_list.html","target":"_self","level":3},{"menuId":4,"parentId":2,"menuName":"管理员列表","menuCode":"ADMINISTRATOR_LIST","icon":"fa fa-bars","href":"view/user/admin_user_list.html","target":"_self","level":3},{"menuId":5,"parentId":1,"menuName":"1_5","menuCode":"ONE_FIVE","icon":"fa fa-bars","href":"page/404.html","target":"_self","level":2},{"menuId":6,"parentId":1,"menuName":"1_6","menuCode":"ONE_SIX","icon":"fa fa-bars","href":"page/404.html","target":"_self","level":2},{"menuId":7,"parentId":6,"menuName":"6_7","menuCode":"SIX_SEVEN","icon":"fa fa-bars","href":"page/404.html","target":"_self","level":3}],"msg":null})
         * @return bool
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function adminMenuList(): bool
        {
            if (!$this->checkAction('MENU_LIST_A')) {
                return false;
            }
            $menuListArray = AdminMenuModel::create()->all();
            foreach ($menuListArray as $value) {
                $v = $value->toArray();
                $menuList[] = [
                    'menuId' => $v['menu_id'],
                    'parentId' => $v['parent_id'],
                    'menuName' => I18N::getInstance()->translate($v['menu_code']),
                    'menuCode' => $v['menu_code'],
                    'icon' => $v['icon'],
                    'href' => $v['href'],
                    'target' => $v['target'],
                    'level' => $v['level'],
                ];
            }
            $this->writeJson(Status::CODE_OK, $menuList);
            return true;
        }

        //获取所有的权限信息

        /**
         * @Api(name="Action List",group="Base info",path="/User/action",description="获取所有权限信息")
         * @Method(allow={POST})
         * @ApiSuccess({"code":200,"result":[{"actionId":1,"menuId":0,"actionCode":"GEN_ACT_A","actionName":"常用操作"},{"actionId":2,"menuId":1,"actionCode":"USER_MA_A","actionName":"用户管理"},{"actionId":3,"menuId":1,"actionCode":"ACTION_LOG_A","actionName":"操作日志"},{"actionId":4,"menuId":2,"actionCode":"USER_ADD_A","actionName":"添加用户"},{"actionId":5,"menuId":2,"actionCode":"USER_LIST_A","actionName":"用户列表"},{"actionId":6,"menuId":2,"actionCode":"USER_DEL_A","actionName":"删除用户"}],"msg":null})
         * @return bool
         * @throws \EasySwoole\I18N\Exception\Exception
         * @throws \EasySwoole\ORM\Exception\Exception
         * @throws \Throwable
         */
        public function actionList(): bool
        {
            if (!$this->checkAction('ACTION_LIST_A')) {
                return false;
            }
            $res = AdminActionModel::create()->all();
            foreach ($res as $value) {
                $v = $value->toArray();
                $actionList[] = [
                    'actionId' => $v['action_id'],
                    'menuId' => $v['menu_id'],
                    'actionCode' => $v['action_code'],
                    'actionName' => I18N::getInstance()->translate($v['action_code']),
                ];
            }
            $this->writeJson(Status::CODE_OK, $actionList);
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
            $configAdminId = Config::getInstance()->getConf('TOP_ADMIN_ID');
            if ($adminId === $configAdminId) {
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