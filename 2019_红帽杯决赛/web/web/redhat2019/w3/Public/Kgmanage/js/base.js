/*
* Author: [ Copy Lian ]
* Date: [ 2015.05.14 ]
* Description [ 基础js ]
*/
$(function(){
    //设置左侧高度
    $('.sidebar-wrap').height($(window).height() - $(".topbar-wrap").height());
    $(window).resize(function() {
        $('.sidebar-wrap').height($(window).height() - $(".topbar-wrap").height());
    });

    //侧边控制按钮
    $('#control').height($(window).height() - $(".topbar-wrap").height());
    $(window).resize(function() {
        $('#control').height($(window).height() - $(".topbar-wrap").height());
    });

    //设置右侧宽度
    $('.Conframe').width($(window).width() - $(".sidebar-wrap").width());
    $('.crumb-wrap').width($(window).width() - $(".sidebar-wrap").width()); //面包屑
    $('.cont-ft').width($(window).width() - $(".sidebar-wrap").width()-31); //copyright
    $(window).resize(function() {
        $('.Conframe').width($(window).width() - $(".sidebar-wrap").width());
        $('.crumb-wrap').width($(window).width() - $(".sidebar-wrap").width());//面包屑
        $('.cont-ft').width($(window).width() - $(".sidebar-wrap").width()-31); //copyright
    });

    $('#control').click(function(){
        //设置侧边
        if($('.sidebar-wrap').css('display') == 'none'){
            $('.sidebar-wrap').css({width:189}).show();
            $(this).css({'left':190,'backgroundPosition':'left center'});
            $(this).attr({'title':'隐藏'});

            $('.Conframe').width($(window).width() - $(".sidebar-wrap").width()).css({'marginLeft':190});
        } else {
            $('.sidebar-wrap').css({width:0}).hide();
            $(this).css({'left':0,'backgroundPosition':'right center'});
            $(this).attr({'title':'显示'});
            $('.Conframe').width($(window).width() - $(".sidebar-wrap").width()).css({'marginLeft':0});
        }
        $('.crumb-wrap').width($(window).width() - $(".sidebar-wrap").width()); //面包屑
        $('.cont-ft').width($(window).width() - $(".sidebar-wrap").width()-31); //copyright
    });

    //设置导航显示隐藏
    $(".sidebar-list").find('ul.sub-menu').prev('a').click(function(){
        if($(this).next('ul.sub-menu').css('display') == 'none'){
            $(this).next('ul.sub-menu').slideDown();
        } else {
            $(this).next('ul.sub-menu').slideUp();
        }
    });

    //设置bootstrap菜单
    $('li.dropdown').mouseover(function(){
        $(this).addClass('open');
    }).mouseout(function() {
        $(this).removeClass('open');
    });

    //ajax全局函数
    var loading;
    $(document).ajaxSend(function(e, jqXHR, options){
        loading = parent.layer.load(1,{shade: [0.3,'#000']});
    }).ajaxSuccess(function(e, xhr, opts){
        parent.layer.close(loading);
    });

    //进度条，静态的，如果需要使用ajax的请使用：jquery.loadingbar.js
    /*$({property: 0}).animate(
        {
            property: 100
        },
        {
            duration:300,
            step: function() {
                var percentage = Math.round(this.property)
                $('#progress').css('width',  percentage+"%");
                if(percentage == 100) {
                    $("#progress").addClass("done");//完成，隐藏进度条
                }
            }
    });*/

    $('#progress').animate({width: '100%'},300, function() {
        $("#progress").addClass("done");//完成，隐藏进度条
    });

    //信息提示
    $('[data-toggle="tooltip"]').tooltip();

    //静态弹出框
    $('[data-toggle="popover"]').popover();

    //设置公共设置菜单
    $('#commonmenuli').on('inserted.bs.popover', function () {
       $('#commonmenuli .popover').css({'top':'-120px'});
    });

    //设置滚动条
    $(window).load(function(){
        var scrollInertiaNum;
        if(/firefox/.test(navigator.userAgent.toLowerCase())){
            scrollInertiaNum = 200;
        } else {
            scrollInertiaNum = 200;
        }
        $(".sidebar-wrap,.side_catemenu").mCustomScrollbar({
            theme:'dark',
            scrollInertia:scrollInertiaNum,
            horizontalScroll:false,
            axis:"y",
        });
        $(document.body).mCustomScrollbar({
            theme:'dark',
            scrollInertia:scrollInertiaNum,
            horizontalScroll:false,
            axis:"y"
        });
    });
});

/**
 * [selectAll 选中节点]
 * @param  {[DOM]} obj  [对象节点]
 * @return {[void]}
 */
function selectAll(obj){
    if($(obj).is(':checked')){
        $(".select_ids").each(function(index, el) {
            if($(this).prop('disabled') == false){
                $(this).prop('checked',true);
            }
        });
    } else {
        $(".select_ids").each(function(index, el) {
            if($(this).prop('disabled') == false){
               $(this).prop('checked',false);
            }
        });
    }
}

/**
 * [delall 组装删除数据的id：批量删除]
 * @param  {[string]} url [处理数据的url地址]
 * @return {[json]}       [返回json格式]
 */
function delall(url,otherData){
    var ids = [];
    $(".select_ids:checked").each(function(index, el) {
        ids.push($(this).val());
    });

    if(ids.length == 0){
        parent.layer.open({
            content:'请选择要删除的数据！',
            yes:function(index){
                parent.layer.close(index);
            },
            icon:2
        });
        return false;
    }

    //变成字符串
    ids = ids.join(',');
    //发送数据
    del(url,ids,otherData);
}

/**
 * [del 删除函数]
 * @param  {[string]} url [处理数据的url地址]
 * @param  {[string]} ids [数据的id字符串]
 * @return {[json]}       [返回json格式]
 */
function del(url,ids,otherData){
    parent.layer.confirm('确定要删除数据？',{icon:3},function(index){
        $.post(url,{id:ids,other:otherData}, function(data, textStatus, xhr) {
            //返回消息
            if(data.status){
                parent.layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                    location.reload();
                });
            } else {
                parent.layer.open({
                    content:data.info,
                    yes:function(index){
                        parent.layer.close(index);
                    },
                    icon:2
                });
                //layer.msg(data.info,{icon:2});
            }

        });
    });
}


/**
 * [sort 数据排序]
 * @param  {[string]} url [处理数据的url地址]
 * @return {[json]}       [返回json]
 */
function sort(url,otherData){

    var sorts = [];
    $(".sort").each(function(index, el){
        var str = $(this).attr('dataId') + "|" + $(this).val();
        sorts.push(str);
    });

    if(sorts.length == 0){
        parent.layer.open({
            content:'没有需要排序的数据！',
            yes:function(index){
                parent.layer.close(index);
            },
            icon:2
        });
        return false;
    }

    //变成字符串
    sorts = sorts.join(',');
    $.post(url, {sort:sorts,other:otherData}, function(data, textStatus, xhr) {
        //弹出消息
        if(data.status){
            parent.layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                location.reload();
            });
        } else {
            parent.layer.open({
                content:data.info,
                yes:function(index){
                    parent.layer.close(index);
                },
                icon:2
            });
           // layer.msg(data.info,{icon:2});
        }
    });
}


/**
 * [submitForm 表单提交ajax数据处理]
 * @param  {[url]} str    [数据发送的url地址]
 * @param  {[object]} obj [对象的DOM节点：#myFrom]
 * @return {[json]}     [返回json]
 */
function submitForm (url,obj) {
   $.post(url, $(obj).serialize(), function(data, textStatus, xhr) {
        //弹出消息
        if(data.status){
            parent.layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                if(data.url){
                    location.href = data.url;
                } else {
                    location.reload();
                }
            });
        } else {
            parent.layer.open({
                content:data.info,
                yes:function(index){
                    parent.layer.close(index);
                },
                icon:2
            });
            //layer.msg(data.info,{icon:2});
        }
    });
}

/**
 * [tips 提示]
 * @param  {[string]} words [提示文字]
 * @param  {[string]} dom   [节点]
 * @param  {[int]} seconds  [显示时间]
 */
function tips(words,dom,seconds) {
    layer.tips(words, dom,{tips:1,time:seconds});
}


/**
 * [submitForm 表单提交ajax数据处理,文件上传,enctype="multipart/form-data"]
 * @param  {[url]} str    [数据发送的url地址]
 * @param  {[object]} obj [对象的DOM节点：#myFrom]
 * @return {[json]}     [返回json]
 */
function submitAjaxForm (url,obj) {
   $(obj).ajaxSubmit({
        type:'post',
        url:url,
        data:$(obj).fixedsSerializeArray(),
        success:function(data, textStatus, jqXHR){
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
        }
   });
}


/**
 * [postUrl post传递url处理函数]
 * @return {[json]}     [返回json]
 */
function postUrl(url,sendData){
    $.post(url, {data:sendData}, function(data){
        //弹出消息
        if(data.status){
            parent.layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                if(data.url){
                    location.href = data.url;
                } else {
                    location.reload();
                }
            });
        } else {
            parent.layer.open({
                content:data.info,
                yes:function(index){
                    if(data.url){
                        location.href = data.url;
                    } else {
                        parent.layer.close(index);
                    }
                },
                icon:2
            });
            //layer.msg(data.info,{icon:2});
        }
    });
}


/**
 * [dialogContent 内容弹窗]
 * @param  {[DOM]} obj  [DOM节点]
 * @return {[type]}     [description]
 */
function dialogContent(obj,title,width,height){
    if(typeof(width) == "undefined"){
        var width = "80%";
    }
    if(typeof(height) == "undefined"){
        var height = "auto";
    }
    layer.open({
        title:title,
        type:1,
        content:$(obj),
        maxWidth:'auto',
        area:[width,height],
        maxmin:true
    })
}


/**
 * [submitFormIframe 表单提交ajax数据处理：iframe]
 * @param  {[url]} str    [数据发送的url地址]
 * @param  {[object]} obj [对象的DOM节点：#myFrom]
 * @return {[json]}     [返回json]
 */
function submitFormIframe (url,obj) {
   $.post(url, $(obj).serialize(), function(data, textStatus, xhr) {
        //弹出消息
        if(data.status){
            parent.layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                parent.document.location.reload();
                //var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                //parent.layer.close(index);
            });
        } else {
            parent.layer.open({
                content:data.info,
                yes:function(index){
                    parent.layer.close(index);
                },
                icon:2
            });
            //layer.msg(data.info,{icon:2});
        }
    });
}


/**
 * [postUrlIframe post传递url处理函数iframe]
 * @return {[json]}     [返回json]
 */
function postUrlIframe(url,sendData){
    $.post(url, {data:sendData}, function(data){
        //弹出消息
        if(data.status){
            parent.layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                parent.document.location.reload();
                //var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                //parent.layer.close(index);
            });
        } else {
            parent.layer.open({
                content:data.info,
                yes:function(index){
                    parent.layer.close(index);
                },
                icon:2
            });
            //layer.msg(data.info,{icon:2});
        }
    });
}


/**
 * [dialogIframe iframe弹窗]
 * @param  {[type]} url   [iframe地址]
 * @param  {[type]} title [标题]
 */
function dialogIframe(url,title,width,height){
    if(typeof(width) == "undefined"){
        var width = "80%";
    }
    if(typeof(height) == "undefined"){
        var height = "50%";
    }
    var url = url + "&iframe=" + window.name;
    parent.layer.open({
        title:title,
        type:2,
        area: [width,height],
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


/**
 * [tabs tab切换]
 * @param  {[string]} dom [DOM节点名称]
 * @return {[type]}     [description]
 */
function tabs(dom,thisobj){
    //tab切换
    $(thisobj).parents('ul').find('li').removeClass('current');
    $(thisobj).parent('li').addClass('current');

    //显示隐藏选项内容
    $(".insert-tab").css('display','none');
    $(dom).css('display','table');

}


/**
 * [dialogContentPer 百分比宽度高度弹窗]
 * @param  {[type]} url   [iframe地址]
 * @param  {[type]} title [标题]
 */
function dialogContentPer(obj,title,width,height){
    if(typeof(width) == "undefined"){
        var width = "80%";
    }
    if(typeof(height) == "undefined"){
        var height = "80%";
    }
    layer.open({
        maxmin:true,
        title:title,
        type:1,
        content:$(obj),
        area:[width,height],
    })
}

/**
 * [submitContentAjaxForm 表单提交ajax数据处理,文件上传,支持父级弹窗,enctype="multipart/form-data"]
 * @param  {[url]} str    [数据发送的url地址]
 * @param  {[object]} obj [对象的DOM节点：#myFrom]
 * @return {[json]}     [返回json]
 */
function submitContentAjaxForm (url,obj) {
   $(obj).ajaxSubmit({
        type:'post',
        url:url,
        data:$(obj).fixedsSerializeArray(),
        success:function(data, textStatus, jqXHR){
            //弹出消息
            if(data.status){
                parent.layer.msg(data.info,{icon:1,time:2000,shade: [0.3,'#000']},function(){
                    if(data.url){
                        location.href = data.url;
                    } else {
                        location.reload();
                    }
                });
            } else {
                 parent.layer.open({
                    content:data.info,
                    yes:function(index){
                        parent.layer.close(index);
                    },
                    icon:2
                });
                //layer.msg(data.info,{icon:2});
            }
        }
   });
}

/**
 * [changeFileName 上传文件路径显示]
 * @param  {[type]} obj [object]
 * @param  {[type]} dom [HTML DOM]
 * @return {[type]}     [description]
 */
function changeFileName(obj,dom){
    $(dom).html(obj.value);
}


/**
 * [moveall 组装移动数据的id：批量移动]
 * @param  {[string]} url [处理数据的url地址]
 * @return {[json]}       [返回json格式]
 */
function moveall(url,otherData){
    var ids = [];
    $(".select_ids:checked").each(function(index, el) {
        ids.push($(this).val());
    });

    if(ids.length == 0){
        parent.layer.open({
            content:'请选择要移动的数据！',
            yes:function(index){
                parent.layer.close(index);
            },
            icon:2
        });
        return false;
    }

    //变成字符串
    ids = ids.join(',');
    url = url + "&id="+ids;
    //发送数据
    dialogIframe(url,otherData,'80%','80%');
}

/**
 * [refreshCate 改变左侧栏目]
 * @param  {[string]} url [post远程url]
 * @param  {[object]} data [post发送的数据]
 * @param  {[object]} dom [返回处理页面的document]
 * @return {[type]}     [description]
 */
function refreshCate(url,data,dom){
    $.post(url, {data:data}, function(data, textStatus, xhr) {
        //原始的侧栏选中的id
        var tree_nod_selected = $(dom).find('#catemenutt #tt').find('li div.tree-node-selected a').attr('id');

        //移除之前的侧栏
        $(dom).find('#catemenutt #tt').remove();
        
        //新的侧栏
        var strdata = '<ul id="tt" class="easyui-tree" data-options="lines:true">' + data + '</ul>';
        $(dom).find('#catemenutt').append(strdata);
        $(dom).find('#catemenutt #tt').tree();

        //设置选中
        $(dom).find('#catemenutt #tt').find('#'+tree_nod_selected+'').parents('div.tree-node').addClass('tree-node-selected');
    });
}