<?php
declare(strict_types=1);

namespace XDApp\Casbin\Repository;


use Hyperf\DbConnection\Db;
use XDApp\Casbin\Entity\Collector;

class CollectorRepository
{
    protected Db $db;
    protected static string $table = 'casbin_collector';
    protected static string $rule_table = 'auth_rule';
    public function __construct(Db $db , array $config = [])
    {
        if (isset($config['table'])) {
            self::$table = $config['table'];
        }
        $this->db = $db;
    }
    public function setDb(Db $db)
    {
        $this->db = $db;
    }
    public function getAll(){
        return $this->db->table(self::$table)->select(['specificKey','object','description','targetAction','targetDesc'])->get();
    }

    public function findCollector(string $targetClass,string $targetAction):?Collector{
        $model = $this->db->table(self::$table)->where('targetClass','=',$targetClass)->where('targetAction','=',$targetAction)->first();
        if (empty($model)) return null;
        $collect = new Collector();
        $collect->targetClass = $targetClass;
        $collect->targetAction = $targetAction;
        $collect->object = (string)$model->object;
        $collect->description = (string)$model->description;
        $collect->targetDesc = (string)$model->targetDesc;
        return $collect;
    }

    public static  function save(Collector $collector){
        //注意此处注解收集的时候，container还没有开始，因此只能使用pdo连接数据库保存数据。
        $pdoHandler =  self::pdoInit();
        $fetch = $pdoHandler->prepare('select * from ' . self::$table .' where targetClass= ? and targetAction= ?');
        $fetch->execute([$collector->targetClass,$collector->targetAction]);
        $res = $fetch->fetch();
        if ($res){
            //udpate
            $stat = $pdoHandler->prepare('update '.self::$table.' SET object=?,description=?,targetDesc=? WHERE targetClass=? and targetAction=?');
            $stat->execute([$collector->object,$collector->description,$collector->targetDesc,$collector->targetClass,$collector->targetAction]);
            //增加判断，看下当前权限是否已存在authRule表，然后进行相应的权限更新;
            $oldObject  = $res['object'];
            $auth = $pdoHandler->prepare('select * from '.self::$rule_table.' where v1=? and v2=?');
            $auth->execute([$oldObject,$collector->targetAction]);
            if ($auth->fetch()){
                $stat = $pdoHandler->prepare('update '.self::$rule_table.' set v1=? where v1=? and v2=?');
                $stat->execute([$collector->object,$oldObject,$collector->targetAction]);
            }
        }else{
            //insert
            $stat = $pdoHandler->prepare('insert into '.self::$table.' (targetClass,object,description,targetAction,targetDesc)values(?,?,?,?,?)');
            $stat->execute([$collector->targetClass,$collector->object,$collector->description,$collector->targetAction,$collector->targetDesc]);
        }
        if (!$stat){
            error($stat->errorInfo());
        }
        return $stat;
    }

    private static function pdoInit(){
        $dbHost = env('DB_HOST');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');
        $dbPort = env('DB_PORT');
        $dbDatabase = env('DB_DATABASE');
        $dsn="mysql:host={$dbHost};port={$dbPort};dbname={$dbDatabase}";
        $_opts_values = array(\PDO::ATTR_PERSISTENT=>true,\PDO::ATTR_ERRMODE=>2,\PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8');
        return new \PDO($dsn,$dbUser,$dbPassword,$_opts_values);
    }



}