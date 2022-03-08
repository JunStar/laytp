layui.define(['jquery', 'element'], function(exports) {
	"use strict";

	var MOD_NAME = 'laytpTab',
		$ = layui.jquery,
		element = layui.element;

	var laytpTab = function(opt) {
		this.option = opt;
	};

	var tabData = new Array();
	var tabDataCurrent = 0;

	laytpTab.prototype.render = function(opt) {
		//默认配置值
		var option = {
			elem: opt.elem,
			data: opt.data,
			tool: opt.tool,
			roll: opt.roll,
			index: opt.index,
			width: opt.width,
			height: opt.height,
			tabMax: opt.tabMax,
			session: opt.session ? opt.session : false,
			closeEvent: opt.closeEvent,
			success: opt.success ? opt.success : function(id) {}
		};

		// 初始化，检索 session 是否开启
		if (option.session) {
			// 替换 opt.data 数据
			if (sessionStorage.getItem(option.elem + "-laytp-tab-data") != null) {
				tabData = JSON.parse(sessionStorage.getItem(option.elem + "-laytp-tab-data"));
				option.data = JSON.parse(sessionStorage.getItem(option.elem + "-laytp-tab-data"));
				tabDataCurrent = sessionStorage.getItem(option.elem + "-laytp-tab-data-current");
				tabData.forEach(function(item, index) {
					if (item.id == tabDataCurrent) {
						option.index = index;
					}
				})
			} else {
				tabData = opt.data;
			}
		}


		var lastIndex;
		var tab = createTab(option);
		$("#" + option.elem).html(tab);
		$(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-prev").click(function() {
			rollPage("left", option);
		})
		$(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-next").click(function() {
			rollPage("right", option);
		})
		element.init();
		toolEvent(option);
		$("#" + option.elem).width(opt.width);
		$("#" + option.elem).height(opt.height);
		$("#" + option.elem).css({
			position: "relative"
		});
		closeEvent(option);

		option.success(sessionStorage.getItem(option.elem + "-laytp-tab-data-current"));

		$("body .layui-tab[lay-filter='" + option.elem + "']").on("contextmenu", "li", function(e) {

			// 获取当前元素位置
			var top = e.clientY;
			var left = e.clientX;

			var currentId = $(this).attr("lay-id");

			var menu = "<ul><li class='item' id='" + option.elem + "closeThis'>关闭当前</li><li class='item' id='" + option.elem +
				"closeOther'>关闭其他</li><li class='item' id='" + option.elem + "closeAll'>关闭所有</li></ul>";

			// 初始化
			layer.open({
				type: 1,
				title: false,
				shade: false,
				skin: 'laytp-tab-menu',
				closeBtn: false,
				area: ['100px', '108px'],
				fixed: true,
				offset: [top, left],
				content: menu, //iframe的url,
				success: function(layero, index) {
					layer.close(lastIndex);
					lastIndex = index;
					menuEvent(option,index);
					var timer;
					$(layero).on('mouseout', function() {
						timer = setTimeout(function() {
							layer.close(index);
						}, 30)
					});

					$(layero).on('mouseover', function() {
						clearTimeout(timer);
					});
					
					// 清除 item 右击
					$(layero).on('contextmenu',function(){
						return false;
					})
					
				}
			});
			return false;
		})

		return new laytpTab(option);
	};

	laytpTab.prototype.click = function(callback) {
		var elem = this.option.elem;
		var option = this.option;
		element.on('tab(' + this.option.elem + ')', function(data) {
			var id = $("#" + elem + " .layui-tab-title .layui-this").attr("lay-id");
			sessionStorage.setItem(option.elem + "-laytp-tab-data-current", id);
			callback(id);
		});
	};

	laytpTab.prototype.positionTab = function() {
		var $tabTitle = $('.layui-tab[lay-filter=' + this.option.elem + ']  .layui-tab-title');
		var autoLeft = 0;
		$tabTitle.children("li").each(function() {
			if ($(this).hasClass('layui-this')) {
				return false;
			} else {
				autoLeft += $(this).outerWidth();
			}
		});
		$tabTitle.animate({
			scrollLeft: autoLeft - $tabTitle.width() / 3
		}, 200);
	};

	laytpTab.prototype.clear = function() {
		sessionStorage.removeItem(this.option.elem + "-laytp-tab-data");
		sessionStorage.removeItem(this.option.elem + "-laytp-tab-data-current");
	};

	laytpTab.prototype.addTab = function(opt) {
		var title = '';
		if (opt.close) {
			title += '<span class="laytp-tab-active"></span><span class="able-close">' + opt.title +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>';
		} else {
			title += '<span class="laytp-tab-active"></span><span class="disable-close">' + opt.title +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>';
		}

		element.tabAdd(this.option.elem, {
			title: title,
			content: '<iframe id="' + opt.id + '" name="' + opt.id + '" data-frameid="' + opt.id + '" border="0" marginwidth="0" marginheight="0" scrolling-x="no" scrolling-y="auto" allowtransparency="yes" width="100%" height="100%" frameborder="no" src="javascript:void(0);" style="width:100%;height:100%;"></iframe>',
			id: opt.id
		});

		setTimeout("document.getElementById('"+opt.id+"').src='"+opt.url+"';",0);

		tabData.push(opt);
		sessionStorage.setItem(this.option.elem + "-laytp-tab-data", JSON.stringify(tabData));
		sessionStorage.setItem(this.option.elem + "-laytp-tab-data-current", opt.id);
		element.tabChange(this.option.elem, opt.id);
	};

	var index = 0;

	// 根据过滤 filter 标识, 删除执行选项卡
	laytpTab.prototype.delTabByElem = function(elem, id, callback) {
		var currentTab = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title [lay-id='" + id + "']");
		if (currentTab.find("span").is(".able-close")) {
			tabDelete(elem, id, callback);
		}
	};

	// 根据过滤 filter 标识, 删除当前选项卡
	laytpTab.prototype.delCurrentTabByElem = function(elem, callback) {
		var currentTab = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title .layui-this");
		if (currentTab.find("span").is(".able-close")) {
			var currentId = currentTab.attr("lay-id");
			tabDelete(elem, currentId, callback);
		}
	};

	// 通过过滤 filter 标识, 新增标签页
	laytpTab.prototype.addTabOnlyByElem = function(elem, opt, time) {
		var title = '';
		if (opt.close) {
			title += '<span class="laytp-tab-active"></span><span class="able-close">' + opt.title +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>'
		} else {
			title += '<span class="laytp-tab-active"></span><span class="disable-close">' + opt.title +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>'
		}
		if ($(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title li[lay-id]").length <= 0) {

			if (time != false && time != 0) {

				var load = '<div id="laytp-tab-loading' + index + '" class="laytp-tab-loading">' +
					'<div class="ball-loader">' +
					'<span></span><span></span><span></span><span></span>' +
					'</div>' +
					'</div>'
				$("#" + elem).find(".laytp-tab").append(load);
				var laytpLoad = $("#" + elem).find("#laytp-tab-loading" + index);
				laytpLoad.css({
					display: "block"
				});
				setTimeout(function() {
					laytpLoad.fadeOut(500);
				}, time);
				index++;
			}

			element.tabAdd(this.option.elem, {
				title: title,
				content: '<iframe id="' + opt.id + '" name="' + opt.id + '" data-frameid="' + opt.id + '" border="0" marginwidth="0" marginheight="0" scrolling-x="no" scrolling-y="auto" allowtransparency="yes" width="100%" height="100%" frameborder="no" src="javascript:void(0);" style="width:100%;height:100%;"></iframe>',
				id: opt.id
			});

			setTimeout("document.getElementById('"+opt.id+"').src='"+opt.url+"';",0);

			tabData.push(opt);
			sessionStorage.setItem(elem + "-laytp-tab-data", JSON.stringify(tabData));
		} else {
			var isData = false;
			$.each($(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title li[lay-id]"), function() {
				if ($(this).attr("lay-id") == opt.id) {
					isData = true;
				}
			})

			if (isData == false) {
				if (time != false && time != 0) {
					var load = '<div id="laytp-tab-loading' + index + '" class="laytp-tab-loading">' +
						'<div class="ball-loader">' +
						'<span></span><span></span><span></span><span></span>' +
						'</div>' +
						'</div>'

					$("#" + elem).find(".laytp-tab").append(load);
					var laytpLoad = $("#" + elem).find("#laytp-tab-loading" + index);
					laytpLoad.css({
						display: "block"
					});
					setTimeout(function() {
						laytpLoad.fadeOut(500);
					}, time);
					index++;
				}

				element.tabAdd(this.option.elem, {
					title: title,
					content: '<iframe id="' + opt.id + '" name="' + opt.id + '" data-frameid="' + opt.id + '" border="0" marginwidth="0" marginheight="0" scrolling-x="no" scrolling-y="auto" allowtransparency="yes" width="100%" height="100%" frameborder="no" src="javascript:void(0);" style="width:100%;height:100%;"></iframe>',
					id: opt.id
				});

				setTimeout("document.getElementById('"+opt.id+"').src='"+opt.url+"';",0);

				tabData.push(opt);
				sessionStorage.setItem(elem + "-laytp-tab-data", JSON.stringify(tabData));

			}
		}
		sessionStorage.setItem(elem + "-laytp-tab-data-current", opt.id);
		element.tabChange(elem, opt.id);
	};

	/** 添 加 唯 一 选 项 卡 */
	laytpTab.prototype.addTabOnly = function(opt, time) {
		var title = '';
		if (opt.close) {
			title += '<span class="laytp-tab-active"></span><span class="able-close">' + opt.title +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>';
		} else {
			title += '<span class="laytp-tab-active"></span><span class="disable-close">' + opt.title +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>';
		}
		if ($(".layui-tab[lay-filter='" + this.option.elem + "'] .layui-tab-title li[lay-id]").length <= 0) {
			if (time != false && time != 0) {
				var load = '<div id="laytp-tab-loading' + index + '" class="laytp-tab-loading">' +
					'<div class="ball-loader">' +
					'<span></span><span></span><span></span><span></span>' +
					'</div>' +
					'</div>';
				$("#" + this.option.elem).find(".laytp-tab").append(load);
				var laytpLoad = $("#" + this.option.elem).find("#laytp-tab-loading" + index);
				laytpLoad.css({
					display: "block"
				});
				setTimeout(function() {
					laytpLoad.fadeOut(500);
				}, time);
				index++;
			}

			element.tabAdd(this.option.elem, {
				title: title,
				content: '<iframe id="' + opt.id + '" name="' + opt.id + '" data-frameid="' + opt.id + '" border="0" marginwidth="0" marginheight="0" scrolling-x="no" scrolling-y="auto" allowtransparency="yes" width="100%" height="100%" frameborder="no" src="javascript:void(0);" style="width:100%;height:100%;"></iframe>',
				id: opt.id
			});

			setTimeout("document.getElementById('"+opt.id+"').src='"+opt.url+"';",0);

			tabData.push(opt);
			sessionStorage.setItem(this.option.elem + "-laytp-tab-data", JSON.stringify(tabData));
			sessionStorage.setItem(this.option.elem + "-laytp-tab-data-current", opt.id);
		} else {
			var isData = false;
			//查询当前选项卡数量
			if ($(".layui-tab[lay-filter='" + this.option.elem + "'] .layui-tab-title li[lay-id]").length >= this.option.tabMax) {
				layer.msg("最多打开" + this.option.tabMax + "个标签页", {
					icon: 2,
					time: 1000,
					shift: 6 //抖动效果
				});
				return false;
			}
			$.each($(".layui-tab[lay-filter='" + this.option.elem + "'] .layui-tab-title li[lay-id]"), function() {
				if ($(this).attr("lay-id") == opt.id) {
					isData = true;
				}
			});
			if (isData == false) {
				if (time != false && time != 0) {
					var load = '<div id="laytp-tab-loading' + index + '" class="laytp-tab-loading">' +
						'<div class="ball-loader">' +
						'<span></span><span></span><span></span><span></span>' +
						'</div>' +
						'</div>'

					$("#" + this.option.elem).find(".laytp-tab").append(load);
					var laytpLoad = $("#" + this.option.elem).find("#laytp-tab-loading" + index);
					laytpLoad.css({
						display: "block"
					});
					setTimeout(function() {
						laytpLoad.fadeOut(500);
					}, time);
					index++;
				}

				element.tabAdd(this.option.elem, {
					title: title,
					content: '<iframe id="' + opt.id + '" name="' + opt.id + '" data-frameid="' + opt.id + '" border="0" marginwidth="0" marginheight="0" scrolling-x="no" scrolling-y="auto" allowtransparency="yes" width="100%" height="100%" frameborder="no" src="javascript:void(0);" style="width:100%;height:100%;"></iframe>',
					id: opt.id
				});

				// 兼容FireFox，避免NS_BINDING_ABORTED错误
				setTimeout("document.getElementById('"+opt.id+"').src='"+opt.url+"';", 10);

				tabData.push(opt);
				sessionStorage.setItem(this.option.elem + "-laytp-tab-data", JSON.stringify(tabData));
				sessionStorage.setItem(this.option.elem + "-laytp-tab-data-current", opt.id);
			}
		}
		element.tabChange(this.option.elem, opt.id);
		sessionStorage.setItem(this.option.elem + "-laytp-tab-data-current", opt.id);
	};

	// 刷 新 指 定 的 选 项 卡
	laytpTab.prototype.refresh = function(time) {
		// 1.为了使用layui的样式加载器，tab页面所有的样式都使用layui的js统一加载了
		// 2.javascript的执行需要放在页面底部
		// 	因为js的执行需要让document元素优先加载，然后再执行js对document元素进行操作
		// 3.现在样式也放在js里面加载所以，document元素优先于css加载了，于是会造成页面跳动
		// 	index.html的加载方式是在head部分加载了需要的css样式，所以index.html的css样式是优于document加载的
		// 	而tab页面的css样式使用的是js统一加载的，所以需要延迟200毫秒，让js把样式都加载完毕之后再渲染展示document元素
		time = 200;
		// 刷 新 指 定 的 选 项 卡
		if (time != false && time != 0) {
			var load = '<div id="laytp-tab-loading' + index + '" class="laytp-tab-loading">' +
				'<div class="ball-loader">' +
				'<span></span><span></span><span></span><span></span>' +
				'</div>' +
				'</div>';

			var elem = this.option.elem;
			$("#" + this.option.elem).find(".laytp-tab").append(load);
			var laytpLoad = $("#" + this.option.elem).find("#laytp-tab-loading" + index);
			laytpLoad.css({
				display: "block"
			});
			index++;
			setTimeout(function() {
				laytpLoad.fadeOut(500, function() {
					laytpLoad.remove();
				});
			}, time);
			$(".layui-tab[lay-filter='" + elem + "'] .layui-tab-content .layui-show").find("iframe")[0].contentWindow
				.location.reload(true);
		} else {
			$(".layui-tab[lay-filter='" + this.option.elem + "'] .layui-tab-content .layui-show").find("iframe")[0].contentWindow
				.location.reload(true);
		}
	};


	function tabDelete(elem, id, callback, option) {

		//根据 elem id 来删除指定的 layui title li
		var tabTitle = $(".layui-tab[lay-filter='" + elem + "']").find(".layui-tab-title");

		// 删除指定 id 的 title
		var removeTab = tabTitle.find("li[lay-id='" + id + "']");
		var nextNode = removeTab.next("li");
		if (!removeTab.hasClass("layui-this")) {
			removeTab.remove();
			var tabContent = $(".layui-tab[lay-filter='" + elem + "']").find("iframe[id='" + id + "']").parent();
			tabContent.remove();

			tabData = JSON.parse(sessionStorage.getItem(elem + "-laytp-tab-data"));
			tabDataCurrent = sessionStorage.getItem(elem + "-laytp-tab-data-current");
			tabData = tabData.filter(function(item) {
				return item.id != id;
			})
			sessionStorage.setItem(elem + "-laytp-tab-data", JSON.stringify(tabData));
			return false;
		}

		var currId;
		if (nextNode.length) {
			nextNode.addClass("layui-this");
			currId = nextNode.attr("lay-id");
			$("#" + elem + " [id='" + currId + "']").parent().addClass("layui-show");
		} else {
			var prevNode = removeTab.prev("li");
			prevNode.addClass("layui-this");
			currId = prevNode.attr("lay-id");
			$("#" + elem + " [id='" + currId + "']").parent().addClass("layui-show");
		}
		callback(currId);
		tabData = JSON.parse(sessionStorage.getItem(elem + "-laytp-tab-data"));
		tabDataCurrent = sessionStorage.getItem(elem + "-laytp-tab-data-current");
		tabData = tabData.filter(function(item) {
			return item.id != id;
		})
		sessionStorage.setItem(elem + "-laytp-tab-data", JSON.stringify(tabData));
		sessionStorage.setItem(elem + "-laytp-tab-data-current", currId);

		removeTab.remove();
		// 删除 content
		var tabContent = $(".layui-tab[lay-filter='" + elem + "']").find("iframe[id='" + id + "']").parent();
		// 删除
		tabContent.remove();
	}

	function createTab(option) {
		var type = "";
		var types = option.type + " ";
		if (option.roll == true) {
			type = "layui-tab-roll";
		}
		if (option.tool != false) {
			type = "layui-tab-tool";
		}
		if (option.roll == true && option.tool != false) {
			type = "layui-tab-rollTool";
		}
		var tab = '<div class="laytp-tab ' + types + type + ' layui-tab" lay-filter="' + option.elem +
			'" lay-allowClose="true">';
		var title = '<ul class="layui-tab-title">';
		var content = '<div class="layui-tab-content">';
		var control =
			'<div class="layui-tab-control"><li class="layui-tab-prev layui-icon layui-icon-left"></li><li class="layui-tab-next layui-icon layui-icon-right"></li><li class="layui-tab-tool layui-icon layui-icon-down"><ul class="layui-nav" lay-filter=""><li class="layui-nav-item"><a href="javascript:;"></a><dl class="layui-nav-child">';

		// 处 理 选 项 卡 头 部
		var index = 0;
		$.each(option.data, function(i, item) {
			var TitleItem = '';
			if (option.index == index) {
				TitleItem += '<li lay-id="' + item.id + '" class="layui-this"><span class="laytp-tab-active"></span>';
			} else {
				TitleItem += '<li lay-id="' + item.id + '" ><span class="laytp-tab-active"></span>';
			}

			if (item.close) {
				// 当 前 选 项 卡 可 以 关 闭
				TitleItem += '<span class="able-close">' + item.title + '</span>';
			} else {
				// 当 前 选 项 卡 不 允 许 关 闭
				TitleItem += '<span class="disable-close">' + item.title + '</span>';
			}
			TitleItem += '<i class="layui-icon layui-unselect layui-tab-close">ဆ</i></li>';
			title += TitleItem;
			if (option.index == index) {

				// 处 理 显 示 内 容
				content += '<div class="layui-show layui-tab-item"><iframe id="' + item.id + '" data-frameid="' + item.id +
					'"  src="' + item.url +
					'" frameborder="no" border="0" marginwidth="0" marginheight="0" style="width: 100%;height: 100%;"></iframe></div>'
			} else {
				// 处 理 显 示 内 容
				content += '<div class="layui-tab-item"><iframe id="' + item.id + '" data-frameid="' + item.id + '"  src="' +
					item.url +
					'" frameborder="no" border="0" marginwidth="0" marginheight="0" style="width: 100%;height: 100%;"></iframe></div>'
			}
			index++;
		});

		title += '</ul>';
		content += '</div>';
		control += '<dd id="closeThis"><a href="javascript:void(0);">关 闭 当 前</a></dd>'
		control += '<dd id="closeOther"><a href="javascript:void(0);">关 闭 其 他</a></dd>'
		control += '<dd id="closeAll"><a href="javascript:void(0);">关 闭 全 部</a></dd>'
		control += '</dl></li></ul></li></div>';

		tab += title;
		tab += control;
		tab += content;
		tab += '</div>';
		tab += '';
		return tab;
	}

	function rollPage(d, option) {
		var $tabTitle = $('#' + option.elem + '  .layui-tab-title');
		var left = $tabTitle.scrollLeft();
		if ('left' === d) {
			$tabTitle.animate({
				scrollLeft: left - 450
			}, 200);
		} else {
			$tabTitle.animate({
				scrollLeft: left + 450
			}, 200);
		}
	}

	function closeEvent(option) {
		$(".layui-tab[lay-filter='" + option.elem + "']").on("click", ".layui-tab-close", function() {
			var layid = $(this).parent().attr("lay-id");
			tabDelete(option.elem, layid, option.closeEvent, option);
		})
	}

	function menuEvent(option,index) {

		$("#" + option.elem + "closeThis").click(function() {
			var currentTab = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title .layui-this");
		
			if (currentTab.find("span").is(".able-close")) {
				var currentId = currentTab.attr("lay-id");
				tabDelete(option.elem, currentId, option.closeEvent, option);
			} else {
				layer.msg("当前页面不允许关闭", {
					icon: 3,
					time: 800
				})
			}
			layer.close(index);
		});

		$("#" + option.elem + "closeOther").click(function() {
			var currentId = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title .layui-this").attr("lay-id");
			var tabtitle = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title li");
			$.each(tabtitle, function(i) {
				if ($(this).attr("lay-id") != currentId) {
					if ($(this).find("span").is(".able-close")) {
						tabDelete(option.elem, $(this).attr("lay-id"), option.closeEvent, option);
					}
				}
			})
			layer.close(index);
		});

		$("#" + option.elem + "closeAll").click(function() {
			var currentId = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title .layui-this").attr("lay-id");
			var tabtitle = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title li");
			$.each(tabtitle, function(i) {
				if ($(this).find("span").is(".able-close")) {
					tabDelete(option.elem, $(this).attr("lay-id"), option.closeEvent, option);
				}
			});
			layer.close(index);
		});
	}


	function toolEvent(option) {

		$("body .layui-tab[lay-filter='" + option.elem + "']").on("click", "#closeThis", function() {
			var currentTab = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title .layui-this");
			if (currentTab.find("span").is(".able-close")) {
				var currentId = currentTab.attr("lay-id");
				tabDelete(option.elem, currentId, option.closeEvent, option);
			} else {
				layer.msg("当前页面不允许关闭", {
					icon: 3,
					time: 800
				})
			}
		})

		$("body .layui-tab[lay-filter='" + option.elem + "']").on("click", "#closeOther", function() {
			var currentId = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title .layui-this").attr("lay-id");
			var tabtitle = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title li");
			$.each(tabtitle, function(i) {
				if ($(this).attr("lay-id") != currentId) {
					if ($(this).find("span").is(".able-close")) {
						tabDelete(option.elem, $(this).attr("lay-id"), option.closeEvent, option);
					}
				}
			})
		})

		$("body .layui-tab[lay-filter='" + option.elem + "']").on("click", "#closeAll", function() {
			var currentId = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title .layui-this").attr("lay-id");
			var tabtitle = $(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title li");
			$.each(tabtitle, function(i) {
				if ($(this).find("span").is(".able-close")) {
					tabDelete(option.elem, $(this).attr("lay-id"), option.closeEvent, option);
				}
			})
		})
	}

	exports(MOD_NAME, new laytpTab());
});
