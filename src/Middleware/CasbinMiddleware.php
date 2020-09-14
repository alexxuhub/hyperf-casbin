<?php
declare(strict_types=1);

namespace XDApp\Casbin\Middleware;


use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use XDApp\Casbin\Service\CasbinVerifyService;
use XDApp\Casbin\Entity\Collector;
use XDApp\Casbin\Repository\CollectorRepository;

class CasbinMiddleware
{
    /**
     * 验证中间件 更新
     * @param ServerRequestInterface $request
     * @param string $confPath
     * @param string $table
     * @return bool
     * @throws \Exception
     */
    public static function filterAuth(ServerRequestInterface $request,string $confPath,bool $collect_open = false,string $table = 'auth_rule'):bool {
        $users = Context::get('user');
        $method = $request->getMethod();
        $urlPath  = $request->getUri()->getPath();
        $routes = ApplicationContext::getContainer()->get(DispatcherFactory::class)->getRouter('http')->getData();
        $funcObject = $routes[0][$method][$urlPath];
        $callBack  = $funcObject->callback;
        //callBack App\Controller\AssetsInfoController@condition
        $callBackArr = explode('@',$callBack);
        if ( $collect_open ){
            $object = $urlPath;
            $action = $method;
            $subject = $users['name'];
        }else {
            $db = ApplicationContext::getContainer()->get(Db::class);
            $repo = new CollectorRepository($db);
            $collect = $repo->findCollector($callBackArr[0],$callBackArr[1]);
            //不在权限收集器内，直接通过
            if (empty($collect)) return true;
            //存在，则进行判断是否拥有后续权限
            $object = $collect->object;
            $action = $collect->targetAction;
            $subject = $users['name'];
        }
        return self::initCasbinSrv($confPath,$table)->verifyCasbin($subject,$object,$action);
    }

    private static function initCasbinSrv(string $confPath,string $table):CasbinVerifyService{
        if (empty($confPath)) throw new \Exception("must set casbin model conf path");
        $casbin = CasbinVerifyService::getInstance();
        $casbin->setPolicyMode($casbin::MODE_DATABASE)
            ->setConfPath($confPath)
            ->setPolicyTable($table);
        return $casbin;
    }

    /**
     * 获取用户所有权限
     * @param string $userName
     * @param string $confPath
     * @param string $table
     * @return array
     * @throws \Exception
     */
    public static function getPermissionForUser(string $userName,string $confPath,string $table = 'auth_rule'){
        $casbin  = self::initCasbinSrv($confPath,$table);
        return  $casbin->getPermissionForUser($userName);
    }

    /**
     * 获取用户所有角色
     * @param string $userName
     * @param string $confPath
     * @param string $table
     * @return array
     * @throws \Exception
     */
    public static function getRoleForUser(string $userName,string $confPath,string $table = 'auth_rule'){
        $casbin  = self::initCasbinSrv($confPath,$table);
        return  $casbin->getRolesForUser($userName);
    }


    /**
     * 赋予用户角色
     * @param string $userName g:v0
     * @param string $role g:v1
     * @param string $confPath
     * @return bool
     * @throws \Exception
     */
    public static function addRoleForUser(string $userName,string $role,string $confPath,string $table = 'auth_rule'){
        $casbin = self::initCasbinSrv($confPath,$table);
        return $casbin->addRoleForUser($userName,$role);
    }

    /**
     * 赋予用户权限
     * @param string $pName p:v0
     * @param string $object p:v1
     *
     * @param string $action p:v2
     * @param string $confPath
     * @return bool
     * @throws \Exception
     */
    public static function addPermissonForUser(string $pName,string $object,string $action,string $confPath,string $table = 'auth_rule'){
        $casbin =  self::initCasbinSrv($confPath,$table);
        return $casbin->addPermissionForUser($pName,$object,$action);
    }

    /**
     * 删除用户角色
     * @param string $userName g:v0
     * @param string $role g:v1
     * @return bool
     * @throws \Exception
     */

    public static function delRoleForUser(string $userName,string $role,string $confPath,string $table = 'auth_rule'){
        $casbin = self::initCasbinSrv($confPath,$table);
        return $casbin->delRoleForUser($userName,$role);
    }

    /**
     * 删除用户权限
     * @param string $userName p:v0
     * @param string $object p:v1
     * @param string $action p:v2
     * @return bool
     * @throws \Exception
     */
    public static function delPermissionForUser(string $userName,string $object,string $action,string $confPath,string $table = 'auth_rule'){
        $casbin = self::initCasbinSrv($confPath,$table);
        return $casbin->delPermissionForUser($userName,$object,$action);
    }



}