-- MySQL dump 10.13  Distrib 8.0.24, for Linux (x86_64)
--
-- Host: localhost    Database: imgurl
-- ------------------------------------------------------
-- Server version	8.0.24

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_user`
--

DROP TABLE IF EXISTS `admin_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_user` (
  `aid` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(25) NOT NULL COMMENT '名称',
  `account` varchar(50) DEFAULT NULL COMMENT '账号',
  `mobile` varchar(25) DEFAULT NULL COMMENT '手机号码',
  `sex` enum('1','2') DEFAULT '1' COMMENT '1=男，2=女',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `lasttime` datetime DEFAULT NULL COMMENT '上次登录时间',
  `role` int DEFAULT NULL,
  `status` enum('1','2') DEFAULT '1' COMMENT '1正常，2=禁用',
  `password` varchar(100) DEFAULT NULL COMMENT '密码',
  `token` varchar(100) DEFAULT NULL COMMENT '令牌',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'https://img.tshy.xyz/5210',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='管理员列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_user`
--

LOCK TABLES `admin_user` WRITE;
/*!40000 ALTER TABLE `admin_user` DISABLE KEYS */;
INSERT INTO `admin_user` VALUES (7,'admin','admin','10086','1','2021-12-01 09:12:51','2022-07-07 13:39:21',1,'1','e10adc3949ba59abbe56e057f20f883e','e901224157e448e375ad359dd3948075','https://img.tshy.xyz/5210'),(12,'test','test','12345','1','2022-06-04 19:21:35','2022-07-07 13:35:02',2,'1','e10adc3949ba59abbe56e057f20f883e','69e44a4967ae94ca00fb3c053d5e60a3','https://img.tshy.xyz/5210');
/*!40000 ALTER TABLE `admin_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_report`
--

DROP TABLE IF EXISTS `article_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_report` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int DEFAULT NULL COMMENT '举报者id',
  `wid` int NOT NULL COMMENT '文章或内容ID',
  `addtime` datetime DEFAULT NULL COMMENT '举报时间',
  `type` varchar(100) NOT NULL,
  `tips` varchar(250) DEFAULT NULL COMMENT '举报其他留言',
  `status` enum('1','2','3') DEFAULT '1' COMMENT '1=未处理2=已处理3=无效举报',
  `result` varchar(200) DEFAULT NULL COMMENT '处理结果',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='内容举报';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_report`
--

LOCK TABLES `article_report` WRITE;
/*!40000 ALTER TABLE `article_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `article_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `code`
--

DROP TABLE IF EXISTS `code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `code` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL COMMENT '邮箱',
  `code` varchar(15) NOT NULL COMMENT '验证码',
  `creattime` int NOT NULL COMMENT '创建时间',
  `status` enum('1','2') DEFAULT '1' COMMENT '是否使用:1=No,2=Used',
  UNIQUE KEY `id` (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=448 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `code`
--

LOCK TABLES `code` WRITE;
/*!40000 ALTER TABLE `code` DISABLE KEYS */;
/*!40000 ALTER TABLE `code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL COMMENT '用户ID',
  `path` varchar(255) DEFAULT NULL COMMENT '地址',
  `size` int DEFAULT '0' COMMENT '尺寸',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `type` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'jpeg' COMMENT '类型',
  `width` int DEFAULT '0',
  `height` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=440 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='上传文件记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friend_link`
--

DROP TABLE IF EXISTS `friend_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `friend_link` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `href` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '2',
  `addtime` datetime DEFAULT NULL,
  `tips` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '网站介绍',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='友情链接';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friend_link`
--

LOCK TABLES `friend_link` WRITE;
/*!40000 ALTER TABLE `friend_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `friend_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `image`
--

DROP TABLE IF EXISTS `image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `image` (
  `id` char(14) NOT NULL,
  `uid` int DEFAULT NULL COMMENT '用户id',
  `url` varchar(155) DEFAULT NULL COMMENT '原图地址',
  `size` decimal(20,2) DEFAULT NULL COMMENT '尺寸',
  `width` int DEFAULT NULL COMMENT '图片宽度',
  `height` int DEFAULT NULL COMMENT '图片高度',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `type` varchar(100) DEFAULT NULL COMMENT '类型',
  `md5` varchar(64) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL COMMENT '图片地址',
  `pron` int DEFAULT '0' COMMENT '涉黄指数',
  `sort_id` char(13) DEFAULT NULL,
  `folder` int DEFAULT NULL,
  `murl` varchar(255) DEFAULT NULL COMMENT '缩略图',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `image_sort_id_uindex` (`sort_id`),
  KEY `uid` (`uid`),
  KEY `image_md5_IDX` (`md5`) USING BTREE,
  CONSTRAINT `image_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `image`
--

LOCK TABLES `image` WRITE;
/*!40000 ALTER TABLE `image` DISABLE KEYS */;
/*!40000 ALTER TABLE `image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '站内新闻id',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `context` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文本内容',
  `aid` int NOT NULL COMMENT '发布者',
  `addtime` datetime DEFAULT NULL COMMENT '发布时间',
  `status` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '1' COMMENT '状态:1=未发布,2=已发布',
  PRIMARY KEY (`id`),
  KEY `news_title_IDX` (`title`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='站内新闻';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL COMMENT '用户UID',
  `context` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '正文',
  `annex_img` json DEFAULT NULL COMMENT '附件内容图片',
  `is_reply` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '1' COMMENT '是否回复处理 ：1=未回复，2=已回复',
  `reply_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '回复内容',
  `addtime` datetime DEFAULT NULL COMMENT '提交时间',
  `reply_time` datetime DEFAULT NULL COMMENT '回复时间',
  `link` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '联系方式',
  PRIMARY KEY (`id`),
  KEY `report_FK` (`uid`),
  CONSTRAINT `report_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='意见反馈';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report`
--

LOCK TABLES `report` WRITE;
/*!40000 ALTER TABLE `report` DISABLE KEYS */;
/*!40000 ALTER TABLE `report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字段',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '值',
  `memo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '备注',
  `tips` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '字段value说明',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='系统设置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('app_logo','https://img.tshy.xyz/i629b062aac271','logo','logo'),('app_name','创次元','网站名称','网站名称'),('bah','陇ICP备000000000号','备案号','备案号'),('cos_check','0','是否开启鉴黄','1=true 0=false'),('default_storage_size','10000000','新人默认空间大小','单位KB'),('max_upload','10000','最大上传文件大小','单位为kb'),('registerType','3','注册类型','注册类型'),('smtpServer','{\"host\":\"\",\"username\":\"\",\"password\":\"\",\"secure\":\"ssl\"}','邮件服务器配置','邮件服务器配置'),('tencent','{\"secretId\":\"\",\"secretKey\":\"\",\"Region\":\"\",\"Bucket\":\"\",\"SmsSdkAppId\":\"\",\"TemplateId\":\"\"}','腾讯云账号配置','腾讯云账号配置'),('uploads','1','是否开启上传文件','1=true 0=false'),('uploads_cos','0','是否开启cos上传','1=true 0=false');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '用户昵称',
  `mobile` varchar(25) NOT NULL COMMENT '手机号码',
  `password` varchar(50) DEFAULT NULL COMMENT '用户密码',
  `isvip` enum('0','1','2') DEFAULT '0' COMMENT '是否会员',
  `lasttime` int DEFAULT NULL COMMENT '上次登录时间',
  `createtime` int DEFAULT NULL COMMENT '注册时间',
  `status` enum('1','2','3') DEFAULT '1' COMMENT '1=正常，2=禁用，3=注销',
  `subscription` int DEFAULT '0' COMMENT '被订阅数量',
  `image_token` varchar(100) DEFAULT NULL COMMENT '图床第三方token',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `id` (`uid`),
  UNIQUE KEY `user_UN` (`mobile`),
  UNIQUE KEY `user_uniqe` (`name`),
  KEY `name` (`name`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (10000,'旺仔小分队','130000000','720106a435cd627d0e4f1fb2687fc514','0',1657177730,1639985608,'1',2,'ea733fda0a5c574622ed36bcd440a85f');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_info` (
  `uid` int NOT NULL COMMENT '用户id',
  `total_store` decimal(20,2) DEFAULT '5000000.00' COMMENT '总可用容量单位kb',
  `use_store` decimal(20,2) DEFAULT '0.00' COMMENT '已用 单位KB',
  `files_num` int DEFAULT '0' COMMENT '文件总数量',
  UNIQUE KEY `user_info_un` (`uid`),
  CONSTRAINT `user_info_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户数据信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_info`
--

LOCK TABLES `user_info` WRITE;
/*!40000 ALTER TABLE `user_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_token` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL COMMENT '用户id',
  `token` varchar(100) DEFAULT NULL COMMENT '用户token',
  `time` int DEFAULT NULL COMMENT '时间',
  `ip` varchar(20) DEFAULT NULL COMMENT '登录IP',
  `device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '设备类型',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_token_UN` (`token`),
  KEY `id` (`id`),
  KEY `uid` (`uid`),
  KEY `user_token_token_IDX` (`token`) USING BTREE,
  CONSTRAINT `user_token_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=821 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_token`
--

LOCK TABLES `user_token` WRITE;
/*!40000 ALTER TABLE `user_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'imgurl'
--

--
-- Dumping routines for database 'imgurl'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-07-08 20:41:35
