layui.define(['jquery', 'element'], function(exports) {
	"use strict";

	var MOD_NAME = 'context',
		$ = layui.jquery,
		element = layui.element;

	var context = new function() {
		
		this.put = function(key,value){
			if(typeof value === "object"){
				value = JSON.stringify(value);
			}
			localStorage.setItem(key,value);
		};

		this.get = function(key){
			var str = localStorage.getItem(key);
			if (typeof str === "string") {
				try {
					var obj = JSON.parse(str);
					if (typeof obj == 'object' && obj) {
                        return obj;
					} else {
						return str;
					}
				} catch (e) {
					return str;
				}
			}
		};

		this.del = function(key){
            localStorage.removeItem(key);
		}
	};
	exports(MOD_NAME, context);
});
