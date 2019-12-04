/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.14 ]
* Description [ 微信js ]
*/

wx.config({
   debug: WechatData.debug,
   appId: WechatData.appId,
   timestamp: WechatData.timestamp,
   nonceStr: WechatData.nonceStr,
   signature: WechatData.signature,
   jsApiList: [
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'hideOptionMenu',
        'showOptionMenu'
   ]
}); 
  
wx.ready(function(){
    //是否不显示菜单
    if(WechatData.hideOptionMenu){
        wx.hideOptionMenu();
    } else {
        wx.showOptionMenu();
    }

    // 分享到朋友圈
    wx.onMenuShareTimeline({
        title: WechatData.title,
        link: WechatData.link,
        imgUrl: WechatData.img,
        success: function () { 
           // 用户确认分享后执行的回调函数
        },
        cancel: function () { 
            // 用户取消分享后执行的回调函数
        }
    });
    
    //分享给朋友
    wx.onMenuShareAppMessage({
        title: WechatData.title,
        desc: WechatData.desc,
        link: WechatData.link,
        imgUrl: WechatData.img, 
        type: 'link', 
        dataUrl: '', 
        success: function () { 
           // 用户确认分享后执行的回调函数
        },
        cancel: function () { 
            // 用户取消分享后执行的回调函数
        }
    });
    
    //分享到QQ
    wx.onMenuShareQQ({
        title: WechatData.title, 
        desc: WechatData.desc,
        link: WechatData.link,
        imgUrl: WechatData.img,
        success: function () { 
           // 用户确认分享后执行的回调函数
        },
        cancel: function () { 
            // 用户取消分享后执行的回调函数
        }
    });
    
    //分享到腾讯微博
    wx.onMenuShareWeibo({
        title: WechatData.title,
        desc: WechatData.desc,
        link: WechatData.link,
        imgUrl: WechatData.img,
        success: function () { 
           // 用户确认分享后执行的回调函数
        },
        cancel: function () { 
            // 用户取消分享后执行的回调函数
        }
    });

    //分享到qq空间
    wx.onMenuShareQZone({
        title: WechatData.title, // 分享标题
        desc: WechatData.desc, // 分享描述
        link:  WechatData.link, // 分享链接
        imgUrl: WechatData.img, // 分享图标
        success: function () { 
           // 用户确认分享后执行的回调函数
        },
        cancel: function () { 
            // 用户取消分享后执行的回调函数
        }
    });
});