layui.config({
    //dir: '/static/layui/' //layui.js 所在路径（注意，如果是script单独引入layui.js，无需设定该参数。如果是使用requireJS等方式加载layui，则需要此参数），一般情况下可以无视
    version: false //一般用于更新模块缓存，默认不开启。设为true即让浏览器不缓存。也可以设为一个固定的值，如：201610
    ,debug: false //用于开启调试模式，默认false，如果设为true，则JS模块的节点会保留在页面
    ,base: '/static/library/layui/extends/' //设定扩展的Layui模块的所在目录，一般用于外部模块扩展
}).extend({
    layTp : 'layTp'
});