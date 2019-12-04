<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=0.7,user-scalable=0">
        <title><?php echo (C("SYSTEM_TITLE")); ?></title>
        <meta name="keywords" content='<?php echo (C("SYSTEM_KEYWORDS")); ?>'>
        <meta name="description" content='<?php echo (C("SYSTEM_DESCRIPTION")); ?>'>
        <link rel = "shortcut icon" href="/aikehou/echo/Public/images/favicon.ico" />
        <link rel="stylesheet" type="text/css" href="/aikehou/echo/Public/Kgmanage/css/login.css">
       	<link rel="stylesheet" type="text/css" href="/aikehou/echo/Public/Kgmanage/css/default_color.css">
        <!--[if lt IE 9]>
        <script type="text/javascript" src="/aikehou/echo/Public/jqueryui/jquery-1.11.1.min.js"></script>
        <![endif]-->
        <!--[if gte IE 9]><!-->
        <script type="text/javascript" src="/aikehou/echo/Public/jqueryui/jquery-2.0.3.min.js"></script>
        <!--<![endif]-->
        <script type="text/javascript" src="/aikehou/echo/Public/jqueryui/layer/layer.js"></script>
    </head>
    <body id="login-page">
        <div id="main-content">

            <!-- 主体 -->
            <div class="login-body">
                <div class="login-main pr">
                    <form action="" method="post" class="login-form"  onsubmit="return false" id="myForm">
                        <h3 class="welcome"><i class="login-logo"></i><?php echo C('SYSTEM_NAME');?></h3>
                        <div id="itemBox" class="item-box">
                            <div class="item">
                                <i class="icon-login-user"></i>
                                <input type="text" name="username" placeholder="请填写用户名" autocomplete="off" />
                            </div>
                            <span class="placeholder_copy placeholder_un">请填写用户名</span>
                            <div class="item b0">
                                <i class="icon-login-pwd"></i>
                                <input type="password" name="password" placeholder="请填写密码" autocomplete="off" />
                            </div>
                            <span class="placeholder_copy placeholder_pwd">请填写密码</span>
                            <div class="item verifycode">
                                <i class="icon-login-verifycode"></i>
                                <input type="text" name="verify" placeholder="请填写验证码" autocomplete="off">
                                <a class="reloadverify" title="换一张" href="javascript:void(0)"  onclick="changeVerify('#verify_code')">换一张？</a>
                            </div>
                            <span class="placeholder_copy placeholder_check">请填写验证码</span>
                            <div>
                                <img class="verifyimg reloadverify" id="verify_code" alt="点击切换" onclick="changeVerify('#verify_code')" src='<?php echo U("verify");?>'>
                            </div>
                        </div>
                        <div class="login_btn_panel">
                            <button class="login-btn" type="submit" onclick='submitForm("<?php echo U("login");?>","#myForm")'>登录</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <script type="text/javascript">
    	/* 登陆表单获取焦点变色 */
    	$(".login-form").on("focus", "input", function(){
            $(this).closest('.item').addClass('focus');
        }).on("blur","input",function(){
            $(this).closest('.item').removeClass('focus');
        });

        //设置居中
        $(function(){
            if($(window).height() > $(".login-form").outerHeight()){
                $(".login-form").animate({"marginTop": ($(window).height() - $(".login-form").outerHeight())/2},500);
            }
            $(window).resize(function(){
                if($(window).height() > $(".login-form").outerHeight()){
                    $(".login-form").animate({"marginTop": ($(window).height() - $(".login-form").outerHeight())/2},500);
                }
            });
        });

        /**
     * [submitForm 处理登陆表单提交]
     * @param  {[type]} url [数据处理地址]
     * @param  {[DOM]} obj  [表单DOM节点：#myFrom]
     * @return {[json]}     [json格式]
     */
    function submitForm (url,obj) {
       $.post(url, $(obj).serialize(), function(data, textStatus, xhr) {
            //弹出消息
            if(data.status){
                layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                    if(data.url){
                        location.href = data.url;
                    } else {
                        location.reload();
                    }
                });
            } else {
                changeVerify("#verify_code");
                layer.open({
                    content:data.info,
                    yes:function(index){
                        if(data.url){
                            location.href = data.url;
                        } else {
                            layer.close(index);
                        }
                    },
                    icon:2
                });
                //layer.msg(data.info,{icon:2});
            }
        });
    }

     /**
     * [changeVerify 刷新验证码]
     * @param  {[object]} obj [传递的对象]
     * @return {[void]}
     */
    function changeVerify(obj){
        $(obj).attr({
            src:"<?php echo U('verify');?>"+"&time="+Math.random()
        });
    }


		$(function(){
			//初始化选中用户名输入框
			$("#itemBox").find("input[name=username]").focus();
            //placeholder兼容性
                //如果支持
            function isPlaceholer(){
                var input = document.createElement('input');
                return "placeholder" in input;
            }
                //如果不支持
            if(!isPlaceholer()){
                $(".placeholder_copy").css({
                    display:'block'
                })
                $("#itemBox input").keydown(function(){
                    $(this).parents(".item").next(".placeholder_copy").css({
                        display:'none'
                    })
                })
                $("#itemBox input").blur(function(){
                    if($(this).val()==""){
                        $(this).parents(".item").next(".placeholder_copy").css({
                            display:'block'
                        })
                    }
                })
            }
		});
    </script>
</body>
</html>