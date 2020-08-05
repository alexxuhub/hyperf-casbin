<?php
declare(strict_types=1);

namespace XDApp\Casbin\Service;
use Casbin\Enforcer;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;
use PhpParser\Node\Scalar\MagicConst\Dir;
use XDApp\Casbin\Adapter\DatabaseAdapter;
use XDApp\Casbin\Exception\CasbinVerifyException;
use XDApp\Casbin\Repository\RuleRepository;

/**
 * casbin对外验证方法
 */

class CasbinVerifyService
{

    private static $_instance  =null;

    /**设置加载模式*/
    const MODE_FILE = 2;
    const MODE_DATABASE = 1;
    const MODES = [
        self::MODE_FILE => '文件加载',
        self::MODE_DATABASE=> '数据库加载',
    ];

    /**@var string*/
    private string $table;

    /**@var string*/
    private string $confPath;

    /**@var string*/
    private string $policyPath;

    /**@var int*/
    private int $policyMode;

    /**
     */
    public static function getInstance(){
        if (is_null(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __clone()
    {
        die("clone is not allowed" . E_USER_ERROR);
    }


    /**
     * 策略加载表
     * @param string $table
     * @return self
     */
    public function setPolicyTable(string $table):self {
        if (empty($this->table)){
            $this->table = $table;
        }
        return $this;
    }

    /**
     * 加载conf路径
     * @param string $confPath
     * @return self
     */
    public function setConfPath(string $confPath):self {
        if (empty($this->confPath)){
            $this->confPath = $confPath;
        }
        return $this;
    }

    /**
     * 文件加载策略路径
     * @param string $policyPath
     * @return self
     */
    public function setPolicyPath(string $policyPath):self {
        if (empty($this->policyPath)){
            $this->policyPath = $policyPath;
        }
        return $this;
    }

    /**
     * 设置策略加载模式
     * @param int $mode
     * @return self
     */
    public function setPolicyMode(int $mode = self::MODE_DATABASE):self {
        if (empty($this->policyMode)){
            $this->policyMode = $mode;
        }
        return $this;
    }

    /**
     * 规则验证
     * @param string $subject  对象
     * @param string $object 资源
     * @param string $policy 策略
     * @throws \Exception
     * @return bool
     */
    public function verifyCasbin(string $subject,string $object,string $policy):bool {

        if (empty($this->policyMode)) throw new CasbinVerifyException("must set policyMode");
        if (empty($this->confPath))  throw new CasbinVerifyException("must set confPath");
        switch ($this->policyMode){
            case self::MODE_FILE:
                return  $this->verifyByPath($subject,$object,$policy);
                break;
            case self::MODE_DATABASE:
                return $this->verifyByDatabase($subject,$object,$policy);
                break;
            default:
                break;
        }
        return false;
    }

    private function verifyByPath(string $subject,string $object,string $policy):bool {
        if (empty($this->policyPath)) throw new CasbinVerifyException("must set policyPath");
        $enfore = new Enforcer($this->confPath,$this->policyPath);
        return $enfore->enforce($subject,$object,$policy);
    }

    private function verifyByDatabase(string $subject,string $object,string $policy):bool {
        if (empty($this->table)) throw new \Exception("must set policyTable");
        $db = ApplicationContext::getContainer()->get(Db::class);
        $repo = new RuleRepository($db,['table'=>$this->table]);
        $adapter = new DatabaseAdapter($repo);
        $enfore = new Enforcer($this->confPath,$adapter);
        return $enfore->enforce($subject,$object,$policy);
    }


    private function initEnforce(){
        $db = ApplicationContext::getContainer()->get(Db::class);
        $repo = new RuleRepository($db,['table'=>$this->table]);
        $adapter = new DatabaseAdapter($repo);
        $enfore = new Enforcer($this->confPath,$adapter);
        return $enfore;
    }


    public function getRolesForUser(string $userName){
        return $this->initEnforce()->getRolesForUser($userName);
    }

    public function getPermissionForUser(string $userName){
        return $this->initEnforce()->getPermissionsForUser($userName);
    }

    public function addRoleForUser(string $userName,string $role){
        return $this->initEnforce()->addRoleForUser($userName,$role);
    }

    public function addPermissionForUser(string $userName,string $object,string $action){
        return $this->initEnforce()->addPermissionForUser($userName,$object,$action);
    }

    public function delRoleForUser(string $userName,string $role){
        return $this->initEnforce()->deleteRoleForUser($userName,$role);
    }
    public function delPermissionForUser(string $userName,string $object,string $action){
        return $this->initEnforce()->deletePermissionForUser($userName,$object,$action);
    }
}