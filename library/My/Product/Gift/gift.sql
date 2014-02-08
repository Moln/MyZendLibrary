CREATE TABLE `game_xlw_gift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdkey` char(14) NOT NULL COMMENT '礼品卡',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '卡状态',
  `account` varchar(32) DEFAULT NULL COMMENT '用户账号',
  `info` varchar(250) DEFAULT NULL COMMENT '消息',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cdkey` (`cdkey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;