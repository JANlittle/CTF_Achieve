<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta name="author" content="CopyLian">
        <title><?php echo C('SYSTEM_TITLE');?></title>
        <meta name="keywords" content="<?php echo C('SYSTEM_KEYWORDS');?>" />
        <meta name="description" content="<?php echo C('SYSTEM_DESCRIPTION');?>" />
        <link href="/aikehou/echo/Public/jqueryui/bootstrap/css/bootstrap.css" rel="stylesheet">
        <link rel="stylesheet" href="/aikehou/echo/Public/jqueryui/bootstrapvalidator/dist/css/bootstrapValidator.css"/>
        <link href="/aikehou/echo/Public/Install/css/install.css" rel="stylesheet">
        <!--[if lt IE 9]>
            <script src="/aikehou/echo/Public/jqueryui/bootstrap/js/html5shiv.min.js"></script>
            <script src="/aikehou/echo/Public/jqueryui/bootstrap/js/respond.min.js"></script>
        <![endif]-->
        <script src="/aikehou/echo/Public/jqueryui/jquery-2.0.3.min.js"></script>
        <script src="/aikehou/echo/Public/jqueryui/bootstrap/js/bootstrap.js"></script>
        <script type="text/javascript" src="/aikehou/echo/Public/jqueryui/bootstrapvalidator/dist/js/bootstrapValidator.js"></script>
        <script type="text/javascript" src="/aikehou/echo/Public/jqueryui/bootstrapvalidator/dist/js/language/zh_CN.js"></script>
        <script src="/aikehou/echo/Public/jqueryui/layer/layer.js"></script>
        <script src="/aikehou/echo/Public/jqueryui/layer/extend/layer.ext.js"></script>
        <script type="text/javascript">
            layer.config({
                extend: ['skin/moon/style.css'],
                skin: 'layer-ext-moon'
            });
        </script>
        <script type="text/javascript" src="/aikehou/echo/Public/jqueryui/jquery.form.js"></script>
        <script src="/aikehou/echo/Public/jqueryui/jquery.fixedsSerializeArray.js"></script>
        <script src="/aikehou/echo/Public/Install/js/base.js"></script>
    </head>

    <body>
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <div class="topbar-logo" style='padding-top: 4px'><a target="_blank" href="http://www.aikehou.com/"><img src="/aikehou/echo/Public/Install/images/logo.png" alt="爱客猴（echo）内容管理系统 v3.2.3"></a></div>
                    </div>
                    <div class="navbar-collapse collapse">
                    	<ul id="step" class="nav navbar-nav">
                    		
    <li><a href="javascript:;">安装协议</a></li>
    <li><a href="javascript:;">环境检测</a></li>
    <li><a href="javascript:;">创建数据库</a></li>
    <li class="active"><a href="javascript:;">完成</a></li>

                    	</ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="jumbotron masthead">
            <div class="container">
                
    <h1>完成</h1>
    <p>:）恭喜你，安装完成！</p>
	<p>现在你可以正确地使用 <a href="http://www.aikehou.com/" target="_blank"><?php echo C('SYSTEM_NAME');?></a> 在使用系统过程，如有出现任何问题可以第一时间反馈给系统官方，我们会在最快时间内给出解决方案！</p>
    <p class="strong">温馨提示：</p>
    <p>为了系统安全性，安装完成后，请手动删除 Apps/Install 模块，使用本系统前，请你确保你已经认真阅读了本系统的安装协议，请尊重版权，谢谢合作！</p>
    <p>开发人员：CopyLian、copy酱</p>
    <p>电子邮件：copylian@aikehou.com</p>
    <p>联系Q Q：<a href="tencent://message/?uin=1010117575&Site=www.aikehou.com&Menu=yes" target="_blank">1010117575</a></p>
    <p><?php echo C('SYSTEM_COPYRIGHT');?></p>

            </div>
        </div>

        <footer class="footer navbar-fixed-bottom">
            <div class="container">
                <div>
                	
    <a class="btn btn-primary btn-lg" href="<?php echo U('Kgmanage/Login/index');?>" target="_blank">登录后台</a>
    <a class="btn btn-success btn-lg" href="/"  target="_blank">访问首页</a>

                </div>
            </div>
        </footer>
    </body>
</html>