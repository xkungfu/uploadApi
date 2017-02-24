document.domain = document.domain.split('.').slice(-2).join('.');
$(function () {

    //展示iframe
    $('.uploadIco img').on('click', function (e) {
        layer.open({
            type: 2,
            title: '图片上传',
            area: ['700px', '500px'],
            fix: true, //不固定
            maxmin: true,
            offset: '100px',
            content: 'http://**.com/addFile.html#pic',
            btn: ['提交'],
            yes: function (index, layerdom) {
                var returnValue = layer.getChildFrame('body', index);
                alltxt = returnValue.find('.imgurl');
                if (alltxt.eq(0).val()) {
                    funDefine(alltxt, e.target); //自定义函数处理返回值，this表示当前dom对象
                } else {
                    layer.msg('请上传图片');
                }
                layer.close(index); //关闭按钮
            }
        });
    });


});




//删除操作
function delUploadImg(obj) {
    $.ajax({
        type: "get",
        url: "http://**.com/server/delPic.php",
        dataType: "jsonp",
        data: {picimg: $(obj).parent().find(':input').val(), domain: window.location.href},
        jsonp: "callback",
        jsonpCallback: "success_jsonpCallback",
        success: function (json) {
            if (json.result) {
                layer.msg('删除成功');
                $(obj).parent().remove();    //删除父级元素
            } else {
                layer.msg('删除失败，请重试！');
            }

        },
        error: function () {
            layer.msg("请重试！");
        }

    });

}   