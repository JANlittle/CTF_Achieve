var iTime = 59;
var Account;
//控制时间
function RemainTime(buttonDom){
    $(buttonDom).prop('disabled', true);
    var iSecond,sSecond="",sTime="";
    if (iTime >= 0){
        iSecond = parseInt(iTime%60);
        iMinute = parseInt(iTime/60)
        if (iSecond >= 0){
            if(iMinute>0){
                sSecond = iMinute + "分" + iSecond + "秒";
            }else{
                sSecond = iSecond + "秒";
            }
        }
        sTime=sSecond;
        if(iTime==0){
            clearTimeout(Account);
            sTime='获取验证码';
            iTime = 59;
            $(buttonDom).prop('disabled', true);
        }else{
            Account = setTimeout("RemainTime('"+buttonDom+"')",1000);
            iTime=iTime-1;
        }
    }else{
        sTime='没有倒计时';
    }
    $(buttonDom).val(sTime);
}
/**
 * [sendCode 短信验证码]
 * @param  {[url]} str    [数据发送的url地址]
 * @param  {[object]} obj [对象的DOM节点：#myFrom]
 * @param  {[object]} buttonDom [按钮dom]
 * @return {[json]}       [返回json]
 */
function sendCode (url,obj,buttonDom) {
   $.post(url, $(obj).serialize(), function(data, textStatus, xhr) {
        //弹出消息
        if(data.status){
            layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                //设置限制秒数
                RemainTime(buttonDom);
            });
        } else {
            layer.open({
                content:data.info,
                yes:function(index){
                    layer.close(index);
                },
                icon:2
            });
        }
    });
}