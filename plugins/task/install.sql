CREATE TABLE `le_cart22` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `goods_id` bigint(20) NOT NULL COMMENT '商品ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `goods_param` varchar(255) NOT NULL DEFAULT '' COMMENT '商品规格',
  `goods_number` int(10) NOT NULL DEFAULT '1' COMMENT '商品数量',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `goods_name` varchar(255) NOT NULL COMMENT '商品名称',
  `goods_image` varchar(255) NOT NULL COMMENT '商品图片',
  `show_goods_param` varchar(255) NOT NULL COMMENT '商品规格',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `商品id` (`goods_id`) USING BTREE,
  KEY `用户id` (`UID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;