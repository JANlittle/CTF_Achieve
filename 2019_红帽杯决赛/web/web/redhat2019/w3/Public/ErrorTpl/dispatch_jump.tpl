<!doctype html>
<html>
    <head>
    <meta charset="utf-8">
    <title>跳转提示</title>
    <style type="text/css">
        html,body,div,ul,ol,li,dl,dt,dd,h1,h2,h3,h4,h5,h6,p {
            margin: 0;
            padding: 0
        }

        h1,h2,h3,h4,h5,h6,em {
            font-size: 1em;
            font-weight: normal;
            font-style: normal
        }

        a {
            text-decoration: none;
            color: #515151
        }

        ul, li {
            list-style: none
        }

        body {
            font-family: "微软雅黑","Microsoft Yahei",Arial,Helvetica,sans-serif,"宋体";
            font-size: 14px;
            color: #515151
        }

        .tips_cont {
            height: 420px;
            padding-top: 100px;
            width: 1080px;
            margin: 0px auto;
            overflow: hidden
        }

        .tips_cont h2 {
            font-size: 30px;
            color: #3a3a3a;
            padding-bottom: 10px
        }

        .tips_cont h5 {
            font-size: 18px;
            color: #3a3a3a;
            padding-bottom: 58px
        }

        .img_tips {
            float: left;
            margin: 0px 88px 0px 155px
        }

        .message_tips {
            float: left;
            margin-top: 42px
        }

        .btn_tips {
            display: block;
            width: 265px;
            height: 55px;
            background-color: #31A5E7;
            font-size: 24px;
            color: #FFF;
            text-align: center;
            line-height: 55px
        }

        .jump { padding: 16px 0; font-size:12px; }
        .jump a { color: #333; font-size:12px; }
        footer{ text-align:center; padding:5px 12px; color:#333; font-size:12px;border-top:1px solid #ddd;}
        </style>
    </head>
    <body>
        <div class="tips_cont">
            <present name="message">
                <div class="img_tips"><img src="__PUBLIC__/ErrorTpl/404/panda-1.jpg"></div>
            <else/>
                <div class="img_tips"><img src="__PUBLIC__/ErrorTpl/404/panda-2.jpg"></div>
            </present>
            <div class="message_tips">
                <present name="message">
                    <h2>操作成功！</h2>
                    <h5 class="success"><?php echo($message); ?></h5>
                <else/>
                    <h2>操作失败！</h2>
                    <h5 class="error"><?php echo($error); ?></h5>
                </present>
                <p class="jump">等待 <b id="wait" style="color:#f00;"><?php echo($waitSecond); ?></b> 秒后页面将自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> </p>
                <a class="btn_tips" href="<?php echo($jumpUrl); ?>">返回上一页</a>
            </div>
        </div>
        <footer><br>CopyRight © 2015 ~ 2016<a href="http://www.aikehou.com/" target="_blank">『爱客猴内容管理系统』后台管理</a> V3.2.3<br><br>Powered by <a href="http://www.aikehou.com/" target="_blank">www.aikehou.com</a></footer>
        
        <script type="text/javascript">
            (function(){
                var wait = document.getElementById('wait'),href = document.getElementById('href').href;
                var interval = setInterval(function(){
                    var time = --wait.innerHTML;
                    if(time <= 0) {
                        location.href = href;
                        clearInterval(interval);
                    };
                }, 1000);
            })();
        </script>
</body>
</html>