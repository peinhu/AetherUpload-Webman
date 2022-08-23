# AetherUpload-Webman  

    
本项目迁移自广受好评的Laravel大文件上传扩展包：[AetherUpload-Laravel](https://github.com/peinhu/AetherUpload-Laravel)

![示例页面](http://wx2.sinaimg.cn/mw690/69e23056gy1fho6ymepjlg20go0aknar.gif) 

# 功能特性
- [x] 百分比进度条  
- [x] 文件类型限制  
- [x] 文件大小限制  
- [x] 多语言支持  
- [x] 资源分组配置  
- [x] 上传完成事件   
- [x] 同步上传 *①*  
- [x] 断线续传 *②*  
- [x] 文件秒传 *③*  
- [x] 自定义中间件 *④*  
- [x] 自定义路由   
- [x] 宽松模式

*①：同步上传相比异步上传，在上传带宽足够大的情况下速度稍慢，但同步可在上传同时进行文件的拼合，而异步因文件块上传完成的先后顺序不确定，需要在所有文件块都完成时才能拼合，将会导致异步上传在接近完成时需等待较长时间。同步上传每次只有一个文件块在上传，在单位时间内占用服务器的内存较少，相比异步方式可支持更多人同时上传。*  

*②：断线续传和断点续传不同，断线续传是指遇到断网或无线网络不稳定时，在不关闭页面的情况下，上传组件会定时自动重试，一旦网络恢复，文件会从未上传成功的那个文件块开始继续上传。断线续传在刷新页面或关闭后重开是无法续传的，之前上传的部分已成为无效文件。*  

*③：文件秒传需服务端Redis和客户端浏览器支持(FileReader、File.slice())，两者缺一则秒传功能无法生效。默认关闭，需在配置文件中开启。*  

*④：结合自定义中间件，可对已上传资源的访问、下载行为进行权限控制。*


# 安装 

0 在终端内切换到你的webman项目根目录，执行`composer require peinhu/aetherupload-webman ^1.0`   
  
1 在浏览器访问`http://域名/aetherupload`可到达示例页面  

*提示：更改相关配置选项请编辑`config/plugin/peinhu/aetherupload-webman/app.php`。*  

# 使用  
  
**文件上传**  

参考示例文件及注释部分，在需要上传大文件的页面引入相应文件和代码。
可使用自定义中间件来对文件上传进行额外过滤，还可使用上传完成事件对上传的文件进一步处理。  

**分组配置**  

在配置文件的groups下新增分组，运行`php webman aetherupload:groups`自动创建对应目录。  

**自定义中间件**  

参考Webman文档路由中间件部分，创建你的中间件并将你编写的中间件名称填入配置文件对应部分，如`[app\middleware\MiddlewareA::class,app\middleware\MiddlewareB::class]`。  

**上传完成事件**  

分为上传完成前和上传完成后事件，参考Webman文档常用组件Event事件部分，为`'aetherupload.before_upload_complete'`及`'aetherupload.upload_complete'`配置对应的事件处理类，在本插件配置文件app.php中将`groups`下相应选项设置为`true`。

**添加秒传功能（需Redis及浏览器支持）**

参考Webman文档Redis部分，安装所需依赖。安装Redis并启动服务。安装predis包`composer require predis/predis`，在`config/redis.php`中设置client为`'predis'`。

*提示：在Redis中维护了一份与实际资源文件对应的秒传清单，实际资源文件的增删造成的变化均需要同步到秒传清单中，否则会产生脏数据，扩展包已包含新增部分，当删除资源文件时，使用者需手动调用对应方法删除秒传清单中的记录。* 
```php
\AetherUpload\Util::deleteResource($savedPath); //删除对应的资源文件
\AetherUpload\Util::deleteRedisSavedPath($savedPath); //删除对应的Redis秒传记录
``` 
  
**使用方便的控制台命令**  

`php webman aetherupload:groups` 列出所有分组并自动创建对应目录  
`php webman aetherupload:build` 在Redis中重建资源文件的秒传清单  
`php webman aetherupload:clean 2` 清除2天前的无效临时文件  

# 优化建议
* **（推荐）设置每天自动清除无效的临时文件**  
由于上传流程存在意外终止的情况，如在传输过程中强行关闭页面或浏览器，将会导致已产生的文件部分成为无效文件，占据大量的存储空间，我们可以使用crontab的定时任务功能来定期清除它们。  
在Linux中运行`crontab -e`命令，确保文件中包含这行代码：  
```php
0 0 * * * php /项目根目录的绝对路径/webman aetherupload:clean 1> /dev/null 2>&1  
```  

* **设置每天自动重建Redis中的秒传清单**  
不恰当的处理和某些极端情况可能使秒传清单中出现脏数据，从而影响到秒传功能的准确性，重建秒传清单可消除脏数据，恢复与实际资源文件的同步。  
在Linux中运行`crontab -e`命令，确保文件中包含这行代码：  
```php
0 0 * * * php /项目根目录的绝对路径/webman aetherupload:build 1> /dev/null 2>&1  
```  

* **提高分块临时文件读写速度（仅对PHP生效）**  
利用Linux的tmpfs文件系统，来达到将上传的分块临时文件放到内存中快速读写的目的，通过以空间换时间，提升读写效率，将会**额外占用**部分内存（约1个分块大小）。  
将php.ini中上传临时目录`upload_tmp_dir`项的值设置为`"/dev/shm"`，重启服务。  

* **提高分块临时文件读写速度（对系统临时目录生效）**  
利用Linux的tmpfs文件系统，来达到将上传的分块临时文件放到内存中快速读写的目的，通过以空间换时间，提升读写效率，将会**额外占用**部分内存（约1个分块大小）。  
执行以下命令：    
`mkdir /dev/shm/tmp`  
`chmod 1777 /dev/shm/tmp`  
`mount --bind /dev/shm/tmp /tmp`  

# 兼容性
<table>
  <th></th>
  <th>IE</th>
  <th>Edge</th>
  <th>Firefox</th>
  <th>Chrome</th>
  <th>Safari</th>
  <tr>
  <td>上传</td>
  <td>10+</td>
  <td>12+</td>
  <td>3.6+</td>
  <td>6+</td>
  <td>5.1+</td>
  </tr>
  <tr>
  <td>秒传</td>
  <td>10+</td>
  <td>12+</td>
  <td>3.6+</td>
  <td>6+</td>
  <td>6+</td>
  </tr>
</table>

# 安全性
AetherUpload在上传前使用白名单+黑名单的形式进行文件后缀名过滤，上传后再检查文件的Mime-Type类型。白名单直接限制了保存文件扩展名，黑名单默认屏蔽了常见的可执行文件扩展名，来阻止上传恶意文件，安全起见白名单一栏不应留空。  

虽然做了诸多安全工作，但恶意文件上传是防不胜防的，建议正确设置上传目录权限，确保相关程序对资源文件没有执行权限。

# 更新日志  
详见[CHANGELOG.md](https://github.com/peinhu/AetherUpload-Webman/blob/master/CHANGELOG.md)  

# 许可证
使用GPLv2许可证, 查看[LICENCE](https://github.com/peinhu/AetherUpload-Webman/blob/master/LICENSE)文件以获得更多信息。  
