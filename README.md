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
#### 1.注解
     在类内方法中使用
     /**
      * @CasbinCollector(object="cateGoryGet",desc="获取资产分类")
      */
     COLLECTOR_OPEN为true时 
     每一次改动都会触发框架的自动收集。
     并且当object改变时，如果auth_rule配置了权限也会同步进行修改。
#### 2.使用
     CasbinMiddleware::filterAuth($request,$casbinModelPath);
     $request: Psr\Http\Message\ServerRequestInterface
     $casbinModelPath:casbin模型路径
     中间件会自动进行匹配路由去collect收集器拿到object，加载auth_rule进行判断权限
     
### 特别注意
     如果COLLECTOR_OPEN不为true，需要用户手动去auth_rule表正确填写
     CasbinCollector(object="",desc="")中的object
     此时对应casbin中的
     subject: user
     object:  url
     $action : method
     
          


    

    