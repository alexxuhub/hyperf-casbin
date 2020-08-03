<?php

declare(strict_types=1);

namespace XDAppTest\Casbin;


use Casbin\Enforcer;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\ApplicationContext;
use PHPUnit\Framework\TestCase;
use XDApp\Casbin\Adapter\DatabaseAdapter;
use XDApp\Casbin\Repository\RuleRepository;

class DatabaseAdapterTest extends TestCase
{
    public function testSavePolicy()
    {
        $container = ApplicationContext::getContainer();
        $db = $container->get(Db::class);
        $repo = new RuleRepository($db, ['table' => 'auth_rule']);
        $adapter = new DatabaseAdapter($repo);
        $enforce = new Enforcer(__DIR__ . "/../examples/rbac_model.conf");
        $enforce->setAdapter($adapter);
        $enforce->loadPolicy();
        $enforce->addRoleForUser("李晨", "admin");
        $enforce->addRoleForUser("王明", "admin");
        $enforce->addRoleForUser("极光", "admin");
        $enforce->deleteRole('admin');
        $this->assertEmpty($enforce->getAllRoles());
    }
}