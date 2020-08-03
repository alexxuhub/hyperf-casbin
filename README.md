### hyperf-casbin
`composer require xd-casbin/casbin`
### init database
    migration *.sql
### config配置
    COLLECTOR_OPEN 如果为true，则会自动收集注解方法
    注解方法的使用方式为@CasbinCollector(object="",desc="")
    如果为false，则不会自动收集注解方法

    