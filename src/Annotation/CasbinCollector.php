<?php
declare(strict_types=1);

namespace XDApp\Casbin\Annotation;

use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Container;
use Hyperf\Di\MetadataCollector;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use XDApp\Casbin\Entity\Collector;
use XDApp\Casbin\Repository\CollectorRepository;


/**
 * @Annotation
 * @Target("ALL")
 */
class CasbinCollector extends AbstractAnnotation
{

    /**@var array*/
    private array $arr ; //属性数组
    public function __construct($value)
    {
        $this->arr = $value;
    }

    public function collectClass(string $className): void
    {
        $object = $this->arr['object']; // cate 对象
        $desc   = $this->arr['desc'];   // desc 对象描述
        $key    = md5($className);      //关联路由的唯一标识
    }
    public function collectMethod(string $className, ?string $target): void
    {
        if (env('COLLECTOR_OPEN')=='true') {
            $this->saveCollect($className, $target);
        }
    }

    public function saveCollect(string $key,string $target){
        $collect = new Collector();
        $collect->targetClass = $key;
        $collect->targetAction = $target;
        $collect->object = $this->arr['object'];
        $collect->description = $this->arr['desc'];
        $collect->targetDesc = ' ';
        CollectorRepository::save($collect);
    }


}