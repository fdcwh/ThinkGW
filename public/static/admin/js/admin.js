;!function (win) {
	"use strict";
	var Hadmin = function () {
		this.v = '1.0.0'; //版本号
	}
	
	Hadmin.prototype.init = function () {
		console.log('init')
	};
	
	/**
	 * [end 执行结束要做的]
	 * @return {[type]} [description]
	 */
	Hadmin.prototype.end = function () {
		
		console.log('end')
	};
	
	/**
	 * [open 打开弹出层]
	 * @param  {[type]}  title [弹出层标题]
	 * @param  {string}  url   [弹出层地址]
	 * @param  {[type]}  w     [宽]
	 * @param  {[type]}  h     [高]
	 * @param  {Boolean} full  [全屏]
	 * @return {[type]}        [description]
	 */
	Hadmin.prototype.open = function (title, url, w, h, full) {
		if (title == null || title === '') {
			var title = false;
		}
		if (url == null || url === '') {
			var url = "404.html";
		}
		if (w == null || w === '') {
			var w = ($(window).width() * 0.8);
		}
		if (h == null || h === '') {
			var h = ($(window).height() * 0.9);
		}
		console.log(url);
		
		if (url.indexOf('?') != -1) {
			url += '&_dialog=1';
		} else {
			url += '?_dialog=1';
		}
		var index = layer.open({
			type      : 2,
			area      : [w + 'px', h + 'px'],
			fix       : false,
			maxmin    : true,
			shadeClose: true,
			shade     : 0.4,
			title     : title,
			content   : url
		});
		if (full) {
			layer.full(index);
		}
	}
	
	/**
	 * [close 关闭弹出层]
	 * @return {[type]} [description]
	 */
	Hadmin.prototype.close = function () {
		var index = parent.layer.getFrameIndex(window.name);
		parent.layer.close(index);
	};
	
	/**
	 * [close 关闭弹出层父窗口关闭]
	 * @return {[type]} [description]
	 */
	Hadmin.prototype.fatherReload = function () {
		parent.location.reload();
	};
	
	Hadmin.prototype.success = function (content, callbackFunction) {
		let options = {icon: 1, time: 1000};
		layer.msg(content, options, callbackFunction);
	}
	
	Hadmin.prototype.error = function (content, callbackFunction) {
		let options = {icon: 2, time: 1000};
		layer.msg(content, options, callbackFunction);
	}
	
	Hadmin.prototype.tips = function (content, callbackFunction) {
		let options = {icon: 3, time: 1000};
		layer.msg(content, options, callbackFunction);
	}
	
	Hadmin.prototype.warning = function (content, callbackFunction) {
		let options = {icon: 0, time: 1000};
		layer.msg(content, options, callbackFunction);
	}
	
	/**
	 *
	 * @param content
	 * @param callbackFunction
	 */
	Hadmin.prototype.confirm = function (content, callbackFunction) {
		let options = {icon: 3, title: '提示！'};
		layer.confirm(content || '确定要执行该操作吗？', options, callbackFunction);
	}
	
	/**
	 * @title ajax()    GET 请求
	 * @param url       请求 URl地址
	 * @param params    请求参数
	 * @param options
	 * @returns {*}
	 */
	Hadmin.prototype.httpGet = function (url, params, options) {
		return this.ajax('GET', url, params, options);
	}
	/**
	 * @title ajax()    POST 请求
	 * @param url       请求 URl地址
	 * @param params    请求参数
	 * @param options
	 * @returns {*}
	 */
	Hadmin.prototype.httpPost = function (url, params, options) {
		return this.ajax('POST', url, params, options);
	}
	
	/**
	 * @title ajax()函数二次封装
	 * @param url       请求 URl地址
	 * @param type      请求类型
	 * @param params    请求参数
	 * @param options
	 * @returns {jQuery}
	 */
	Hadmin.prototype.ajax = function (type, url, params, options) {
		const deferred = $.Deferred();
		let load = true;
		if (typeof (options) !== "undefined") {
			load = (options.load !== false);
		}
		
		let loadIndex;
		$.ajax({
			url        : url, type: type || 'GET', data: params || {}, dataType: 'json', beforeSend: function () {
				if (load) {
					loadIndex = layer.load(1, {shade: 0.2});
				}
			}, success : function (res) {
				if (res.code === 200) {
					if (res.url && res.url != 'tip') {
						adm.success(res.msg, function () {
							if (res.url === "reload") {
								window.location.reload();
							} else if (res.url === "close.reload") {
								adm.close();
								adm.fatherReload();
							} else if (res.url === 'tip') {
								return false;
							} else {
								window.location.href = res.url;
							}
						});
					} else {
						// 业务正常
						deferred.resolve(res)
					}
				} else {
					// 业务异常
					layer.msg(res.msg, {icon: 7, time: 2000});
					deferred.reject(res);
				}
			}, complete: function () {
				if (load) {
					layer.close(loadIndex);
				}
			}, error   : function () {
				layer.close(loadIndex);
				layer.msg('服务器错误', {icon: 2, time: 2000});
				deferred.reject('ajax error: 服务器错误');
			}
		});
		return deferred.promise();
	}
	
	/**
	 * @param type
	 * @param url
	 * @param data
	 * @param params
	 */
	Hadmin.prototype.http = function (type, url, data, params) {
		/*$.ajax({
			url        : url,
			dataType   : params.dataType || 'json',
			contentType: params.contentType || "application/x-www-form-urlencoded;charset=utf-8",
			type       : type || "post",
			async      : false,
			data       : data || {},
			success    : function (res, status) {
				if (res.code === 200) {
					if (typeof params.success == 'function') {
						params.success(res, status);
					} else {
						layer.msg(res.msg || '操作成功!', {icon: 1});
					}
				} else {
					if (typeof params.error == 'function') {
						params.error(res, status);
					} else {
						layer.msg(res.msg || '操作失败!', {icon: 5});
					}
				}
				
			},
			error      : function (XMLHttpRequest, textStatus, errorThrown) {
				layer.close(loading);
				if (typeof params.serverError == 'function') {
					params.serverError(XMLHttpRequest.responseText, textStatus, XMLHttpRequest, errorThrown);
				} else {
					// alert("服务器异常 ，请联系开发人员！");
					layer.msg('服务器异常 ，请联系开发人员！', {icon: 5});
					console.error(XMLHttpRequest.responseText);
				}
			},
			complete   : function () {
				layer.close(loading);
			},
		});*/
	};
	
	win.adm = new Hadmin();
}(window);

layui.config({
	base   : '/static/admin/modules/', //假设这是你存放拓展模块的根目录
	version: '0.0.1',
}).extend({
	treeTable: 'treeTable/treeTable',
	jstree   : 'jstree/jstree',
	uploader : 'file-upload',
	sortable : 'sortable',
	xmSelect : 'xm-select',
}).use(['layer', 'element', 'jquery'], function () {
	var $ = layui.jquery, layer = layui.layer, element = layui.element;
	// 打开页面初始
	adm.init();
	//左侧菜单
	
	// 页面加载完要做的
	adm.end();
})