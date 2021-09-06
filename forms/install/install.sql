SET NAMES utf8mb4;

CREATE TABLE `heshop_initialize_prefix_account`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `mobile` bigint(11) NULL DEFAULT NULL COMMENT '手机号',
  `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '密码',
  `nickname` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '昵称姓名',
  `roles` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '角色列表',
  `format` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '规则格式',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '帐号状态',
  `is_deleted` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除',
  `created_time` bigint(10) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除事件',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '头像',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '姓名',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '账号类型 1超管  2商家',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `form` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '注册表单数据',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_cart`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `goods_id` bigint(20) NOT NULL COMMENT '商品ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `goods_param` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品规格',
  `goods_number` int(10) NOT NULL DEFAULT 1 COMMENT '商品数量',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `goods_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品名称',
  `goods_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品图片',
  `show_goods_param` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品规格',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商品id`(`goods_id`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_coupon`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '优惠券名称',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '优惠券类型：1=满减，2=折扣',
  `discount` decimal(10, 2) NOT NULL DEFAULT 10.00 COMMENT '折扣 type=2时',
  `total_num` bigint(10) NOT NULL COMMENT '发放总量',
  `expire_type` tinyint(1) NOT NULL COMMENT '用券类型 1=领取后N天过期，2=指定有效期',
  `expire_day` bigint(10) NOT NULL DEFAULT 1 COMMENT '有效天数，expire_type=1时',
  `begin_time` bigint(10) NOT NULL DEFAULT 0 COMMENT '用券开始时间',
  `end_time` bigint(10) NOT NULL DEFAULT 0 COMMENT '用券结束时间',
  `min_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '门槛金额',
  `sub_price` decimal(10, 2) NOT NULL COMMENT '优惠金额',
  `appoint_type` tinyint(1) NOT NULL COMMENT '适用商品 1:全场通用 2:指定商品可用 3:指定分类可用 4:指定商品不可用 5:指定分类不可用',
  `give_limit` tinyint(1) NULL DEFAULT NULL COMMENT '每人限领 0无限制',
  `enable_share` tinyint(1) NOT NULL DEFAULT 0 COMMENT '分享设置 1开启 0关闭',
  `expire_remind` int(11) NULL DEFAULT NULL COMMENT '到期提醒',
  `enable_refund` tinyint(1) NOT NULL DEFAULT 0 COMMENT '退款设置 1开 0关',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '使用说明',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_recycle` tinyint(1) NULL DEFAULT 0 COMMENT '是否在回收站',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  `appoint_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '指定数据',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '上下架状态  0下架 1上架',
  `over_num` bigint(10) NOT NULL COMMENT '剩余量',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_fitment`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '关键字',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

INSERT INTO `heshop_initialize_prefix_fitment` VALUES (1, 'themeColor', 'orange_theme', '98c08c25f8136d590c', 1612335658, 1616060722, NULL, 0);
INSERT INTO `heshop_initialize_prefix_fitment` VALUES (2, 'tabbar', '{\"tabbarStyle\":2,\"background_color\":\"#FFFFFF\",\"inactive_color\":\"#1A1818\",\"active_color\":\"#f5212d\",\"data\":[{\"text\":\"首页\",\"page\":\"setup\",\"iconPath\":\"HESHOP_URL_STRING/static/images/tabbar/nav_home_normal.png\",\"selectedIconPath\":\"HESHOP_URL_STRING/static/images/tabbar/nav_home_selected.png\",\"link\":{\"name\":\"店铺首页\",\"path\":\"/pages/index/index\",\"param\":{},\"index\":0,\"extend\":false}},{\"pagePath\":\"\",\"iconPath\":\"HESHOP_URL_STRING/static/images/tabbar/nav_classification_normal.png\",\"selectedIconPath\":\"HESHOP_URL_STRING/static/images/tabbar/nav_classification_selected.png\",\"text\":\"分类\",\"link\":{\"name\":\"全部商品\",\"path\":\"/pages/goods/list\",\"param\":{},\"index\":1,\"extend\":false}},{\"pagePath\":\"\",\"iconPath\":\"HESHOP_URL_STRING/static/images/tabbar/nav_shopping-cart_normal.png\",\"selectedIconPath\":\"HESHOP_URL_STRING/static/images/tabbar/nav_shopping-cart_selected.png\",\"text\":\"购物车\",\"link\":{\"name\":\"购物车\",\"path\":\"/pages/cart/index\",\"param\":{},\"index\":4,\"extend\":false}},{\"pagePath\":\"\",\"iconPath\":\"HESHOP_URL_STRING/static/images/tabbar/nav_personal-center_normal.png\",\"selectedIconPath\":\"HESHOP_URL_STRING/static/images/tabbar/nav_personal-center_selected.png\",\"text\":\"我\",\"link\":{\"name\":\"个人中心\",\"path\":\"/pages/user/index\",\"param\":{},\"index\":5,\"extend\":false}}]}', '98c08c25f8136d590c', 1, 1616062061, NULL, 0);

CREATE TABLE `heshop_initialize_prefix_fitment_page`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '微页面名称',
  `goods_number` smallint(3) NOT NULL DEFAULT 0 COMMENT '商品数量',
  `visit_number` int(10) NOT NULL DEFAULT 0 COMMENT '访问次数',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '页面配置',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1默认页面',
  `background` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#F7F7F7' COMMENT '背景色',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '微页面标题',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

INSERT INTO `heshop_initialize_prefix_fitment_page` VALUES (56, '首页', 0, 0, '[{\"name\":\"banner\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/banner-icon.png\",\"title\":\"轮播图\",\"content\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/a10756074b56634da47d413465031b8b.png\",\"link\":{}},{\"url\":\"\",\"link\":{}}],\"facade\":{\"chamfer_style\":2,\"indicator_style\":1,\"indicator_align\":\"right\",\"indicator_color\":\"#f5212d\"}},{\"name\":\"navigation\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/navigation-icon.png\",\"title\":\"图文导航\",\"content\":{\"style\":1,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/70d949b83c78c77cd5af558345ba79a7.png\",\"title\":\"彩妆\",\"link\":{\"name\":\"购物车\",\"path\":\"/pages/cart/index\",\"param\":{},\"index\":4,\"extend\":false}},{\"url\":\"HESHOP_URL_STRING/static/images/template/54ec56b72951d3eca3aabc0dcc40f769.png\",\"title\":\"面膜\",\"link\":{\"name\":\"全部商品\",\"path\":\"/pages/goods/list\",\"param\":{},\"index\":1,\"extend\":false}},{\"url\":\"HESHOP_URL_STRING/static/images/template/d5603eee479a407c9b7e4518d56854be.png\",\"title\":\"电器\",\"link\":{\"name\":\"微页面\",\"path\":\"/pages/page/index\",\"param\":{\"id\":51,\"title\":\"世平测试专用\",\"name\":\"首页\",\"status\":0},\"index\":7,\"extend\":true}},{\"url\":\"HESHOP_URL_STRING/static/images/template/a0385b5f5b863ee26f6bb37eca52e872.png\",\"title\":\"护理\",\"link\":{}}]},\"facade\":{\"nav_style\":1,\"nav_line\":2,\"nav_line_mun\":4,\"active_color\":\"#f5212d\",\"indicator_style\":1,\"background_color\":\"#FFFFFF\",\"text_color\":\"#333333\"}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":4,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/34e8694117ac342d348ad7b084b5779d.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/eae6e01e25dcbdc07648106571f10a3d.png\",\"link\":{\"name\":\"全部商品\",\"path\":\"/pages/goods/list\",\"param\":{},\"index\":1,\"extend\":false}},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/758aee7474e05fb4f8a691548240c6ee.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"title\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/title-icon.png\",\"title\":\"标题栏\",\"content\":{\"style\":1,\"title\":\"为您推荐\",\"subtitle\":\"子标题\",\"is_more\":true,\"moretitle\":\"查看更多\",\"morelink\":{}},\"facade\":{\"title_color\":\"#333333\",\"title_font_size\":14,\"subtitle_color\":\"#999999\",\"subtitle_font_size\":12,\"more_color\":\"#999999\",\"margin\":20}},{\"name\":\"goods\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/goods-icon.png\",\"title\":\"商品\",\"content\":{\"type\":1,\"goods\":[],\"group\":{\"id\":0,\"name\":\"\",\"limit\":20},\"is_title\":true,\"is_price\":true,\"is_button\":true},\"facade\":{\"list_style\":2,\"card_style\":2,\"chamfer_style\":1,\"margin\":16,\"padding\":20}},{\"name\":\"wechat\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/wechat-icon.png\",\"title\":\"微信公众号\",\"content\":{},\"facade\":{\"line_style\":\"\",\"line_color\":\"\",\"high_style\":20}}]', 1, '#F7F7F7','98c08c25f8136d590c', 1616038123, 1616479829, NULL, 0, '默认模板');

CREATE TABLE `heshop_initialize_prefix_fitment_template`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '封面',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `background` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#F7F7F7' COMMENT '背景色',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `writer` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '作者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

INSERT INTO `heshop_initialize_prefix_fitment_template` VALUES (1, '默认模板', 'https://qmxq.oss-cn-hangzhou.aliyuncs.com/template/1.jpg', '[{\"name\":\"banner\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/banner-icon.png\",\"title\":\"轮播图\",\"content\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/a10756074b56634da47d413465031b8b.png\",\"link\":{}},{\"url\":\"\",\"link\":{}}],\"facade\":{\"chamfer_style\":2,\"indicator_style\":1,\"indicator_align\":\"right\",\"indicator_color\":\"#f5212d\"}},{\"name\":\"navigation\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/navigation-icon.png\",\"title\":\"图文导航\",\"content\":{\"style\":1,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/70d949b83c78c77cd5af558345ba79a7.png\",\"title\":\"彩妆\",\"link\":{\"name\":\"购物车\",\"path\":\"/pages/cart/index\",\"param\":{},\"index\":4,\"extend\":false}},{\"url\":\"HESHOP_URL_STRING/static/images/template/54ec56b72951d3eca3aabc0dcc40f769.png\",\"title\":\"面膜\",\"link\":{\"name\":\"全部商品\",\"path\":\"/pages/goods/list\",\"param\":{},\"index\":1,\"extend\":false}},{\"url\":\"HESHOP_URL_STRING/static/images/template/d5603eee479a407c9b7e4518d56854be.png\",\"title\":\"电器\",\"link\":{\"name\":\"微页面\",\"path\":\"/pages/page/index\",\"param\":{\"id\":51,\"title\":\"世平测试专用\",\"name\":\"首页\",\"status\":0},\"index\":7,\"extend\":true}},{\"url\":\"HESHOP_URL_STRING/static/images/template/a0385b5f5b863ee26f6bb37eca52e872.png\",\"title\":\"护理\",\"link\":{}}]},\"facade\":{\"nav_style\":1,\"nav_line\":2,\"nav_line_mun\":4,\"active_color\":\"#f5212d\",\"indicator_style\":1,\"background_color\":\"#FFFFFF\",\"text_color\":\"#333333\"}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":4,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/34e8694117ac342d348ad7b084b5779d.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/eae6e01e25dcbdc07648106571f10a3d.png\",\"link\":{\"name\":\"全部商品\",\"path\":\"/pages/goods/list\",\"param\":{},\"index\":1,\"extend\":false}},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/758aee7474e05fb4f8a691548240c6ee.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"title\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/title-icon.png\",\"title\":\"标题栏\",\"content\":{\"style\":1,\"title\":\"为您推荐\",\"subtitle\":\"子标题\",\"is_more\":true,\"moretitle\":\"查看更多\",\"morelink\":{}},\"facade\":{\"title_color\":\"#333333\",\"title_font_size\":14,\"subtitle_color\":\"#999999\",\"subtitle_font_size\":12,\"more_color\":\"#999999\",\"margin\":20}},{\"name\":\"goods\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/goods-icon.png\",\"title\":\"商品\",\"content\":{\"type\":1,\"goods\":[],\"group\":{\"id\":0,\"name\":\"\",\"limit\":20},\"is_title\":true,\"is_price\":true,\"is_button\":true},\"facade\":{\"list_style\":2,\"card_style\":2,\"chamfer_style\":1,\"margin\":16,\"padding\":20}},{\"name\":\"wechat\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/wechat-icon.png\",\"title\":\"微信公众号\",\"content\":{},\"facade\":{\"line_style\":\"\",\"line_color\":\"\",\"high_style\":20}}]','#F7F7F7', 1606122453, 1606123444, NULL, 0, '浙江禾成云计算有限公司');
INSERT INTO `heshop_initialize_prefix_fitment_template` VALUES (2, '数码', 'https://qmxq.oss-cn-hangzhou.aliyuncs.com/template/4.jpg', '[{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/4474faf49865412230a269e216c2d7b0.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":3,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/d93536fa973fb2356b1f547e1682bafb.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/e69d7af91c301c0ccbcfb426dd1d6784.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/619615a44b7a349a9d7b8efac576c5e7.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"title\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/title-icon.png\",\"title\":\"标题栏\",\"content\":{\"style\":1,\"title\":\"热卖单品\",\"subtitle\":\"子标题\",\"is_more\":true,\"moretitle\":\"查看更多\",\"morelink\":{}},\"facade\":{\"title_color\":\"#333333\",\"title_font_size\":14,\"subtitle_color\":\"#999999\",\"subtitle_font_size\":12,\"more_color\":\"#999999\",\"margin\":20}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":5,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/af9617318a46dc899682b8d504e43847.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/0b80365f83f9b6e5bc941c57c27c79e8.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/57f06451c7da3d5e03c13bb3bd3b6b75.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/01dea483e96b264831efa18c52c93e00.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/bf13baf5c5b7c31fbe02431a50401893.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"goods\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/goods-icon.png\",\"title\":\"商品\",\"content\":{\"type\":1,\"goods\":[],\"group\":{\"id\":0,\"name\":\"\",\"limit\":20},\"is_title\":true,\"is_price\":true,\"is_button\":true},\"facade\":{\"list_style\":4,\"card_style\":1,\"chamfer_style\":1,\"margin\":20,\"padding\":16}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/08ca073e2eff7cc63a741cb14ffe4728.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"goods\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/goods-icon.png\",\"title\":\"商品\",\"content\":{\"type\":1,\"goods\":[],\"group\":{\"id\":0,\"name\":\"\",\"limit\":20},\"is_title\":true,\"is_price\":true,\"is_button\":true},\"facade\":{\"list_style\":4,\"card_style\":1,\"chamfer_style\":1,\"margin\":20,\"padding\":16}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/195bc3a1c11b749e16d0737a79cb9bb8.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":2,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/c7d458758adac079a3c50c60ee6042f3.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/0ec2baa82e1d6a09e51049cf714559ef.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"wechat\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/wechat-icon.png\",\"title\":\"微信公众号\",\"content\":{},\"facade\":{\"line_style\":\"\",\"line_color\":\"\",\"high_style\":20}}]','#F7F7F7', 1616048277, 1616048277, NULL, 0, '浙江禾成云计算有限公司');
INSERT INTO `heshop_initialize_prefix_fitment_template` VALUES (3, '服装', 'https://qmxq.oss-cn-hangzhou.aliyuncs.com/template/5.jpg', '[{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":5,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/0582da9deb8b3687366d7a59b6f8eef7.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/0cee778c3e5818966f26aec497c86c49.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/26fe99ee7020a4c317c20685eaa0ba93.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/4bea7ee0241ca62b65f5732894502500.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/131ee6c4563b7cd306d83a45e58774b8.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":5,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/06c3275feb4c097cf02a906e37a684ce.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/899d672731cbf3bec05fe03f68f24bb4.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/f4c78cf8b1f44a9228a93eed2b0bd591.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/d7c90aac3a55b8adbd0d1fac3eea10e2.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/37e002ee6fa4d4171bb576cda6901968.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":2,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/591e72db7b47bd1a2202aecb74f2cec8.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/0318418713a043288c21de05ec21379e.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/7c0ac5c3b5794437f1c7e503ba539e54.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":2,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/87b032217446ba462ab42beb56fd459e.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/002495e71ba948120c71764a5f028cc1.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":2,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/f010967c7ebf7e315b8fccc999c0beb6.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/c5d7baf72cc8e29cdfcfccd34b8fe8d9.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"title\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/title-icon.png\",\"title\":\"标题栏\",\"content\":{\"style\":1,\"title\":\"为你推荐\",\"subtitle\":\"子标题\",\"is_more\":true,\"moretitle\":\"查看更多\",\"morelink\":{}},\"facade\":{\"title_color\":\"#DD6B1E\",\"title_font_size\":14,\"subtitle_color\":\"#999999\",\"subtitle_font_size\":12,\"more_color\":\"#999999\",\"margin\":20}},{\"name\":\"goods\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/goods-icon.png\",\"title\":\"商品\",\"content\":{\"type\":1,\"goods\":[],\"group\":{\"id\":0,\"name\":\"\",\"limit\":20},\"is_title\":true,\"is_price\":true,\"is_button\":true},\"facade\":{\"list_style\":3,\"card_style\":1,\"chamfer_style\":1,\"margin\":20,\"padding\":20}},{\"name\":\"wechat\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/wechat-icon.png\",\"title\":\"微信公众号\",\"content\":{},\"facade\":{\"line_style\":\"\",\"line_color\":\"\",\"high_style\":20}}]','#F7F7F7', 1616048277, 1616048277, NULL, 0, '浙江禾成云计算有限公司');
INSERT INTO `heshop_initialize_prefix_fitment_template` VALUES (4, '生鲜', 'https://qmxq.oss-cn-hangzhou.aliyuncs.com/template/3.jpg', '[{\"name\":\"search\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/search-icon.png\",\"title\":\"搜索框\",\"content\":{\"style\":1,\"text\":\"\"},\"facade\":{\"border_style\":2,\"text_align\":\"center\",\"background_color\":\"#7BC63E\",\"border_color\":\"#FFFFFF\",\"icon_color\":\"#999999\",\"text_color\":\"#999999\",\"high_style\":20}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/679be0fe7db66cffd7a0262f7c8606e7.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"navigation\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/navigation-icon.png\",\"title\":\"图文导航\",\"content\":{\"style\":1,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/3021eb04ab25278761440f3ff971a298.png\",\"title\":\"肉禽蛋类\",\"link\":{}},{\"url\":\"HESHOP_URL_STRING/static/images/template/b1a1cdb9885212911b4770cf7617ed58.png\",\"title\":\"花样水果\",\"link\":{}},{\"url\":\"HESHOP_URL_STRING/static/images/template/e99ecea8bcd7f87a5cf4da9c71cb28b8.png\",\"title\":\"放心菜场\",\"link\":{}},{\"url\":\"HESHOP_URL_STRING/static/images/template/8ca7f193ed5262eaec1ef67da80bfce6.png\",\"title\":\"冷冻食品\",\"link\":{}},{\"url\":\"HESHOP_URL_STRING/static/images/template/860df41ae69078bec226bbaddaed77a2.png\",\"title\":\"生猛海鲜\",\"link\":{}},{\"url\":\"HESHOP_URL_STRING/static/images/template/f5c45dc195610f6afc0b796485fa7a8a.png\",\"title\":\"酒水饮品\",\"link\":{}},{\"url\":\"HESHOP_URL_STRING/static/images/template/67a9b53e07a18abd88f58a0972b00460.png\",\"title\":\"日用百货\",\"link\":{}},{\"url\":\"HESHOP_URL_STRING/static/images/template/baa82b6f3e689be5ffd6ab0a5902b938.png\",\"title\":\"米面粮油\",\"link\":{}}]},\"facade\":{\"nav_style\":2,\"nav_line\":2,\"nav_line_mun\":4,\"active_color\":\"#f5212d\",\"indicator_style\":2,\"background_color\":\"#FFFFFF\",\"text_color\":\"#333333\"}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/99cbca0d03ca1b90685b58b0df1263e3.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":5,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/645ff87bcd3fe435df02b356235f0cbd.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/bbe24bdad11ed867a7e5692c67a95152.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/baff33fdbfba4de74390e891b90d0a6d.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/29401fbb34cc8b658eab4837254f675e.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":6,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/df86e435c525b00c02a48f3f89f58c8b.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/4835f2b980852f82966b998d6e6a6f94.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/489a769cfe477edd56b77dabab1af592.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/f97cc0294e60cb985225f6a15859adbe.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"title\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/title-icon.png\",\"title\":\"标题栏\",\"content\":{\"style\":3,\"title\":\"源头鲜货\",\"subtitle\":\"你pick哪一个\",\"is_more\":true,\"moretitle\":\"查看更多\",\"morelink\":{}},\"facade\":{\"title_color\":\"#333333\",\"title_font_size\":14,\"subtitle_color\":\"#999999\",\"subtitle_font_size\":12,\"more_color\":\"#999999\",\"margin\":20}},{\"name\":\"goods\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/goods-icon.png\",\"title\":\"商品\",\"content\":{\"type\":1,\"goods\":[],\"group\":{\"id\":0,\"name\":\"\",\"limit\":20},\"is_title\":true,\"is_price\":true,\"is_button\":true},\"facade\":{\"list_style\":3,\"card_style\":3,\"chamfer_style\":1,\"margin\":20,\"padding\":20}},{\"name\":\"wechat\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/wechat-icon.png\",\"title\":\"微信公众号\",\"content\":{},\"facade\":{\"line_style\":\"\",\"line_color\":\"\",\"high_style\":20}}]','#F7F7F7', 1616048277, 1616048277, NULL, 0, '浙江禾成云计算有限公司');
INSERT INTO `heshop_initialize_prefix_fitment_template` VALUES (5, '零食', 'https://qmxq.oss-cn-hangzhou.aliyuncs.com/template/2.jpg', '[{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/862074066fc38534afca1542f66d312d.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"blank\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/blank-icon.png\",\"title\":\"辅助空白\",\"content\":{},\"facade\":{\"height\":20,\"background_color\":\"#FFFFFF\"}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":3,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/9e6f10816ff3e1c6930130fd4fad1157.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/564da6977f9b0151afdafee3e3df88b8.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/8253ef28c0c22ef8f2f8ea66c21e11f9.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":2,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/a335a389807bc24495080928f6c592f8.png\",\"link\":\"\"},{\"title\":\"\",\"url\":\"HESHOP_URL_STRING/static/images/template/bfeae90e1b4bc7b4aef6d66aee5142f6.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"blank\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/blank-icon.png\",\"title\":\"辅助空白\",\"content\":{},\"facade\":{\"height\":20,\"background_color\":\"#FFFFFF\"}},{\"name\":\"title\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/title-icon.png\",\"title\":\"标题栏\",\"content\":{\"style\":1,\"title\":\"今日特价\",\"subtitle\":\"子标题\",\"is_more\":true,\"moretitle\":\"查看更多\",\"morelink\":{}},\"facade\":{\"title_color\":\"#F87C1E\",\"title_font_size\":14,\"subtitle_color\":\"#999999\",\"subtitle_font_size\":12,\"more_color\":\"#999999\",\"margin\":40}},{\"name\":\"goods\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/goods-icon.png\",\"title\":\"商品\",\"content\":{\"type\":1,\"goods\":[],\"group\":{\"id\":0,\"name\":\"\",\"limit\":20},\"is_title\":true,\"is_price\":true,\"is_button\":true},\"facade\":{\"list_style\":4,\"card_style\":2,\"chamfer_style\":1,\"margin\":20,\"padding\":20}},{\"name\":\"rubik\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/rubik-icon.png\",\"title\":\"图片魔方\",\"content\":{\"style\":1,\"density\":4,\"data\":[{\"url\":\"HESHOP_URL_STRING/static/images/template/fa47b7fd449e4a322df801418da85a1c.png\",\"link\":\"\"}]},\"facade\":{}},{\"name\":\"tabs\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/tabs-icon.png\",\"title\":\"选项卡\",\"content\":{\"data\":[{\"title\":\"膨化食品\",\"type\":1,\"goods\":[],\"group\":{}},{\"title\":\"气泡果酒\",\"type\":1,\"goods\":[],\"group\":{}},{\"title\":\"水果罐头\",\"type\":1,\"goods\":[],\"group\":{}},{\"title\":\"水果干\",\"type\":1,\"goods\":[],\"group\":{}},{\"title\":\"巧克力\",\"type\":1,\"goods\":[],\"group\":{}}],\"is_title\":true,\"is_price\":true,\"is_button\":true},\"facade\":{\"list_style\":2,\"card_style\":2,\"chamfer_style\":0,\"margin\":16,\"padding\":20}},{\"name\":\"wechat\",\"icon\":\"http://qmxq.oss-cn-hangzhou.aliyuncs.com/pageicon/wechat-icon.png\",\"title\":\"微信公众号\",\"content\":{},\"facade\":{\"line_style\":\"\",\"line_color\":\"\",\"high_style\":20}}]','#F7F7F7', 1616048277, 1616048277, NULL, 0, '浙江禾成云计算有限公司');


CREATE TABLE `heshop_initialize_prefix_gallery`  (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分组标题',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1图片 2视频',
  `group_id` bigint(10) NOT NULL DEFAULT 1 COMMENT '所属分组',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '素材地址',
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '缩略图',
  `sort` smallint(3) NOT NULL DEFAULT 1 COMMENT '排序',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `size` int(10) NOT NULL DEFAULT 0 COMMENT '素材大小',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `分组id`(`group_id`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_gallery_group`  (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分组名称',
  `parent_id` bigint(10) NOT NULL DEFAULT 0 COMMENT '父级id',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '分组路径',
  `sort` smallint(4) NOT NULL DEFAULT 1 COMMENT '排序',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1图片 2视频',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `父级id`(`parent_id`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of he_gallery_group
-- ----------------------------
INSERT INTO `heshop_initialize_prefix_gallery_group` VALUES (1, '未分组', 0, '0', 9999, 1, 1, '98c08c25f8136d590c', 1602813712, 1602815710, NULL, 0, 1);

CREATE TABLE `heshop_initialize_prefix_goods`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品标题',
  `price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品价格',
  `line_price` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '商品划线价',
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品分类列表',
  `status` tinyint(3) NOT NULL DEFAULT 1 COMMENT '商品状态： 0全部完成  1第一步完成  2第二步完成  3第三部完成',
  `param_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '规格类型：1单规格 2 多规格',
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '单位',
  `slideshow` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '轮播图',
  `is_video` tinyint(1) NOT NULL DEFAULT 0 COMMENT '视频开关： 0 关闭 1 启用',
  `video` varchar(8192) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '视频地址',
  `video_cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '视频封面',
  `is_real` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否实物： 0 虚拟 1 实物',
  `is_sale` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否上架：0 下架 1 上架',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '标签',
  `stocks` int(10) NOT NULL DEFAULT 0 COMMENT '库存数量',
  `reduce_stocks` tinyint(1) NOT NULL DEFAULT 2 COMMENT '减库方式：1 付款减库存 2 拍下减库存',
  `ft_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '运费设置  1统一价格 2使用模板',
  `ft_price` decimal(10, 2) NULL DEFAULT NULL COMMENT '统一运费',
  `ft_id` bigint(10) NULL DEFAULT NULL COMMENT '运费模板ID',
  `pfr_id` bigint(10) NULL DEFAULT NULL COMMENT '包邮规则ID',
  `limit_buy_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '限购状态 0不限制 1限制',
  `limit_buy_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '限购周期 day天 week周  month月  all永久',
  `limit_buy_value` smallint(5) NULL DEFAULT NULL COMMENT '限购数量',
  `min_number` smallint(3) NOT NULL DEFAULT 1 COMMENT '起购数量',
  `sort` smallint(3) NOT NULL DEFAULT 1 COMMENT '排序',
  `services` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '产品服务列表',
  `visits` int(10) NOT NULL DEFAULT 0 COMMENT '访问量',
  `virtual_sales` int(10) NOT NULL DEFAULT 0 COMMENT '虚拟销售量',
  `sales` int(10) NOT NULL DEFAULT 0 COMMENT '销售量',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_recycle` tinyint(1) NOT NULL DEFAULT 0 COMMENT '回收站',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `sales_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '销售额',
  `is_promoter` tinyint(1) NULL DEFAULT 0 COMMENT '参与分销  0不参与  1参与',
  `max_price` decimal(10, 2) NULL DEFAULT NULL COMMENT '最高价',
  `max_profits` decimal(10, 2) NULL DEFAULT NULL COMMENT '最高利润',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE,
  INDEX `货运模板id`(`ft_id`) USING BTREE,
  INDEX `包邮规则id`(`pfr_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_goods_body`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `goods_id` bigint(20) NOT NULL COMMENT '商品id',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商品详情',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商品id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_goods_coupon`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` bigint(20) NOT NULL COMMENT '商品ID',
  `coupon_id` bigint(20) NOT NULL COMMENT '发放优惠券ID',
  `number` int(10) NOT NULL COMMENT '发放优惠券数量',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商品id`(`goods_id`) USING BTREE,
  INDEX `优惠券id`(`coupon_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_goods_data`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` bigint(20) NOT NULL DEFAULT 100 COMMENT '商品ID',
  `param_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '规格参数',
  `price` decimal(10, 2) NOT NULL COMMENT '价格',
  `stocks` int(10) NOT NULL DEFAULT 0 COMMENT '库存',
  `cost_price` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '成本价',
  `weight` decimal(6, 2) NULL DEFAULT 0.00 COMMENT '重量',
  `goods_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商品编号',
  `is_deleted` tinyint(1) NULL DEFAULT NULL,
  `created_time` bigint(10) NULL DEFAULT NULL,
  `updated_time` bigint(10) NULL DEFAULT NULL,
  `deleted_time` bigint(10) NULL DEFAULT NULL,
  `task_stock` int(10) NOT NULL DEFAULT 0 COMMENT '兑换库存',
  `task_number` bigint(10) NOT NULL DEFAULT 1 COMMENT '兑换积分',
  `task_price` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '兑换价格',
  `task_limit` bigint(5) NULL DEFAULT NULL COMMENT '兑换限制',
  `task_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否上架：0 下架 1 上架',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商品id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_goods_export`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `conditions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '导出条件json',
  `goods_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '数据json',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_goods_group`  (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分组名称',
  `parent_id` bigint(10) NOT NULL DEFAULT 0 COMMENT '父级ID',
  `goods_show` tinyint(2) NOT NULL COMMENT '分组下商品展示形式',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分组图标',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分组广告图',
  `path` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '分组路径',
  `sort` smallint(3) NOT NULL DEFAULT 1 COMMENT '排序',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `type` tinyint(1) NULL DEFAULT 1 COMMENT '1一层  2二层  3三层',
  `is_show` tinyint(1) NULL DEFAULT 1 COMMENT '是否显示 0不显示  1显示',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `父级id`(`parent_id`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_goods_param`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `goods_id` bigint(20) NOT NULL COMMENT '商品id',
  `param_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '规格信息',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商品id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_goods_service`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '服务名称',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '服务说明',
  `sort` smallint(3) NOT NULL DEFAULT 1 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0未启用  1启用',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_logistics_freight_template`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '运费模板名称',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '计费方式 1按件数 2按重量',
  `freight_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '运费规则json',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认 1默认 0非默认',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_logistics_package_free`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '包邮规则名',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '包邮类型 1订单满额包邮 2订单满件包邮 3商品满额包邮 4商品满件包邮',
  `free_area` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '包邮区域json',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认 1默认 0非默认',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_order`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单编号',
  `UID` bigint(20) NOT NULL COMMENT '买家ID',
  `total_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '总计价格',
  `pay_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实付金额',
  `goods_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品金额',
  `goods_reduced` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品减少金额',
  `freight_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '运费金额',
  `freight_reduced` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '运费减少金额',
  `coupon_reduced` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '优惠券优惠金额',
  `status` smallint(3) NOT NULL DEFAULT 100 COMMENT '100待付款  101用户取消 102超时取消 103商户取消  201已付款(待发货)  202已发货(待收货)  203已收货 204已完成',
  `cancel_time` bigint(10) NULL DEFAULT 0 COMMENT '关闭时间',
  `send_time` bigint(10) NULL DEFAULT NULL COMMENT '发货时间',
  `received_time` bigint(10) NULL DEFAULT NULL COMMENT '收货时间',
  `finish_time` bigint(10) NULL DEFAULT NULL COMMENT '结束时间',
  `after_sales` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0正常  1售后中',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '来源',
  `pay_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '支付交易号',
  `pay_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'wechart微信  alipay支付宝',
  `pay_time` bigint(10) NULL DEFAULT NULL COMMENT '支付时间',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '商家备注',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `is_evaluate` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未评价 1已评价',
  `evaluate_time` bigint(10) NULL DEFAULT NULL COMMENT '评价时间',
  `is_recycle` tinyint(1) NULL DEFAULT 0 COMMENT '是否在回收站',
  `score_amount` bigint(10) NOT NULL DEFAULT 0 COMMENT '积分支付',
  `total_score` bigint(10) NOT NULL DEFAULT 0 COMMENT '积分统计',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '订单类型 base 基础订单 task 任务订单',
  `is_promoter` tinyint(1) NULL DEFAULT 0 COMMENT '是否是分销订单 0普通订单  1自购优惠 2自购返佣 3普通分销',
  `promoter_reduced` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '分销自购优惠',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `订单编号`(`order_sn`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_order_after`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单编号',
  `after_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '售后编号',
  `order_goods_id` bigint(20) NOT NULL COMMENT '订单商品id',
  `UID` bigint(20) NOT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0退款 1退货退款 2换货',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '来源',
  `return_number` int(10) NOT NULL DEFAULT 1 COMMENT '退货数量',
  `return_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '退款金额',
  `return_freight` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '退款运费',
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '说明图片',
  `status` smallint(3) NOT NULL DEFAULT 100 COMMENT '100待审核 101首次拒绝 102再次提交待审核  111审核通过待退款 121审核通过待买家发货 122买家发货待商家收货退款 131审核通过待买家发货 132买家发货待商家收货  133商家换货(买家待收)  200售后已完成  201两次拒绝之后完成',
  `return_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '退货地址',
  `user_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户备注',
  `user_freight_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '用户物流信息JSON',
  `merchant_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商家备注',
  `merchant_freight_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商家物流信息JSON',
  `merchant_id` bigint(10) NOT NULL COMMENT '商家ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `return_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '退货原因',
  `process` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '售后流程',
  `refuse_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '拒绝原因',
  `actual_refund` decimal(10, 2) NULL DEFAULT NULL COMMENT '实际退款',
  `audit_time` bigint(10) NULL DEFAULT NULL COMMENT '审核时间',
  `return_time` bigint(10) NULL DEFAULT NULL COMMENT '退款时间',
  `exchange_time` bigint(10) NULL DEFAULT NULL COMMENT '换货时间',
  `refuse_time` bigint(10) NULL DEFAULT NULL COMMENT '拒绝时间',
  `finish_time` bigint(10) NULL DEFAULT NULL COMMENT '完成时间',
  `salesexchange_time` bigint(10) NULL DEFAULT NULL COMMENT '退货时间',
  `return_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '退款单号',
  `second_refuse_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '第二次拒绝原因',
  `return_score` bigint(20) NOT NULL DEFAULT 0 COMMENT '退款积分',
  `actual_score` bigint(20) NULL DEFAULT NULL COMMENT '实际退还积分',
  `order_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'base' COMMENT '订单类型',
  `return_score_type` tinyint(1) NULL DEFAULT 0 COMMENT '0不退积分 1退积分',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `订单编号`(`order_sn`) USING BTREE,
  INDEX `售后编号`(`after_sn`) USING BTREE,
  INDEX `订单商品id`(`order_goods_id`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_order_after_export`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `conditions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '导出条件json',
  `parameter` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '参数json',
  `order_after_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '数据json',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_order_batch_handle`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `handle_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '参数json',
  `order_number` smallint(4) NOT NULL COMMENT '发货订单数',
  `success_number` smallint(4) NOT NULL COMMENT '成功发货数',
  `error_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '失败数据json',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `APPID`(`AppID`) USING BTREE,
  INDEX `商户ID`(`merchant_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_order_buyer`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '订单编号',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户备注',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '收件人',
  `mobile` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '联系电话',
  `province` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '省',
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '市',
  `district` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '区县',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '详细地址',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `订单编号`(`order_sn`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_order_evaluate`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `goods_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品名称',
  `goods_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品图片',
  `goods_id` bigint(20) NOT NULL COMMENT '商品ID',
  `goods_param_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商品规格键',
  `goods_param` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商品规格',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0隐藏  1普通  2置顶',
  `star` tinyint(1) NOT NULL COMMENT '星级',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '评论内容',
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '评论图片',
  `reply` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '商家回复',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  `show_goods_param` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商品规格键',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `订单编号`(`order_sn`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE,
  INDEX `商品id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


CREATE TABLE `heshop_initialize_prefix_order_export`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `conditions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '导出条件json',
  `parameter` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '参数json',
  `order_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '数据json',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_order_freight`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单编号',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1自己联系物流  2无需物流',
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '物流公司代号',
  `logistics_company` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '物流公司',
  `freight_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '快递单号',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `preview_image`  varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '电子面单预览图',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `订单编号`(`order_sn`) USING BTREE,
  INDEX `物流编号`(`freight_sn`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_order_freight_goods`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `freight_id` bigint(20) NOT NULL COMMENT '物流包裹ID',
  `order_goods_id` bigint(20) NOT NULL COMMENT '订单商品ID',
  `bag_goods_number` int(10) NOT NULL COMMENT '订单商品数量',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `包裹id`(`freight_id`) USING BTREE,
  INDEX `订单商品id`(`order_goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_order_goods`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '订单编号',
  `goods_id` bigint(20) NOT NULL COMMENT '商品id',
  `goods_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '商品货号',
  `goods_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `goods_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品封面',
  `goods_param_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品规格组',
  `goods_param` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品规格',
  `goods_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品价格',
  `goods_cost_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品成本价',
  `goods_weight` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '商品重量',
  `goods_number` int(10) NOT NULL DEFAULT 1 COMMENT '商品数量',
  `total_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '总计金额',
  `pay_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实付金额',
  `after_sales` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0正常 1售后',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `is_evaluate` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未评价 1已评价',
  `show_goods_param` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商品规格键',
  `coupon_reduced` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '优惠券优惠金额',
  `goods_score` bigint(10) NOT NULL DEFAULT 0 COMMENT '商品积分',
  `score_amount` bigint(10) NULL DEFAULT 0 COMMENT '总计积分',
  `promoter_reduced` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '分销自购优惠',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `订单编号`(`order_sn`) USING BTREE,
  INDEX `商品id`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_order_pay`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pay_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '支付单号',
  `order_list` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '支付的所有订单JSON',
  `total_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '支付金额',
  `pay_type` tinyint(1) NULL DEFAULT NULL COMMENT '1微信  2支付宝',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未支付  1已支付',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_promoter`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `level` tinyint(2) NULL DEFAULT 1 COMMENT '当前等级',
  `start_level` tinyint(2) NULL DEFAULT 1 COMMENT '起步等级',
  `commission` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '佣金',
  `commission_amount` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '总计佣金',
  `status` tinyint(2) NULL DEFAULT 0 COMMENT '-2清退后接到招募令 -1接到招募令 0普通用户 1申请待审核 2审核通过 3已拒绝 4已清退',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '拒绝原因',
  `transfer_id` bigint(20) NULL DEFAULT NULL COMMENT '移交用户ID',
  `repel_time` bigint(10) NULL DEFAULT NULL COMMENT '清退时间',
  `invite_id` bigint(10) NOT NULL DEFAULT 0 COMMENT '邀请方ID',
  `apply_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '申请内容json',
  `apply_time` bigint(10) NULL DEFAULT NULL COMMENT '申请时间',
  `join_time` bigint(10) NULL DEFAULT NULL COMMENT '加入时间',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  `invite_number` int(11) NULL DEFAULT 0 COMMENT '邀请数量',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `用户ID`(`UID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_promoter_commission`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `beneficiary` bigint(20) NOT NULL COMMENT '受益人ID',
  `order_goods_id` bigint(20) NOT NULL COMMENT '分销订单ID',
  `commission` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '佣金',
  `level` tinyint(1) NULL DEFAULT 1 COMMENT '佣金等级',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  `sales_amount` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '销售金额',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `受益人ID`(`beneficiary`) USING BTREE,
  INDEX `分销订单ID`(`order_goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_promoter_goods`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` bigint(20) NOT NULL COMMENT '商品ID',
  `sales` int(10) NULL DEFAULT 0 COMMENT '销量',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商品ID`(`goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_promoter_level`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `level` tinyint(1) NOT NULL COMMENT '等级权重',
  `name` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '等级名称',
  `first` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '一级佣金',
  `second` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '二级佣金',
  `third` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '三级佣金',
  `is_auto` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0不允许 1允许',
  `update_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1任意条件 2全部条件',
  `condition` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '条件',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

INSERT INTO `heshop_initialize_prefix_promoter_level` VALUES (1, 1, '默认一级', 0, 0, 0, 0, 1, '[]', '98c08c25f8136d590c', 1, 1623231954, 0, 0, 0);

CREATE TABLE `heshop_initialize_prefix_promoter_level_change_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `old_level` tinyint(2) NOT NULL COMMENT '之前等级',
  `old_level_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '之前等级',
  `new_level` tinyint(2) NOT NULL COMMENT '现在等级',
  `new_level_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '现在等级',
  `type` tinyint(1) NULL DEFAULT 1 COMMENT '1升级  2降级',
  `look_status` tinyint(1) NULL DEFAULT 0 COMMENT '查看状态',
  `push_status` tinyint(1) NULL DEFAULT 0 COMMENT '推送状态',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `用户ID`(`UID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_promoter_lose_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `parent_id` bigint(20) NOT NULL COMMENT '父级ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `type` tinyint(1) NULL DEFAULT 1 COMMENT '失去下级原因  1解除  2清退  3保护期',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `父级ID`(`parent_id`) USING BTREE,
  INDEX `用户ID`(`UID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_promoter_material`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '素材名称',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1图片 2视频',
  `content` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '素材文案',
  `pic_list` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '图片列表',
  `video_list` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '视频',
  `video_cover` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '视频封面',
  `goods_id` int(11) NOT NULL DEFAULT 0 COMMENT '关联商品id',
  `share_count` int(11) NOT NULL DEFAULT 0 COMMENT '分享次数',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_promoter_order`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `UID` bigint(20) NOT NULL COMMENT '买家ID',
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
  `order_goods_id` bigint(20) NOT NULL COMMENT '订单商品ID',
  `goods_number` int(10) NOT NULL COMMENT '总共商品数量',
  `commission_number` int(10) NOT NULL COMMENT '分佣商品数量',
  `total_amount` decimal(10, 2) NOT NULL COMMENT '分佣金额',
  `profits_amount` decimal(10, 2) NOT NULL COMMENT '分佣利润',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '-1已失效 0待结算 1已结算',
  `count_rules` tinyint(1) NULL DEFAULT 1 COMMENT '计算规则  1商品实付金额  2商品利润',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `订单商品ID`(`order_goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_promoter_zone`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否为管理员',
  `name` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '素材名称',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1图片 2视频',
  `content` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '素材文案',
  `pic_list` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '图片列表',
  `video_list` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '视频',
  `video_cover` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '视频封面',
  `link` varchar(5096) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '跳转链接',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 72 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_promoter_zone_upvote`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `zone_id` int(11) NOT NULL COMMENT '空间动态id',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 51 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_roles`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '规则名称',
  `describe` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '暂无描述' COMMENT '规则描述',
  `visits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '权限列表',
  `created_time` bigint(10) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of he_roles
-- ----------------------------
INSERT INTO `heshop_initialize_prefix_roles` VALUES (1, '超级管理员', '拥有平台最高权限', '[53,54,55,56,57,58,59,60,61,62,64,65,68,75,76,77,78,79,80,81,82,83]', 1600931133, 1603848730, NULL, 0);

CREATE TABLE `heshop_initialize_prefix_rules`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '规则名称',
  `describe` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '暂无描述' COMMENT '规则描述',
  `api` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '权限列表',
  `created_time` bigint(10) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of he_rules
-- ----------------------------
INSERT INTO `heshop_initialize_prefix_rules` VALUES (1, 'asdasd', '订单列表', '[81]', 1601286206, 1602469087, NULL, 0);

CREATE TABLE `heshop_initialize_prefix_sms_code_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` int(10) NOT NULL COMMENT '验证码',
  `mobile` bigint(11) NOT NULL COMMENT '手机',
  `type` tinyint(1) NULL DEFAULT NULL COMMENT '1手机绑定验证码',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


CREATE TABLE `heshop_initialize_prefix_statistical_goods_visit_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` bigint(50) NOT NULL COMMENT '商定编号',
  `UID` bigint(20) NULL DEFAULT NULL COMMENT '用户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商品id`(`goods_id`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


CREATE TABLE `heshop_initialize_prefix_statistical_upload_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `size` int(10) NOT NULL COMMENT '大小',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '地址',
  `UID` bigint(20) NULL DEFAULT NULL COMMENT '用户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


CREATE TABLE `heshop_initialize_prefix_statistical_visit_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `UID` bigint(20) NULL DEFAULT NULL COMMENT '用户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


CREATE TABLE `heshop_initialize_prefix_store_address`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '收件人',
  `mobile` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '联系方式',
  `province` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '省',
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '市',
  `district` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '县',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '详细地址',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0非默认  1默认',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `商户id`(`merchant_id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_store_setting`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '关键字',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of he_store_setting
-- ----------------------------
INSERT INTO `heshop_initialize_prefix_store_setting` VALUES (5, 'message_setting', '{\"order_pay\":{\"sms\":true,\"wechat_public\":true,\"wechat_subscribe\":false},\"pay_success\":{\"sms\":true,\"wechat_public\":true,\"wechat_subscribe\":false},\"order_send\":{\"sms\":true,\"wechat_public\":true,\"wechat_subscribe\":true},\"agree_after\":{\"sms\":true,\"wechat_public\":true,\"wechat_subscribe\":true},\"refuse_after\":{\"sms\":true,\"wechat_public\":true,\"wechat_subscribe\":true},\"create_after\":{\"sms\":true,\"wechat_public\":true,\"wechat_subscribe\":false},\"return_success\":{\"sms\":true,\"wechat_public\":true,\"wechat_subscribe\":false}}', 1, '98c08c25f8136d590c', 1605671754, 1609209437, NULL, 0);
INSERT INTO `heshop_initialize_prefix_store_setting` VALUES (6, 'setting_collection', '{\"basic_setting\":{\"run_status\":1,\"restore_status\":1,\"restore_time\":\"1615615440000\"},\"store_setting\":{\"name\":\"小店\",\"logo\":\"HESHOP_URL_STRING/static/images/home.png\",\"abstract\":\"123123\",\"phone\":\"13333333333\",\"address\":\"205\",\"addressList\":[\"浙江省\",\"嘉兴市\",\"南湖区\"],\"province\":\"浙江省\",\"city\":\"嘉兴市\",\"district\":\"南湖区\"},\"trade_setting\":{\"cancel_status\":1,\"cancel_time\":\"1\",\"received_time\":\"7\",\"evaluate_status\":0,\"evaluate_time\":\"30\",\"after_time\":\"10\",\"exchange_status\":1,\"pay_way\":{\"wechat\":{\"title\":\"微信\",\"value\":true},\"alipay\":{\"title\":\"支付宝\",\"value\":false}}},\"goods_setting\":{\"recommend_status\":1,\"recommend_showpage\":{\"goodsinfo\":{\"title\":\"商品详情\",\"value\":false},\"pay_success\":{\"title\":\"支付完成\",\"value\":false},\"personal_center\":{\"title\":\"个人中心\",\"value\":false},\"orderinfo\":{\"title\":\"订单详情\",\"value\":false},\"cart\":{\"title\":\"购物车\",\"value\":false}},\"evaluate_show\":2,\"order_list_roll\":0,\"sales_show\":0,\"soldout_show\":1,\"recommend_goods\":[]},\"user_setting\":{\"mobile_auth\":{\"join_shopping_cart\":{\"title\":\"加入购物车\",\"value\":true},\"paybuy\":{\"title\":\"支付购买\",\"value\":true},\"get_tickets\":{\"title\":\"获取卡券\",\"value\":false}},\"userinfo_auth\":{\"join_shopping_cart\":{\"title\":\"加入购物车\",\"value\":true},\"paybuy\":{\"title\":\"支付购买\",\"value\":true},\"get_tickets\":{\"title\":\"获取卡券\",\"value\":false}}}}', 1, '98c08c25f8136d590c', 1609224588, 1615879206, NULL, 0);
INSERT INTO `heshop_initialize_prefix_store_setting` VALUES (7, 'goods_group_setting', '{\"group_show\":1}', 1, '98c08c25f8136d590c', 1609825806, 1610185214, NULL, 0);


CREATE TABLE `heshop_initialize_prefix_user`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `mobile` bigint(11) NULL DEFAULT NULL COMMENT '手机号',
  `realname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户姓名',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '头像',
  `gender` tinyint(1) NOT NULL DEFAULT 0 COMMENT '性别 0未知 1男 2女',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '来源',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '帐号状态 0正常  1禁用',
  `parent_id` bigint(20) NULL DEFAULT 0 COMMENT '父级ID',
  `bind_time` bigint(10) NULL DEFAULT NULL COMMENT '绑定时间',
  `AppID` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `created_time` bigint(10) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除事件',
  `birthday` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '生日',
  `area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '地区',
  `wechat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '微信号',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `应用id`(`AppID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_user_address`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '收件人',
  `mobile` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '联系方式',
  `province` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '省',
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '市',
  `district` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '县',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '详细地址',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0非默认  1默认',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_user_coupon`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `coupon_id` bigint(20) NOT NULL COMMENT '优惠券ID',
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '订单号',
  `origin` tinyint(1) NOT NULL COMMENT '来源  1:自己领取 2:商家发放 3:下单赠送',
  `use_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '已使用的优惠券数据',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_recycle` tinyint(1) NULL DEFAULT 0 COMMENT '是否在回收站',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `goods_id` bigint(20) NULL DEFAULT NULL COMMENT '订单商品id,用于退款后失效',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态  0未使用  1已使用 2已失效',
  `begin_time` bigint(10) NULL DEFAULT NULL COMMENT '有效期开始时间',
  `end_time` bigint(10) NULL DEFAULT NULL COMMENT '有效期结束时间',
  `origin_order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '来源订单号',
  `use_time` bigint(10) NULL DEFAULT NULL COMMENT '使用时间',
  `is_remind` tinyint(1) NOT NULL DEFAULT 0 COMMENT '到期提醒 0否 1是',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `优惠券ID`(`coupon_id`) USING BTREE,
  INDEX `优惠券来源`(`goods_id`, `origin_order_sn`) USING BTREE,
  INDEX `应用ID`(`AppID`) USING BTREE,
  INDEX `用户ID`(`UID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_user_export`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `conditions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '导出条件json',
  `user_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '数据json',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE `heshop_initialize_prefix_user_label`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标签名称',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '标签类型 1手动 2自动',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '启用状态 0不启用  1启用',
  `conditions_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '达标条件  1满足所有  2任意一个',
  `conditions_setting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '条件设置',
  `filter_user` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '过滤的用户',
  `merchant_id` bigint(10) NOT NULL COMMENT '店铺ID',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `created_time` bigint(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` bigint(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `users_number` int(10) NULL DEFAULT 0 COMMENT '拥有用户数量',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_user_label_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `label_id` bigint(10) NOT NULL COMMENT '标签ID',
  `created_time` bigint(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` bigint(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `用户ID`(`UID`) USING BTREE,
  INDEX `标签ID`(`label_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_user_oauth`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自动编号',
  `UID` int(11) NULL DEFAULT NULL COMMENT '用户ID',
  `oauthID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '第三方ID',
  `unionID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '唯一标识',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '应用识别码',
  `format` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '格式数据',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `created_time` bigint(10) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除事件',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;


CREATE TABLE `heshop_initialize_prefix_user_statistical`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `buy_number` int(6) NOT NULL DEFAULT 0 COMMENT '总购买次数',
  `buy_amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '消费金额',
  `last_buy_time` bigint(10) NULL DEFAULT NULL COMMENT '上次购买时间',
  `last_visit_time` bigint(10) NULL DEFAULT NULL COMMENT '上次访问时间',
  `created_time` bigint(10) NOT NULL COMMENT '创建时间',
  `updated_time` bigint(10) NULL DEFAULT NULL COMMENT '修改时间',
  `deleted_time` bigint(10) NULL DEFAULT NULL COMMENT '删除时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `用户id`(`UID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE `heshop_initialize_prefix_collect_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '采集类型 1阿里巴巴、2淘宝、3京东、4拼多多、5天猫',
  `link` varchar(2048) COLLATE utf8mb4_general_ci NOT NULL COMMENT '采集链接',
  `json` longtext COLLATE utf8mb4_general_ci NOT NULL COMMENT '数据json',
  `goods_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态 1成功 0失败',
  `AppID` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `heshop_initialize_prefix_goods_param_template` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `param_name` varchar(256) COLLATE utf8mb4_general_ci NOT NULL COMMENT '规格名',
  `param_data` text COLLATE utf8mb4_general_ci COMMENT '规格值',
  `AppID` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `heshop_initialize_prefix_live_goods` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '商品名称',
  `cover` varchar(4096) NOT NULL DEFAULT '' COMMENT '商品封面',
  `price_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '价格类型',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `price2` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格2',
  `link` varchar(256) NOT NULL DEFAULT '' COMMENT '小程序路径',
  `audit_id` varchar(255) NOT NULL DEFAULT '' COMMENT '审核单号',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `AppID` varchar(50) NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL DEFAULT '1' COMMENT '商户ID',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  `gid` int(11) NOT NULL DEFAULT '0' COMMENT 'leadshop商品id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `heshop_initialize_prefix_live_room` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `room_id` bigint(11) NOT NULL COMMENT '商品名称',
  `anchor_wechat` varchar(128) NOT NULL COMMENT '主播微信号',
  `sub_wechat` varchar(128) NOT NULL DEFAULT '' COMMENT '副手微信号',
  `AppID` varchar(50) NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL DEFAULT '1' COMMENT '商户ID',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `heshop_initialize_prefix_task` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) DEFAULT NULL COMMENT '任务名称',
  `keyword` varchar(255) DEFAULT NULL COMMENT '任务标识符',
  `formula` varchar(255) DEFAULT NULL COMMENT '计算公式',
  `icon` varchar(255) DEFAULT NULL COMMENT '任务图标',
  `type` varchar(3) DEFAULT 'add' COMMENT '任务类型',
  `total` varchar(10) DEFAULT '1' COMMENT '累计次数',
  `acquire` bigint(10) DEFAULT '1' COMMENT '获取积分',
  `maximum` bigint(10) DEFAULT '1' COMMENT '最大值',
  `remark` varchar(255) DEFAULT NULL COMMENT '积分说明',
  `url` varchar(255) DEFAULT NULL COMMENT '跳转链接',
  `status` tinyint(1) DEFAULT '0' COMMENT '任务状态 0关闭 1开启',
  `extend` text COMMENT '扩展配置',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  `prompt` varchar(255) DEFAULT NULL COMMENT '积分提示说明',
  `extra` varchar(255) DEFAULT NULL COMMENT '第三个说明',
  `page_tips` varchar(255) DEFAULT NULL COMMENT '微页面说明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;


BEGIN;
INSERT INTO `heshop_initialize_prefix_task` VALUES (1, '购买商品', 'goods', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_goods_icon.png', '1', '0.02', 10, NULL, '消费%s元，获得%s积分', '/pages/goods/list', 1, '[]', 0, 1624552148, 1626170393, 0, '每日最多%s次', NULL, '消费%s元，获得%s积分');
INSERT INTO `heshop_initialize_prefix_task` VALUES (2, '完成下单', 'order', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_order_icon.png', '1', '2', 77, NULL, '每下%s笔订单，获得%s积分', '/pages/goods/list', 1, '[]', 0, 1624552148, 1626170393, 0, '每日最多%s次', '再购买%s单，即可获得积分', '每下%5笔订单，获得%s积分');
INSERT INTO `heshop_initialize_prefix_task` VALUES (3, '每日签到', 'signin', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_signin_icon.png', '1', '1', 1000, 10, '每日签到，获得%s积分', '/plugins/task/index', 1, '[]', 0, 1624552148, 1626170393, 0, NULL, NULL, '每日签到，获得%s积分');
INSERT INTO `heshop_initialize_prefix_task` VALUES (4, '连续签到', 'sustain', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_signin_icon.png', '1', '2', 20, 100, '连续签到%s天，获得额外%s积分', '/plugins/task/index', 1, '[]', 0, 1624552148, 1626170393, 0, NULL, NULL, NULL);
INSERT INTO `heshop_initialize_prefix_task` VALUES (5, '分享转发', 'share', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_share_icon.png', '1', '2', 6, 6, '每日分享转发%s次及以上，获得%s积分', '/pages/index/index', 1, '[]', 0, 1624552148, 1626170393, 0, '再分享转发%s次，即可获得积分', NULL, '每日转发%s次，获得%s积分');
INSERT INTO `heshop_initialize_prefix_task` VALUES (6, '浏览商品', 'browse', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_browse_icon.png', '1', '2', 10, 6, '每日浏览商品%s件及以上，获得%s积分', '/pages/goods/search-list?task_browse=1', 1, '[]', 0, 1624552149, 1626170393, 0, '再浏览%s件商品，即可获得积分', NULL, NULL);
INSERT INTO `heshop_initialize_prefix_task` VALUES (7, '邀请好友', 'invite', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_invite_icon.png', '1', '3', 6, 19, '每邀请好友%s人，获得%s积分', '/pages/index/index', 1, '[]', 0, 1624552149, 1626170393, 0, '再邀请%s人，即可获得积分', NULL, NULL);
INSERT INTO `heshop_initialize_prefix_task` VALUES (8, '完善信息', 'perfect', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_perfect_icon.png', '2', '1', 123, 20, '完善个人信息，获得%s积分', '/plugins/task/userinfo', 1, '[\"realname\",\"avatar\",\"gender\",\"wechat\"]', 0, 1624552149, 1626170393, 0, NULL, NULL, NULL);
INSERT INTO `heshop_initialize_prefix_task` VALUES (9, '绑定手机号', 'binding', NULL, 'http://qmxq.oss-cn-hangzhou.aliyuncs.com/task/icon/task_binding_icon.png', '2', '1', 11, 20, '绑定手机号，获得%s积分', '/pages/user/index', 1, '[\"phone\"]', 0, 1624552149, 1626170393, 0, NULL, NULL, NULL);
COMMIT;

CREATE TABLE `heshop_initialize_prefix_task_goods` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` bigint(20) NOT NULL COMMENT '商品ID',
  `task_stock` bigint(10) NOT NULL COMMENT '兑换库存',
  `task_number` bigint(10) NOT NULL COMMENT '兑换积分',
  `task_price` decimal(10,2) DEFAULT NULL COMMENT '兑换价格',
  `task_limit` bigint(5) DEFAULT NULL COMMENT '兑换限制',
  `goods_is_sale` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否上架：0 下架 1 上架',
  `is_recycle` tinyint(1) DEFAULT '0' COMMENT '是否在回收站',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  `task_status` tinyint(1) DEFAULT '0' COMMENT '兑换状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `heshop_initialize_prefix_task_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `task_id` bigint(20) NOT NULL COMMENT '任务ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `start_time` bigint(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '任务装填： 0 未完成 1 已完成',
  `number` bigint(10) NOT NULL DEFAULT '1' COMMENT '积分分值',
  `extend` text COMMENT '扩展信息处理',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `heshop_initialize_prefix_task_score` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `task_id` bigint(20) NOT NULL COMMENT '任务ID',
  `order_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_sn` varchar(50) DEFAULT NULL COMMENT '订单号',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `start_time` bigint(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '任务装填： 0 未完成 1 已完成',
  `number` bigint(10) NOT NULL DEFAULT '1' COMMENT '积分分值',
  `remark` varchar(255) DEFAULT NULL COMMENT '收支说明',
  `identifier` varchar(30) DEFAULT NULL COMMENT '标识符',
  `type` varchar(3) NOT NULL DEFAULT 'add' COMMENT '收支类型：add 增加 del 减少',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `heshop_initialize_prefix_task_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `UID` bigint(20) NOT NULL COMMENT '用户ID',
  `number` bigint(10) DEFAULT '0' COMMENT '积分值',
  `total` bigint(10) DEFAULT '0' COMMENT '积分累计',
  `consume` bigint(10) DEFAULT '0' COMMENT '已消费积分',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `heshop_initialize_prefix_waybill` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(10) NOT NULL COMMENT '物流公司编号',
  `name` varchar(256) NOT NULL COMMENT '名称',
  `mobile` varchar(32) NOT NULL COMMENT '联系方式',
  `province` varchar(50) NOT NULL COMMENT '省',
  `city` varchar(50) NOT NULL COMMENT '市',
  `district` varchar(50) NOT NULL COMMENT '区县',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  `AppID` varchar(50) NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `updated_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `deleted_time` int(10) DEFAULT '0' COMMENT '删除时间',
  `is_deleted` tinyint(100) DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `heshop_initialize_prefix_finance`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `UID` bigint(20) NULL DEFAULT NULL COMMENT '用户ID',
  `order_sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '提现订单号',
  `price` decimal(10, 2) NOT NULL COMMENT '提现金额',
  `service_charge` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '提现手续费（%）',
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '提现方式 wechatDib: \'自动到账微信零钱\', wechat: \'提现到微信\', alipay: \'提现到支付宝\',bankCard: \'提现到银行卡\'',
  `extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '额外信息',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '提现状态 0--申请 1--同意 2--已打款 3--驳回',
  `remark` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '真实姓名',
  `model` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '提现来源(promoter)',
  `transfer_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0.待转账 | 1.已转账  | 2.拒绝转账',
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机',
  `AppID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用ID',
  `merchant_id` bigint(10) NOT NULL COMMENT '商户ID',
  `created_time` int(10) NULL DEFAULT 0 COMMENT '创建时间',
  `updated_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  `deleted_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  `is_deleted` tinyint(100) NULL DEFAULT 0 COMMENT '是否删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;