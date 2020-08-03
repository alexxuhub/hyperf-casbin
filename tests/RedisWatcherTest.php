<?php

declare(strict_types=1);

namespace XDAppTest\Casbin;


use Casbin\Enforcer;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;
use PHPUnit\Framework\TestCase;
use XDApp\Casbin\Watcher\RedisWatcher;
use function foo\func;

class RedisWatcherTest extends TestCase
{
    public function testSubscribe()
    {
        $container = ApplicationContext::getContainer();
        $redis = $container->get(RedisFactory::class)->get('default');
        $watcher = new RedisWatcher($redis, ['squashMessages' => true, 'squashTime' => 2 * 1000, 'ignoreSelf' => false]);
        $watcher->setUpdateCallback(function () {
            echo "callback";
        });

        $watcher->update();
    }

    public function testRedisWatcher() {
        $container = ApplicationContext::getContainer();
        $redis = $container->get(RedisFactory::class)->get('default');
        $enforce = new Enforcer(__DIR__ . "/../examples/rbac_model.conf", __DIR__ . "/../examples/rbac_policy.csv");
        $watcher = new RedisWatcher($redis, ['squashMessages' => true, 'squashTime' => 2 * 1000, 'ignoreSelf' => false]);
        $enforce->setWatcher($watcher);
        $watcher->setUpdateCallback(function () use ($enforce) {
            $enforce->addPermissionForUser('alice', 'data1', 'invalid');
            $rs = $enforce->enforce('alice', 'data1', 'invalid');
            $this->assertTrue($rs);
        });
        $watcher->update();
        //need sleep
        sleep(3);
        $enforce->savePolicy();
    }

    public function testPermission(){
        $container = ApplicationContext::getContainer();
        $redis = $container->get(RedisFactory::class)->get('default');
        $enforce = new Enforcer(__DIR__ . "/../examples/rbac_model.conf", __DIR__ . "/../examples/assets_policy.csv");
        $this->assertIsBool($enforce->enforce('bob', 'info', 'put'));
    }

    public function testCasbinVerify(){
        $enfore = new Enforcer(__DIR__.'/../src/Conf/basic_rbac.conf',__DIR__.'/../src/Conf/basic_rbac.csv');
        $res = $enfore->enforce('admin','/api/v1/category','POST');
        $roles = $enfore->getRolesForUser('admin');
        var_dump($roles);
        $this->assertIsBool($res);
    }
}
