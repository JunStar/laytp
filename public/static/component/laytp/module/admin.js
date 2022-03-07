layui.define([
	'message',
	'table',
	'jquery',
	'element',
	'yaml',
	'form',
	'laytpTab',
	'menu',
	'frame',
	'theme',
	'convert'
	],
	function(exports) {
		"use strict";

		var $ = layui.jquery,
			form = layui.form,
			element = layui.element,
			yaml = layui.yaml,
			laytpTab = layui.laytpTab,
			convert = layui.convert,
			laytpMenu = layui.menu,
			laytpFrame = layui.frame,
			laytpTheme = layui.theme,
			message = layui.message;

		var bodyFrame;
		var sideMenu;
		var bodyTab;
		var config;
		var logout = function() {};
		var msgInstance;

		var body = $('body');

		var laytpAdmin = new function() {

			// 默认配置
			var configType = 'yml';
			var configPath = 'laytp.config.yml';

			this.setConfigPath = function(path) {
				configPath = path;
			};

			this.setConfigType = function(type) {
				configType = type;
			};

			this.setAvatar = function(url, username) {
				// var image = new Image();
				// if(localStorage.getItem("staticDomain") && !url){
				// 	image.src = localStorage.getItem("staticDomain") + "/admin/images/avatar.jpg";
				// }else{
				// 	image.src = url || "/static/admin/images/avatar.jpg";
				// }
				// image.onload = function() {
				// 	$(".layui-nav-img").attr("src", convert.imageToBase64(image));
				// };
				var src = '';
				if(facade.getStaticDomain() && !url){
					src = facade.getStaticDomain() + "/admin/images/avatar.jpg";
				}else{
					src = url || "/static/admin/images/avatar.jpg";
				}
				$(".layui-nav-img").attr("src", src);
				$(".layui-nav-img").parent().append(username);
			};

			this.render = function(initConfig) {
				if (initConfig !== undefined) {
					applyConfig(initConfig);
				} else {
					applyConfig(laytpAdmin.readConfig());
				}
				window.config = config;
				window.bodyTab = bodyTab;
			};

			this.readConfig = function() {
				if (configType === "yml") {
					return yaml.load(configPath);
				} else {
					var data;
					$.ajax({
						url: configPath,
						type: 'get',
						dataType: 'json',
						async: false,
						success: function(result) {
							data = result;
						}
					});
					return data;
				}
			};

			this.messageRender = function(option) {
				var option = {
					elem: '.message',
					url: option.header.message,
					height: '250px'
				};
				msgInstance = message.render(option);
			};

			this.logoRender = function(param) {
				if(facade.getStaticDomain()){
					$(".layui-logo .logo").attr("src", facade.getStaticDomain() + param.logo.image);
				}else{
					$(".layui-logo .logo").attr("src", param.logo.image);
				}

				$(".layui-logo .title").html(param.logo.title);
			};

			this.menuRender = function(param) {
				sideMenu = laytpMenu.render({
					elem: 'sideMenu',
					async: param.menu.async !== undefined ? param.menu.async : true,
					theme: "dark-theme",
					height: '100%',
					method: param.menu.method,
					control: param.menu.control ? 'control' : false, // control
					defaultMenu: 0,
					accordion: param.menu.accordion,
					url: param.menu.data,
					data: param.menu.data, //async为false时，传入菜单数组
					parseData: function(res){
						if(param.isSearch){
							var result = {
								"id" : 0,
								"is_menu" : 1,
								"is_show" : 1,
								"title" : "搜索结果",
								"type" : 0,
								"children" : res,
								"href" : "",
								"icon" : ""
							};
							var resArr = [];
							resArr.push(result);
							return resArr;
						}else{
							window.menuData = [];
							if(res.data){
								if(res.code === 10401){
									layui.popup.failure(res["msg"],function(){
                                    	facade.redirect("/admin/login.html");
									});
								}else{
									window.menuData = laytpAdmin.parseData(res.data);
								}
							}else{
								window.menuData = laytpAdmin.parseData(res);
							}
							return window.menuData;
						}

					},
					change: function() {
						compatible();
					},
					done: function() {
						sideMenu.selectItem(param.menu.select);
						sideMenu.selectItem(sessionStorage.getItem("content-laytp-tab-data-current"));
					}
				});
			};

			this.parseData = function(data){
				$.each(data, function(i, item) {
					var tempItem = {};
					tempItem.id = item.id;
					tempItem.href = item.href;
					tempItem.icon = item.icon;
					tempItem.type = 0;
					tempItem.title = item.name;
					if(typeof item.children != null && typeof item.children !== "undefined" && item.children.length > 0){
						tempItem.children = laytpAdmin.parseData(item.children);
					}else{
						tempItem.type = 1;
					}
					data[i] = tempItem;
				});
				return data;
			};

			this.bodyRender = function(param) {
				body.on("click", ".refresh", function() {
					var refreshA = $(".refresh a");
					refreshA.removeClass("layui-icon-refresh-1");
					refreshA.addClass("layui-anim");
					refreshA.addClass("layui-anim-rotate");
					refreshA.addClass("layui-anim-loop");
					refreshA.addClass("layui-icon-loading");
					if (param.tab.muiltTab) bodyTab.refresh(400);
					else bodyFrame.refresh(400);
					setTimeout(function() {
						refreshA.addClass("layui-icon-refresh-1");
						refreshA.removeClass("layui-anim");
						refreshA.removeClass("layui-anim-rotate");
						refreshA.removeClass("layui-anim-loop");
						refreshA.removeClass("layui-icon-loading");
					}, 600);
				});
				if (param.tab.muiltTab) {
					bodyTab = laytpTab.render({
						elem: 'content',
						roll: true,
						tool: true,
						width: '100%',
						height: '100%',
						session: param.tab.session,
						index: 0,
						tabMax: param.tab.tabMax,
						closeEvent: function(id) {
							sideMenu.selectItem(id);
						},
						data: [{
							id: param.tab.index.id,
							url: param.tab.index.href,
							title: param.tab.index.title,
							close: false
						}],
						success: function(id) {
							if (param.tab.session) {
								setTimeout(function() {
									sideMenu.selectItem(id);
									bodyTab.positionTab();
								}, 500);
							}
						}
					});
					bodyTab.click(function(id) {
						if (!param.tab.keepState) {
							bodyTab.refresh(false);
						}
						bodyTab.positionTab();
						sideMenu.selectItem(id);
					});

					sideMenu.click(function(dom, data) {
						bodyTab.addTabOnly({
							id: data.menuId,
							title: data.menuTitle,
							url: data.menuUrl,
							icon: data.menuIcon,
							close: true
						}, 300);

						compatible();

					});
				} else {
					bodyFrame = laytpFrame.render({
						elem: 'content',
						title: '控制面板',
						url: param.tab.index.href,
						width: '100%',
						height: '100%'
					});
					window.bodyFrame = bodyFrame;

					sideMenu.click(function(dom, data) {
						bodyFrame.changePage(data.menuUrl, data.menuPath, true);
						compatible();
					});
				}
			};

			this.keepLoad = function(param) {
				compatible();
				setTimeout(function() {
					$(".loader-main").fadeOut(200);
				}, param.other.keepLoad)
			};

			this.themeRender = function(option) {
				if (option.theme.allowCustom === false) {
					$(".setting").remove();
				}
				var colorId = localStorage.getItem("theme-color");
				var currentColor = getColorById(colorId);
				localStorage.setItem("theme-color", currentColor.id);
				localStorage.setItem("theme-color-context", currentColor.color);
				laytpTheme.changeTheme(window, option.other.autoHead);
				var menu = localStorage.getItem("theme-menu");
				if (menu == null) {
					menu = option.theme.defaultMenu;
				} else {
					if (option.theme.allowCustom === false) {
						menu = option.theme.defaultMenu;
					}
				}
				localStorage.setItem("theme-menu", menu);
				this.menuSkin(menu);
			}

			this.menuSkin = function(theme) {
				var laytpAdmin = $(".laytp-admin");
				laytpAdmin.removeClass("light-theme");
				laytpAdmin.removeClass("dark-theme");
				laytpAdmin.addClass(theme);
			}

			this.logout = function(callback) {
				logout = callback;
			}

			this.message = function(callback) {
				if (callback != null) {
					msgInstance.click(callback);
				} else {
					msgInstance.click(messageTip);
				}
			}

			this.jump = function(id, title, url) {
				if (config.tab.muiltTab) {
					bodyTab.addTabOnly({
						id: id,
						title: title,
						url: url,
						icon: null,
						close: true
					}, 300);
				} else {
					sideMenu.selectItem(id);
					bodyFrame.changePage(url, title, true);
				}
			}
		};

		var messageTip = function(id, title, context, form) {
			layer.open({
				type: 1,
				title: '消息', //标题
				area: ['390px', '330px'], //宽高
				shade: 0.4, //遮罩透明度
				content: "<div style='background-color:whitesmoke;'><div class='layui-card'><div class='layui-card-body'>来源 : &nbsp; " +
					form + "</div><div class='layui-card-header' >标题 : &nbsp; " + title +
					"</div><div class='layui-card-body' >内容 : &nbsp; " + context + "</div></div></div>", //支持获取DOM元素
				btn: ['确认'], //按钮组
				scrollbar: false, //屏蔽浏览器滚动条
				yes: function(index) { //layer.msg('yes');    //点击确定回调
					layer.close(index);
					showToast();
				}
			});
		}

		function collaspe() {
			sideMenu.collaspe();
			var admin = $(".laytp-admin");
			var left = $(".layui-icon-spread-left")
			var right = $(".layui-icon-shrink-right")
			if (admin.is(".laytp-mini")) {
				left.addClass("layui-icon-shrink-right")
				left.removeClass("layui-icon-spread-left")
				admin.removeClass("laytp-mini");
			} else {
				right.addClass("layui-icon-spread-left")
				right.removeClass("layui-icon-shrink-right")
				admin.addClass("laytp-mini");
			}
		}

		body.on("click", ".logout", function() {
			// 回调
			var result = logout();

			if (result) {
				// 清空缓存
				bodyTab.clear();
			}
		})

		body.on("click", ".collaspe,.laytp-cover", function() {
			collaspe();
		});

		body.on("click", ".fullScreen", function() {
			if ($(this).hasClass("layui-icon-screen-restore")) {
				screenFun(2).then(function() {
					$(".fullScreen").eq(0).removeClass("layui-icon-screen-restore");
				});
			} else {
				screenFun(1).then(function() {
					$(".fullScreen").eq(0).addClass("layui-icon-screen-restore");
				});
			}
		});

		body.on("click", '[user-menu-id]', function() {
			if (config.tab.muiltTab) {
				bodyTab.addTabOnly({
					id: $(this).attr("user-menu-id"),
					title: $(this).attr("user-menu-title"),
					url: $(this).attr("user-menu-url"),
					icon: "",
					close: true
				}, 300);
			} else {
				bodyFrame.changePage($(this).attr("user-menu-url"), "", true);
			}
		});

		body.on("click", ".setting", function() {

			var bgColorHtml =
				'<li class="layui-this" data-select-bgcolor="dark-theme" >' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: white;"></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: #28333E;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			bgColorHtml +=
				'<li  data-select-bgcolor="light-theme" >' +
				'<a href="javascript:;" data-skin="skin-blue" style="" class="clearfix full-opacity-hover">' +
				'<div><span style="display:block; width: 20%; float: left; height: 12px; background: white;"></span><span style="display:block; width: 80%; float: left; height: 12px; background: white;"></span></div>' +
				'<div><span style="display:block; width: 20%; float: left; height: 40px; background: white;"></span><span style="display:block; width: 80%; float: left; height: 40px; background: #f4f5f7;"></span></div>' +
				'</a>' +
				'</li>';

			var html =
				'<div class="laytpone-color">\n' +
				'<div class="color-title">整体风格</div>\n' +
				'<div class="color-content">\n' +
				'<ul>\n' + bgColorHtml + '</ul>\n' +
				'</div>\n' +
				'</div>';

			layer.open({
				type: 1,
				offset: 'r',
				area: ['320px', '100%'],
				title: false,
				shade: 0.1,
				closeBtn: 0,
				shadeClose: false,
				anim: -1,
				skin: 'layer-anim-right',
				move: false,
				content: html + buildColorHtml() + buildLinkHtml() + bottomTool(),
				success: function(layero, index) {

					var color = localStorage.getItem("theme-color");
					var menu = localStorage.getItem("theme-menu");

					if (color !== "null") {
						$(".select-color-item").removeClass("layui-icon").removeClass("layui-icon-ok");
						$("*[color-id='" + color + "']").addClass("layui-icon").addClass("layui-icon-ok");
					}
					if (menu !== "null") {
						$("*[data-select-bgcolor]").removeClass("layui-this");
						$("[data-select-bgcolor='" + menu + "']").addClass("layui-this");
					}
					$(document).off("click", '#layui-layer-shade' + index).on("click", '#layui-layer-shade' + index, function () {
					// $('#layui-layer-shade' + index).click(function() {
						var $layero = $('#layui-layer' + index);
						$layero.animate({
							left: $layero.offset().left + $layero.width()
						}, 200, function() {
							layer.close(index);
						});
					});

					$('#closeTheme').click(function() {
						var $layero = $('#layui-layer' + index);
						$layero.animate({
							left: $layero.offset().left + $layero.width()
						}, 200, function() {
							layer.close(index);
						});
					})
				}
			});
		});

		function bottomTool() {
			return "<button id='closeTheme' style='position: absolute;bottom: 20px;left: 20px;' class='laytp-btn'>关闭</button>"
		}

		body.on('click', '[data-select-bgcolor]', function() {
			var theme = $(this).attr('data-select-bgcolor');
			$('[data-select-bgcolor]').removeClass("layui-this");
			$(this).addClass("layui-this");
			localStorage.setItem("theme-menu", theme);
			laytpAdmin.menuSkin(theme);
		});

		body.on('click', '.select-color-item', function() {
			$(".select-color-item").removeClass("layui-icon").removeClass("layui-icon-ok");
			$(this).addClass("layui-icon").addClass("layui-icon-ok");
			var colorId = $(".select-color-item.layui-icon-ok").attr("color-id");
			var currentColor = getColorById(colorId);
			localStorage.setItem("theme-color", currentColor.id);
			localStorage.setItem("theme-color-context", currentColor.color);
			laytpTheme.changeTheme(window, config.other.autoHead);
		});

		function applyConfig(param) {
			config = param;
			laytpAdmin.logoRender(param);
			laytpAdmin.menuRender(param);
			laytpAdmin.bodyRender(param);
			laytpAdmin.themeRender(param);
			laytpAdmin.keepLoad(param);
			if (param.header.message !== false) {
				laytpAdmin.messageRender(param);
			}
		}

		function getColorById(id) {
			var color;
			var flag = false;
			$.each(config.colors, function(i, value) {
				if (value.id === id) {
					color = value;
					flag = true;
				}
			})
			if (flag === false || config.theme.allowCustom === false) {
				$.each(config.colors, function(i, value) {
					if (value.id === config.theme.defaultColor) {
						color = value;
					}
				})
			}
			return color;
		}

		function buildLinkHtml() {
			var links = "";
			$.each(config.links, function(i, value) {
				links += '<a class="more-menu-item" href="' + value.href + '">' +
					'<i class="' + value.icon + '" style="font-size: 19px;"></i> ' + value.title +
					'</a>'
			})
			return '<div class="more-menu-list">' + links + '</div>';
		}

		function buildColorHtml() {
			var colors = "";
			$.each(config.colors, function(i, value) {
				colors += "<span class='select-color-item' color-id='" + value.id + "' style='background-color:" + value.color +
					";'></span>";
			})
			return "<div class='select-color'><div class='select-color-title'>主题配色</div><div class='select-color-content'>" +
				colors + "</div></div>"
		}

		function compatible() {
			if ($(window).width() <= 768) {
				collaspe()
			}
		}

		function screenFun(num) {
			num = num || 1;
			num = num * 1;
			var docElm = document.documentElement;
			switch (num) {
				case 1:
					if (docElm.requestFullscreen) {
						docElm.requestFullscreen();
					} else if (docElm.mozRequestFullScreen) {
						docElm.mozRequestFullScreen();
					} else if (docElm.webkitRequestFullScreen) {
						docElm.webkitRequestFullScreen();
					} else if (docElm.msRequestFullscreen) {
						docElm.msRequestFullscreen();
					}
					break;
				case 2:
					if (document.exitFullscreen) {
						document.exitFullscreen();
					} else if (document.mozCancelFullScreen) {
						document.mozCancelFullScreen();
					} else if (document.webkitCancelFullScreen) {
						document.webkitCancelFullScreen();
					} else if (document.msExitFullscreen) {
						document.msExitFullscreen();
					}
					break;
			}
			return new Promise(function(res, rej) {
				res("返回值");
			});
		}

		function isFullscreen() {
			return document.fullscreenElement ||
				document.msFullscreenElement ||
				document.mozFullScreenElement ||
				document.webkitFullscreenElement || false;
		}

		window.onresize = function() {
			if (!isFullscreen()) {
				$(".fullScreen").eq(0).removeClass("layui-icon-screen-restore");
			}
		}

		exports('admin', laytpAdmin);
	})
