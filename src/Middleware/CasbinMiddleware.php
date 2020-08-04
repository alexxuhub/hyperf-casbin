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
    public static function filterAuth(ServerRequestInterface $request,string $confPath,string $table = 'auth_rule'):bool {
         $users = Context::get('user');
         $method = $request->getMethod();
         $urlPath  = $request->getUri()->getPath();
         $routes = ApplicationContext::getContainer()->get(DispatcherFactory::class)->getRouter('http')->getData();
         $funcObject = $routes[0][$method][$urlPath];
         $callBack  = $funcObject->callback;
         //callBack App\Controller\AssetsInfoController@condition
         $callBackArr = explode('@',$callBack);
         $db = ApplicationContext::getContainer()->get(Db::class);
         $repo = new CollectorRepository($db);
         $collect = $repo->findCollector($callBackArr[0],$callBackArr[1]);
         //不在权限收集器内，直接通过
         if (empty($collect) && env('COLLECTOR_OPEN') == 'true') return true;
         //存在，则进行判断是否拥有后续权限
         $object = $collect->object;
         $action = $collect->targetAction;
         $subject = $users['name'];

         $casbin = CasbinVerifyService::getInstance();
         $casbin->setPolicyMode($casbin::MODE_DATABASE)
           ->setConfPath($confPath)
           ->setPolicyTable($table);
        return $casbin->verifyCasbin($subject,$object,$action);
    }
}