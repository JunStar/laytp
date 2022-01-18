window.rootPath = (function(src) {
    src = document.scripts[document.scripts.length - 1].src;
    return src.substring(0, src.lastIndexOf("/") + 1);
})();

var base = '', dir = '';
if(localStorage.getItem("staticDomain")){
    dir = localStorage.getItem("staticDomain") + "/";
    base = localStorage.getItem("staticDomain") + "/component/laytp/module/";

}else{
    dir = "";
    base = rootPath + "module/";
}

layui.config({
    dir: dir, // 此值影响layui.addcss方法
    base: base, //设定扩展的Layui模块的所在目录，一般用于外部模块扩展
    version: localStorage.getItem("version") //js和css文件统一版本号
}).use([
    'theme',
    'facade',
], function () {
    // 加载css样式
    var cssPath = [
        // layui.css要优先于layui.js进行加载，否则有些样式会出错，所以在每个加载layui.js的前面去加载layui.css
        // "/component/layui/css/layui.css",
        // js模块的css
        "/component/laytp/font/iconfont.css",
        "/component/laytp/font/font-awesome-4.7.0/css/font-awesome.css",
        "/component/laytp/css/module/dtree/font/dtreefont.css",
        "/component/laytp/css/module/dtree/dtree.css",
        "/component/laytp/css/module/iconPicker.css",
        "/component/laytp/css/module/treetable.css",
        "/component/laytp/css/module/message.css",
        "/component/laytp/css/module/cropper.css",
        "/component/laytp/css/module/loading.css",
        "/component/laytp/css/module/topBar.css",
        "/component/laytp/css/module/select.css",
        "/component/laytp/css/module/layout.css",
        "/component/laytp/css/module/laytpUpload.css",
        "/component/laytp/css/module/notice.css",
        "/component/laytp/css/module/button.css",
        "/component/laytp/css/module/table.css",
        "/component/laytp/css/module/frame.css",
        "/component/laytp/css/module/icon.css",
        "/component/laytp/css/module/layer.css",
        "/component/laytp/css/module/menu.css",
        "/component/laytp/css/module/form.css",
        "/component/laytp/css/module/link.css",
        "/component/laytp/css/module/code.css",
        "/component/laytp/css/module/step.css",
        "/component/laytp/css/module/card.css",
        "/component/laytp/css/module/tab.css",
        "/component/laytp/css/module/tag.css",
        "/component/laytp/css/module/verticalCard.css",
        "/component/laytp/css/module/prettify.css",
        "/component/laytp/css/module/operationDropdown.css",

        // 静态页面html独立的css
        // "admin/css/admin.css", // index.html布局页面，在这里来加载它的css页面样式会错乱，放在index.html页面顶部加载需要的css
        // "admin/css/load.css", // index.html布局页面，在这里来加载它的css页面样式会错乱，放在index.html页面顶部加载需要的css
        "/admin/css/other/console.css", // 控制面板的css
    ];

    for(let k in cssPath){
        if(localStorage.getItem("staticDomain")){
            cssPath[k] = ".." + cssPath[k];
            layui.addcss(cssPath[k] + "?v=" + localStorage.getItem("version"));
        }else{
            cssPath[k] = "/static" + cssPath[k];
            layui.link(cssPath[k] + "?v=" + localStorage.getItem("version"));
        }
    }

    // 设置主题
    layui.theme.changeTheme(window, false);
});