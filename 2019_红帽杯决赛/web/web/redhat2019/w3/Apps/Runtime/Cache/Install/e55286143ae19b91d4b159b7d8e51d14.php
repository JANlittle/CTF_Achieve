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
                    		
    <li class="active"><a href="javascript:;">安装协议</a></li>
    <li><a href="javascript:;">环境检测</a></li>
    <li><a href="javascript:;">创建数据库</a></li>
    <li><a href="javascript:;">安装</a></li>
    <li><a href="javascript:;">完成</a></li>

                    	</ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="jumbotron masthead">
            <div class="container">
                
    <h2><?php echo C('SYSTEM_NAME');?> 安装协议</h2>
    <p><?php echo C('SYSTEM_COPYRIGHT');?></p>
    <p>感谢您选择<?php echo C('SYSTEM_NAME');?>（以下简称：<?php echo C('SYSTEM_SHORTNAME');?>）。<?php echo C('SYSTEM_DESCRIPTION');?></p>
    <p><?php echo C('SYSTEM_SHORTNAME');?>的官方网址：<?php echo C('SYSTEM_URL');?></p>
    <p>为了使你正确并合法地使用本软件，请在使用前务必阅读清楚下面的协议条款：</p>
    <p class="strong">一、本授权协议适用于 <?php echo C('SYSTEM_SHORTNAME');?> v3.x.x 版本，<?php echo C('SYSTEM_SHORTNAME');?>官方对本授权协议有最终解释权。</p>
    <p class="strong">二、协议许可的权利</p>
    <p class="textindex">1、您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。</p>
    <p class="textindex">2、您可以在协议规定的约束和限制范围内修改 <?php echo C('SYSTEM_SHORTNAME');?> 源代码或界面风格以适应您的网站要求。</p>
    <p class="textindex">3、您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。</p>
    <p class="textindex">4、获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力</p>

    <p class="strong">三、协议规定的约束和限制</p>
    <p class="textindex">1、未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目的或实现盈利的网站）。</p>
    <p class="textindex">2、未经官方许可，不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。</p>
    <p class="textindex">3、未经官方许可，禁止在 <?php echo C('SYSTEM_SHORTNAME');?> 的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</p>
    <p class="textindex">5、如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。</p>

            </div>
        </div>

        <footer class="footer navbar-fixed-bottom">
            <div class="container">
                <div>
                	
    <?php if($installok != 1): ?><a class="btn btn-primary btn-lg" href="<?php echo U('Install/step1');?>">同意安装协议</a>
        <a class="btn btn-default btn-lg" href="http://www.aikehou.com/">不同意</a>
    <?php else: ?>
        <a class="btn btn-primary btn-lg" href="<?php echo U('Kgmanage/Login/index');?>" target="_blank">登录后台</a>
        <a class="btn btn-success btn-lg" href="/"  target="_blank">访问首页</a>
        <span class="red" style="padding:10px;position: relative;top:4px;">系统已经成功安装，请不要重复安装！</span><?php endif; ?>

                </div>
            </div>
        </footer>
    </body>
</html>