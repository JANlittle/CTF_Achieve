/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.14 ]
* Description [ 基础js ]
*/

$(function(){

    /*$(document).ajaxStart(function(){
        //添加进度条
        $('body').append('<div id="loadingbar" class="waiting" style="width:0"><dt></dt><dd></dd></div>');
        $("#loadingbar").animate({width:myrand(4,8)*10+"%"},50);
    }).ajaxSend(function(e, jqXHR, options){

    }).ajaxSuccess(function(e, xhr, opts){
        //数据返回成功
        $("#loadingbar").animate({width:"100%"},50);

    }).ajaxComplete(function(event,request, settings){
        //ajax完成
        $("#loadingbar").fadeOut(1000,'linear',function(){
            $("#loadingbar").remove();
        });

    }).ajaxStop(function(){
        //ajax结束
    });*/

    //ajax全局函数
    var loading;
    $(document).ajaxSend(function(e, jqXHR, options){
        loading = layer.load(1,{shade: [0.3,'#000']});
    }).ajaxSuccess(function(e, xhr, opts){
        layer.close(loading);
    });
});


/**
 * [myrand 范围随机数]
 * @param  {[type]} begin [开始]
 * @param  {[type]} end   [结尾]
 * @return {[type]}       [返回随机数]
 */
function myrand(begin,end){
     return Math.random()*(end-begin)+begin;
    //return Math.floor(Math.random()*(end-begin))+begin;
}

/**
 * [checkBaoming 在线报名]
 * @param  {[type]} url     [description]
 * @param  {[type]} formDom [description]
 * @return {[type]}         [description]
 */
function checkBaoming(url,formDom){
    //验证数据合法性
    //验证姓名
    if(!checkName($("#name").val())){
        layer.open({
            content:'姓名格式不正确！',
            yes:function(index){
                layer.close(index);
                $("#name").focus();
            },
            icon:2
        });
        return false;
    }

    //验证联系电话
    if(!checkTel($("#tel").val())){
        layer.open({
            content:'手机格式不正确！',
            yes:function(index){
                layer.close(index);
                $("#tel").focus();
            },
            icon:2
        });
        return false;
    }

    //验证QQ号码
    if($("#myqq").val().length > 0){
        if(!checkQQ($("#myqq").val())){
            layer.open({
                content:'QQ号码格式不正确！',
                yes:function(index){
                    layer.close(index);
                    $("#myqq").focus();
                },
                icon:2
            });
            return false;
        }
    }

    //执行写入
    submitForm(url,formDom);
}


/**
 * [openwindow 打开新窗口]
 * @param  {[type]} url [description]
 * @return {[type]}     [description]
 */
function openwindow(url,width,height){
    if(isNaN(width)){
        var width = 800;
    }

    if(isNaN(height)){
        var height = 600;
    }
    var openUrl = url;
    var iWidth = width;
    var iHeight = height;
    var iTop = (window.screen.availHeight-30-iHeight)/2;
    var iLeft = (window.screen.availWidth-10-iWidth)/2;
    window.open(openUrl,"","height="+iHeight+", width="+iWidth+", top="+iTop+", left="+iLeft); 
}

/**
 * [submitForm 表单提交ajx数据处理]
 * @param  {[url]} str    [数据发送的url地址]
 * @param  {[object]} obj [对象的DOM节点：#myFrom]
 * @return {[json]}     [返回json]
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
 * [submitForm_Redirect 表单提交ajx数据处理：成功返回数据直接跳转]
 * @param  {[url]} str    [数据发送的url地址]
 * @param  {[object]} obj [对象的DOM节点：#myFrom]
 * @return {[json]}     [返回json]
 */
function submitForm_Redirect (url,obj) {
   $.post(url, $(obj).serialize(), function(data, textStatus, xhr) {
        //弹出消息
        if(data.status){
            if(data.url){
                location.href = data.url;
            } else {
                location.reload();
            }
        } else {
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
 * [loginout 退出登陆]
 * @param  {[string]} url [url地址]
 */
function loginout(url){
    $.post(url, {}, function(data, textStatus, xhr) {
        //弹出消息
        if(data.status){
            layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                if(data.url){
                    location.href = data.url;
                } else {
                    //location.reload();
                }
            });
        } else {
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
 * [AddFavorite 收菜本站]
 * @param {[type]} title [description]
 * @param {[type]} url   [description]
 */
function AddFavorite(title, url) {
    try {
        window.external.addFavorite(url, title);
    } catch (e) {
        try {
            window.sidebar.addPanel(title, url, "");
        }
        catch (e) {
            layer.alert("抱歉，您所使用的浏览器无法完成此操作。\n\n加入收藏失败，请使用Ctrl+D进行添加");
        }
    }
}

/**
 * [SetHome 设置主页]
 * @param {[type]} obj [description]
*/
function SetHome(obj){
    url = document.URL;
    try{
        obj.style.behavior='url(#default#homepage)';
        obj.setHomePage(url);
    }catch(e) {
        if(window.netscape){
            try{
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
            }catch(e){
               layer.alert("抱歉，此操作被浏览器拒绝！\n\n请在浏览器地址栏输入“about:config”并回车然后将[signed.applets.codebase_principal_support]设置为'true'");     
            }
        }else{
            layer.alert("抱歉，您所使用的浏览器无法完成此操作。\n\n您需要手动将【"+url+"】设置为首页。");
        }
    }
}

/**
 * [checkQQ 验证QQ号码5-11位]
 * @param  {[type]} qq [description]
 * @return {[type]}    [description]
*/
function checkQQ(qq) {
    var filter = /^\s*[.0-9]{5,12}\s*$/;
    if (!filter.test(qq)) {
        return false;
    } else {
        return true;
    }
}

/**
 * [checkEmail 验证邮箱格式]
 * @param  {[type]} str [description]
 * @return {[type]}     [description]
*/
function checkEmail(str) {
    if (str.charAt(0) == "." || str.charAt(0) == "@" || str.indexOf('@', 0) == -1 ||
        str.indexOf('.', 0) == -1 || str.lastIndexOf("@") == str.length - 1 ||
        str.lastIndexOf(".") == str.length - 1 ||
        str.indexOf('@.') > -1)
        return false;
    else
        return true;
}

/**
 * [checkTel 校验手机号码]
 * @param  {[type]} s [description]
 * @return {[type]}   [description]
*/
function checkTel(s) {
    //var patrn = /^[+]{0,1}(\d){1,3}[ ]?([-]?((\d)|[ ]){1,12})+$/;
    var patrn = /^(13[0-9]{9})|(14[0-9])|(18[0-9])|(15[0-9][0-9]{8})$/;
    if (!patrn.exec(s)) return false
    return true
}

/**
 * [checkName 验证姓名]
 * @param  {[type]} name [description]
 * @return {[type]}      [description]
*/
function checkName(name){
    if(name.match(/^[\u4e00-\u9fa5]+$/)){
        return true;
    } else{
        return false;
    }
}


/**
 * [dialogIframe iframe弹窗]
 * @param  {[type]} url   [iframe地址]
 * @param  {[type]} title [标题]
 */
function dialogIframe(url,title){
    //var url = url + "&iframe=" + window.name;
    parent.layer.open({
        title:title,
        type:2,
        area: ['80%','80%'],
        maxmin:true,
        content:url
    });
}

/**
 * [closeIframe 关闭父级iframe]
 */
function closeIframe(){
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.close(index);
}