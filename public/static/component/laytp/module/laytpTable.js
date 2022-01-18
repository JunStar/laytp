/**
 * 数据表格处理
 * @version: 1.0
 * @Author:  JunStar
 * @Date:    2021-9-8 23:56:21
 * @Last Modified by:   JunStar
 * @Last Modified time: 2021-09-22 17:19:12
 */
layui.define([

],function (exports) {
    const MOD_NAME = "laytpTable";
    let $ = layui.$;

    let laytpTable = {
        /**
         * 数据表格渲染完成调用的方法
         *  主要工作，让layui-table-body的最大高度最多就是当前可见屏幕，超过了当前可见高度，就出现滚动条
         *  增加参数reduceHeight的设置，是当底部有浮动的层时，需要把底部浮动层的高度也减去
         *  弹窗展示数据表格时，底部可能需要加上操作按钮，reduceHeight的值设置上浮动层高度即可
         * @param reduceHeight int 减少的高度，单位px
         */
        done : function(reduceHeight){
            if(!reduceHeight){
                reduceHeight = 0;
            }
            // 获取屏幕可见高度
            var windowHeight = $(window).height();
            var maxHeight = parseInt(windowHeight) - (190 + reduceHeight);
            // 设置.layui-table-body的最大高度和y轴滚动条自动出现
            // 为了防止fixed="left" 固定层也出现滚动条，增加上.layui-table-main类的限制
            $(".layui-table-body.layui-table-main").css("max-height", maxHeight + 'px');
            $(".layui-table-body.layui-table-main").css("overflow-y", "auto");
        }
    };

    //输出模块
    exports(MOD_NAME, laytpTable);
});