create table `casbin_collector`(
   `id` int unsigned not null primary key auto_increment comment '主键id',
   `targetClass` varchar(255) not null default '' comment '目标类',
   `targetAction` varchar(255) not null default '' comment '目标方法',
   `object` varchar(255) not null default '' comment '注解自定义对象名称',
   `description` varchar(255) not null default '' comment '文字描述',
   `targetDesc` varchar(255) not null default '' comment '方法描述'
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'CasBin资源收集';