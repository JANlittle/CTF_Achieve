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
    <li class="active"><a href="javascript:;">创建数据库</a></li>
    <li><a href="javascript:;">完成</a></li>

                    	</ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="jumbotron masthead">
            <div class="container">
                
    <?php
 defined('SAE_MYSQL_HOST_M') or define('SAE_MYSQL_HOST_M', 'localhost'); defined('SAE_MYSQL_HOST_M') or define('SAE_MYSQL_PORT', '3306'); ?>
    <h1>创建数据库</h1>
    <form action="" method="post" id="form" onsubmit="return false;">
        <div class="create-database">
            <div class="form-group">
                <label for="dbtype">数据库类型</label>
                <select name="db[dbtype]" class='form-control' id="dbtype">
	                <option value="mysql">mysql</option>
                </select>
            </div>
            <div class="form-group">
                <label for="dbhost">数据库服务器</label>
                <input type="text" name="db[dbhost]" value="<?php if(defined("SAE_MYSQL_HOST_M")): echo (SAE_MYSQL_HOST_M); else: ?>localhost<?php endif; ?>" class='form-control' id="dbhost">
                <div class="help-block-kk">数据库服务器IP，一般为localhost、127.0.0.1</div>
            </div>
            <div class="form-group">
                <label for="dbuser">数据库用户名</label>
                <input type="text" name="db[dbuser]" value="<?php if(defined("SAE_MYSQL_USER")): echo (SAE_MYSQL_USER); else: ?>root<?php endif; ?>" class='form-control' id="dbuser" placeholder="请输入数据库用户名">
            </div>
            <div class="form-group">
                <label for="dbpasswd">数据库密码</label>
                <input type="text" name="db[dbpasswd]" value="<?php if(defined("SAE_MYSQL_PASS")): echo (SAE_MYSQL_PASS); endif; ?>" class='form-control' id="dbpasswd" placeholder="请输入数据库密码">
            </div>
            <div class="form-group">
                <label for="dbport">数据库端口</label>
                <input type="text" name="db[dbport]" value="<?php if(defined("SAE_MYSQL_PORT")): echo (SAE_MYSQL_PORT); else: ?>3306<?php endif; ?>" class='form-control' id="dbport" placeholder="请输入数据库端口">
                <div class="help-block-kk">数据库服务连接端口，一般为3306</div>
            </div>
            <div class="form-group">
                <label for="dbname">数据库名</label>
                <input type="text" name="db[dbname]" value="<?php if(defined("SAE_MYSQL_DB")): echo (SAE_MYSQL_DB); else: ?>echo<?php endif; ?>" class='form-control' id="dbname" placeholder="请输入数据库名">
                <div class="help-block-kk">只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾</div>
            </div>
            <div class="form-group">
                <label for="dbprefix">数据表前缀</label>
                <input type="text" name="db[dbprefix]" value="echo_" class='form-control' id="dbprefix" placeholder="请输入数据表前缀">
                <div class="help-block-kk">只能由英文字母、数字和下划线组成，并且仅能字母开头。</div>
            </div>
        </div>

        <div class="create-database">
            <h2>Ucenter用户中心信息</h2>
            <div class="form-group">
                <label for="ucdbname">用户中心数据库名</label>
                <input type="text" name="uc[ucdbname]" value="<?php if(defined("SAE_MYSQL_DB")): echo (SAE_MYSQL_DB); else: ?>echoucenter<?php endif; ?>" class='form-control' id="ucdbname" placeholder="请输入用户中心数据库名">
                <div class="help-block-kk">只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾</div>
            </div>
            <div class="form-group">
                <label for="ucdbprefix">用户中心数据表前缀</label>
                <input type="text" name="uc[ucdbprefix]" value="echo_" class='form-control' id="ucdbprefix" placeholder="请输入用户中心数据表前缀">
                <div class="help-block-kk">只能由英文字母、数字和下划线组成，并且仅能字母开头。</div>
            </div>
            <div class="form-group">
                <label for="ucdomain">用户中心域名</label>
                <input type="text" name="uc[ucdomain]" value="http://ucenter.aikehou.com" class='form-control' id="ucdomain" placeholder="请输入用户中心域名">
                <div class="help-block-kk">访问用户中心的独立域名，如：http://ucenter.aikehou.com</div>
            </div>
        </div>

        <div class="create-database">
            <h2>创始人帐号信息</h2>
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" name="admin[username]" value="admin" class='form-control' id="username" placeholder="请输入用户名">
                <div class="help-block-kk">只能由英文字母、数字组成，并且仅能字母开头，5 到 20 个字符</div>
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" name="admin[password]" value="" class='form-control' id="password" placeholder="请输入密码">
                <div class="help-block-kk">密码长度为 6 到 20 个字符</div>
            </div>
            <div class="form-group">
                <label for="passwordck">确认密码</label>
                <input type="password" name="admin[passwordck]" value="" class='form-control' id="passwordck" placeholder="请输入确认密码">
            </div>
        </div>
    </form>
    <div class="blank10"></div>
    <div class="blank10"></div>
    <div class="blank10"></div>
    <script type="text/javascript">
        //验证数据
        $(function(){
            $('#form').bootstrapValidator({
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                fields: {
                    'db[dbtype]': {
                        validators: {
                            notEmpty: {
                                'message':'请选择数据库类型！'
                            }
                        }
                    },
                    'db[dbhost]': {
                        validators: {
                            notEmpty: {
                                'message':'数据库服务器必须填写！'
                            }
                        }
                    },
                    'db[dbuser]': {
                        validators: {
                            notEmpty: {
                                'message':'数据库用户名必须填写！'
                            }
                        }
                    },
                    /*'db[dbpasswd]': {
                        validators: {
                            notEmpty: {
                                'message':'数据库密码必须填写！'
                            }
                        }
                    },*/
                    'db[dbport]': {
                        validators: {
                            notEmpty: {
                                'message':'数据库端口必须填写！'
                            }
                        }
                    },
                    'db[dbname]': {
                        validators: {
                            notEmpty: {
                                'message':'数据库名必须填写！'
                            },
                            regexp: {
                                regexp: /^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}([a-zA-Z0-9]){1}$/,
                                'message':'数据库名格式不正确！'
                            }
                        }
                    },
                    'db[dbprefix]': {
                        validators: {
                            notEmpty: {
                                'message':'数据表前缀必须填写！'
                            },
                            regexp: {
                                regexp: /^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}$/,
                                'message':'数据表前缀格式不正确！'
                            }
                        }
                    },
                    'uc[ucdbname]': {
                        validators: {
                            notEmpty: {
                                'message':'用户中心数据库名必须填写！'
                            },
                            regexp: {
                                regexp: /^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}([a-zA-Z0-9]){1}$/,
                                'message':'用户中心数据库名格式不正确！'
                            }
                        }
                    },
                    'uc[ucdbprefix]': {
                        validators: {
                            notEmpty: {
                                'message':'用户中心数据表前缀必须填写！'
                            },
                            regexp: {
                                regexp: /^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}$/,
                                'message':'用户中心数据表前缀格式不正确！'
                            }
                        }
                    },
                    'uc[ucdomain]': {
                        validators: {
                            notEmpty: {
                                'message':'用户中心域名必须填写'
                            },
                            regexp: {
                                regexp: /^http[s]?:\/\/(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*\'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/\?)|(\/[0-9a-zA-Z_!~\'\.;\?:@&=\+\$,%#-\/^\*\|]*)?)$/,
                                'message':'用户中心域名格式不正确！'
                            }
                        }
                    },
                    'admin[username]': {
                        validators: {
                            notEmpty: {
                                'message':'用户名必须填写！'
                            },
                            stringLength: {
                                min: 5,
                                max: 20,
                                'message':'用户名长度为 5 至 20 个字符'
                            },
                            regexp: {
                                regexp: /^[a-zA-Z]{1}([a-zA-Z0-9])+$/,
                                'message':'用户名格式不正确！'
                            }
                        }
                    },
                    'admin[password]': {
                        validators: {
                            notEmpty: {
                                'message':'密码必须填写！'
                            },
                            stringLength: {
                                min: 6,
                                max: 20,
                                'message':'密码长度为 6 至 20 个字符'
                            }
                        }
                    },
                    'admin[passwordck]': {
                        validators: {
                            notEmpty: {
                                'message':'确认密码必须填写！'
                            },
                            identical: {
                                field: 'admin[password]',
                                message: '两次密码不一致！'
                            }
                        }
                    }
                }
            }).on('success.form.bv', function(e) {
                //禁止默认提交
                e.preventDefault();

                //提交数据
                submitForm('<?php echo U("step2");?>','#form');
            });

            //按钮事件
            $('#anzhuan').click(function() {
                $('#form').bootstrapValidator('validate');
            });
        });
    </script>

            </div>
        </div>

        <footer class="footer navbar-fixed-bottom">
            <div class="container">
                <div>
                	
    <a class="btn btn-success btn-lg" href="<?php echo U('Install/step1');?>">上一步</a>
    <button type="button" class="btn btn-primary btn-lg" id="anzhuan">下一步</button>

                </div>
            </div>
        </footer>
    </body>
</html>