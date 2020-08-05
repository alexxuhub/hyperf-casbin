<?php
declare(strict_types=1);

namespace XDAppTest\Casbin;


use PHPUnit\Framework\TestCase;
use XDApp\Casbin\Middleware\CasbinMiddleware;

class CasbinMiddleTest  extends TestCase
{
    private $basePath;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->basePath = dirname(__DIR__, 1).'/';
    }

    public function testGetUserPermission(){
        $userName = 'policy_cate';
        $permissions = CasbinMiddleware::getPermissionForUser($userName,$this->basePath.'/examples/rbac_model.conf');
        $this->assertIsArray($permissions);
    }

    public function testGetUserRole(){
        $userName = 'admin';
        $role = CasbinMiddleware::getRoleForUser($userName,$this->basePath.'/examples/rbac_model.conf');
        $this->assertIsArray($role);
    }

    public function testAddRoleForUser(){
        $userName = '测试库';
        $role = 'admin';
        $res = CasbinMiddleware::addRoleForUser($userName,$role,$this->basePath.'/examples/rbac_model.conf');
        $this->assertIsBool($res);
    }

    public function testAddPermissionForUser(){
        $userName = 'policy_cate';
        $object = 'updateCate2';
        $action = 'update2';
        $res = CasbinMiddleware::addPermissonForUser($userName,$object,$action,$this->basePath.'/examples/rbac_model.conf');
        $this->assertIsBool($res);
    }


    public function testDelRoleForUser(){
        $userName = 'alice';
        $role = 'admin';
        $res = CasbinMiddleware::delRoleForUser($userName,$role,$this->basePath.'/examples/rbac_model.conf');
        $this->assertIsBool($res);
    }
    public function testDelPermissionForUser(){
        $userName = 'policy_cate';
        $object = 'addCate2';
        $action = 'add';
        $res = CasbinMiddleware::delPermissionForUser($userName,$object,$action,$this->basePath.'/examples/rbac_model.conf');
        var_dump($res);
        $this->assertIsBool($res);

    }

}