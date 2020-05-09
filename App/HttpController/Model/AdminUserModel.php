<?php

    namespace App\HttpController\Model;

    use EasySwoole\ORM\Utility\Schema\Table;

    /**
     * Class AdminUserModel
     * @package App\HttpController\Model
     * @author shuimengzhi
     * @store 后台管理员信息
     */
    class AdminUserModel extends \EasySwoole\ORM\AbstractModel
    {
        /**
         * @var string
         */
        protected $tableName = 'admin_user';
        private $admin_name;
        private $password;
        private $email;
        private $create_time;

        /**
         * @param bool $isCache
         * @return Table
         */
        public function schemaInfo(bool $isCache = true): Table
        {
            $table = new Table($this->tableName);
            $table->colInt('admin_id')->setIsPrimaryKey(true)->setIsAutoIncrement()
                ->setColumnComment('后台管理员ID');
            $table->colVarChar('admin_name', 30)->setIsNotNull()->setIsUnique()
                ->setColumnComment('后台管理员名称');
            $table->colVarChar('password', 60)->setIsNotNull()
                ->setColumnComment('密码');
            $table->colVarChar('email', 60)->setIsNotNull()
                ->setColumnComment('Email');
            $table->colInt('create_time', 30)->setIsNotNull()
                ->setColumnComment('注册时间');
            $table->colInt('last_time', 30)
                ->setColumnComment('最后登录时间');
            $table->colVarChar('last_ip', 30)
                ->setColumnComment('最后登录的IP地址');
            $table->colText('action_list')->setIsNotNull()
                ->setColumnComment('授权的功能权限');
            $table->colVarChar('menu_list',200)
                ->setColumnComment('该用户能显示的菜单列表');
            $table->colVarChar('token',350)->setIsUnique()
                ->setColumnComment('token');
            return $table;
        }
    }