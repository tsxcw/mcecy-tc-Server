# 创次元图床端API程序

****

创次元前端开源地址 https://github.com/tsxcw/mcecy-tc-Client

创次元后台管理开源地址 https://github.com/tsxcw/mcecy-tc-admin

服务器监控程序开源地址 https://gitee.com/jhjd/server-monitoring

安装教程 https://jhjd.gitee.io/staticdata/index.html

** 注意，此教程是基于宝塔面板为服务器管理面板作为演示 **

1，确认系统存在 php（需要安装 redis 扩展） 环境以及 mysql，还有 redis 程序。

2，首先将程序源代码克隆或者下载到服务器。

3，然后添加网站，将根目录设置为程序源文件所在目录。

4，通过浏览器访问 域名/api/index/run;返回 ok 则表示成功。

5，记得添加 ssl 证书。

后端 nginx配置

# 伪静态

```
location / {
 if (!-e $request_filename) {
   rewrite ^(.*)$ /index.php?s=$1 last;
  break;
  }
}
```


安装疑问联系Q：1756328925