SET NAMES utf8mb4;
SET
    FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tp_admin_user
-- ----------------------------
DROP TABLE IF EXISTS `tp_admin_user`;
CREATE TABLE `tp_admin_user`
(
    `id`              int(10) unsigned               NOT NULL AUTO_INCREMENT,
    `username`        varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户名',
    `password`        varchar(96) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '密码',
    `email`           varchar(64) CHARACTER SET utf8          DEFAULT '' COMMENT '邮箱地址',
    `mobile`          varchar(12) CHARACTER SET utf8          DEFAULT '' COMMENT '手机号码',
    `role`            int(10) unsigned               NOT NULL DEFAULT '0' COMMENT '角色ID',
    `create_time`     bigint(20) unsigned            NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time`     bigint(20) unsigned            NOT NULL DEFAULT '0' COMMENT '更新时间',
    `last_login_time` bigint(20) unsigned                     DEFAULT '0' COMMENT '最后一次登录时间',
    `last_login_ip`   varchar(20)                             DEFAULT '0' COMMENT '登录ip',
    `status`          tinyint(4)                     NOT NULL DEFAULT '1' COMMENT '状态：0禁用，1启用',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8mb4 COMMENT ='用户表';

-- ----------------------------
-- Records of tp_admin_user
-- ----------------------------
BEGIN;
INSERT INTO `tp_admin_user` (`id`, `username`, `password`, `email`, `mobile`, `role`, `create_time`, `update_time`,
                             `last_login_time`, `last_login_ip`, `status`)
VALUES (1, 'admin', '48ecc23b8fb9d668a63b23e05dfb7daba28ee231f7072d047c7227bfb2add049', '12123', '', 1, 1476065410,
        1659714164, 1659714164, '::1', 1);
COMMIT;

SET
    FOREIGN_KEY_CHECKS = 1;
