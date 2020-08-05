### hyperf-casbin
`composer require xd-casbin/casbin`
### 准备步骤
#### 1.创建策略表
     执行 migration/1_create_auth_rule.sql
#### 2.创建收集表（不希望自动收集注解可略过）
     执行 migration/1_create_casbin_collector.up.sql
#### 3.env配置文件
    必要的数据库配置之外
    新增 COLLECTOR_OPEN 
        true:会将注解自动收集到collector表中
        false:不进行处理
### 中间件使用
#### 1.验证
##### 1.1.注解
     在类内方法中使用
     /**
      * @CasbinCollector(object="cateGoryGet",desc="获取资产分类")
      */
     COLLECTOR_OPEN为true时 
     每一次改动都会触发框架的自动收集。
     并且当object改变时，如果auth_rule配置了权限也会同步进行修改。
##### 1.2.使用
     CasbinMiddleware::filterAuth($request,$casbinModelPath);
     $request: Psr\Http\Message\ServerRequestInterface
     $casbinModelPath:casbin模型路径
     中间件会自动进行匹配路由去collect收集器拿到object，加载auth_rule进行判断权限
     
##### 1.3.特别注意
     如果COLLECTOR_OPEN不为true，需要用户手动去auth_rule表正确填写
     此时对应casbin中的
     subject: user
     object:  url
     $action : method
#### 2.角色权限接口

##### 2.1 获取用户角色
     /**
          * 获取用户所有角色
          * @param string $userName
          * @param string $confPath
          * @param string $table
          * @return array
          * @throws \Exception
          */    
     CasbinMiddleware::getRoleForUser()
##### 2.2 获取角色权限
     /**
          * 获取用户所有权限
          * @param string $userName
          * @param string $confPath
          * @param string $table
          * @return array
          * @throws \Exception
          */
     CasbinMiddleware::getPermissionForUser()
##### 2.3 增加用户角色
      /**
          * 赋予用户角色
          * @param string $userName g:v0
          * @param string $role g:v1
          * @param string $confPath
          * @return bool
          * @throws \Exception
          */ 
     CasbinMiddleware::addRoleForUser()
##### 2.4 增加角色权限
      /**
          * 赋予用户权限
          * @param string $pName p:v0
          * @param string $object p:v1
          * @param string $action p:v2
          * @param string $confPath
          * @return bool
          * @throws \Exception
          */ 
     CasbinMiddleware::addPermissonForUser()
##### 2.5 删除用户角色
     /**
          * 删除用户角色
          * @param string $userName g:v0
          * @param string $role g:v1
          * @return bool
          * @throws \Exception
          */
     CasbinMiddleware::delRoleForUser()
##### 2.6 删除角色权限  
     /**
          * 删除用户权限
          * @param string $userName p:v0
          * @param string $object p:v1
          * @param string $action p:v2
          * @return bool
          * @throws \Exception
          */  
     CasbinMiddleware::delPermissionForUser()
          


    

    