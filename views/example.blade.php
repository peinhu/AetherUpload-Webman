<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content=""><!--需要csrf token-->
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>This is an example page.</h1>
        <i>view the source code in <a href="/aetherupload/example_source" target="_blank">vendor/peinhu/aetherupload-webman/views/example.blade.php</a></i>
    </div>

    <div class="row">
        <form method="post" action="/aetherupload">
            <div class="form-group " id="aetherupload-wrapper"><!--组件最外部需要一个名为aetherupload-wrapper的id，用以包装组件-->
                <label>文件1(自定义)：</label>
                <div class="controls">
                    <input type="file" id="aetherupload-resource" onchange="aetherupload(this).setGroup('file').setSavedPathField('#aetherupload-savedpath').setPreprocessRoute('/aetherupload/preprocess').setUploadingRoute('/aetherupload/uploading').setLaxMode(false).success(someCallback).upload()"/>
                    <!--需要一个名为aetherupload-resource的id，用以标识上传的文件，setGroup(...)设置分组名，setSavedPathField(...)设置资源存储路径的保存节点，setPreprocessRoute(...)设置预处理路由，setUploadingRoute(...)设置上传分块路由，setLaxMode(...)设置宽松模式，success(...)可用于声名上传成功后的回调方法名。默认为选择文件后触发上传，也可根据需求手动更改为特定事件触发，如点击提交表单时-->
                    <div class="progress " style="height: 6px;margin-bottom: 2px;margin-top: 10px;width: 200px;">
                        <div id="aetherupload-progressbar" style="background:blue;height:6px;width:0;"></div><!--需要一个名为aetherupload-progressbar的id，用以标识进度条-->
                    </div>
                    <span style="font-size:12px;color:#aaa;" id="aetherupload-output"></span><!--需要一个名为aetherupload-output的id，用以标识提示信息-->
                    <input type="hidden" name="file1" id="aetherupload-savedpath"><!--需要一个自定义名称的id，以及一个自定义名称的name值, 用以标识资源储存路径自动填充位置，默认id为aetherupload-savedpath，可根据setSavedPathField(...)设置为其它任意值-->
                </div>
            </div>

            <div class="form-group " id="aetherupload-wrapper">
                <label>文件2(简略)：</label>
                <div class="controls">
                    <input type="file" id="aetherupload-resource" onchange="aetherupload(this).upload()"/>
                    <div class="progress " style="height: 6px;margin-bottom: 2px;margin-top: 10px;width: 200px;">
                        <div id="aetherupload-progressbar" style="background:blue;height:6px;width:0;"></div>
                    </div>
                    <span style="font-size:12px;color:#aaa;" id="aetherupload-output"></span>
                    <input type="hidden" name="file2" id="aetherupload-savedpath">
                </div>
            </div>
 
            <button type="submit" class="btn btn-primary">点击提交</button>
        </form>

        <hr/>

        <div id="result"></div>

    </div>
</div>
<script src="/vendor/aetherupload/js/aetherupload-all.js"></script><!--需引入aetherupload-core.js、zepto.min.js（类似jquery，更轻量化，可与jquery互相代替）、spark-md5.min.js，此文件已包含上述全部，也可分别单独引入 -->

<!-- <script src="vendor/aetherupload/js/aetherupload-core.js"></script> -->
<!-- <script src="https://cdn.bootcdn.net/ajax/libs/spark-md5/3.0.0/spark-md5.min.js"></script> -->
<!-- <script src="https://cdn.bootcdn.net/ajax/libs/zepto/1.2.0/zepto.min.js"></script> -->
<!-- <script src="https://cdn.bootcdn.net/ajax/libs/jquery/1.9.1/jquery.min.js"></script> -->

<script>
    // success(someCallback)中声名的回调方法需在此定义，参数someCallback可为任意名称，此方法将会在上传完成后被调用
    // 可使用this对象获得resourceName,resourceSize,resourceTempBaseName,resourceExt,groupSubdir,group,savedPath等属性的值
    someCallback = function () {
        // Example
        $('#result').append(
            '<p>执行回调 - 文件已上传，原名：<span >' + this.resourceName + '</span> | 大小：<span >' + parseFloat(this.resourceSize / (1000 * 1000)).toFixed(2) + 'MB（按1kb=1000b换算得出）' + '</span> | 储存名：<span >' + this.savedPath.substr(this.savedPath.lastIndexOf('_') + 1) + '</span></p>'
        );
    }

</script>
</body>
</html>
