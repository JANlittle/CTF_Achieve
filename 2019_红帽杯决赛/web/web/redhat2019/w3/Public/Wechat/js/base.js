/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.14 ]
* Description [ 基础js ]
*/
$(function(){
    //ajax全局函数
    var loading;
    $(document).ajaxSend(function(e, jqXHR, options){
        loading = layer.open({type: 2,content: '',shade: false});
    }).ajaxSuccess(function(e, xhr, opts){
        layer.close(loading);
    });
});


/**
 * [postUrl post传递url处理函数]
 * @return {[json]}     [返回json]
 */
function postUrl(url,sendData){
    $.post(url, {data:sendData}, function(data){
        //弹出消息
        if(data.status){
            layer.open({
                content: data.info,
                btn:['确定'],
                yes: function(index){
                    if(data.url){
                        location.href = data.url;
                    } else {
                        layer.close(index);
                        return false;
                    }
                }
            });
        } else {
            layer.open({
                content: data.info,
                btn:['确定'],
                yes: function(index){
                    layer.close(index);
                    return false;
                }
            });
        }
    });
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
            layer.open({
                content: data.info,
                btn:['确定'],
                yes: function(index){
                    if(data.url){
                        location.href = data.url;
                    } else {
                        layer.close(index);
                        return false;
                    }
                }
            });
        } else {
            layer.open({
                content: data.info,
                btn:['确定'],
                yes: function(index){
                    layer.close(index);
                    return false;
                }
            });
        }
    });
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