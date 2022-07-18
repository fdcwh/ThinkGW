var layForm;
var table;
layui.use(['form', 'element', 'layer', 'table', 'laydate'], function () {
	var laydate = layui.laydate;
	table = layui.table;
	layForm = layui.form;
	var tablelist = $('.layui-table').attr('id');
	laydate.render({
		elem: '#sstarttime'
	});
	laydate.render({
		elem: '#sendtime'
	});
	
	
	table.on('tool(' + tablelist + ')', function (obj) {
		var data = obj.data;
		var url = $(this).attr('data-href');
		if (obj.event === 'del') {
			layer.confirm('确定要删除吗', function (index) {
				loading = layer.load(1, {shade: [0.1, '#fff']});
				$.post(url ? url : 'delete', {ids: [data.id]}, function (res) {
					layer.close(loading);
					if (res.code > 0) {
						layer.msg(res.msg);
						obj.del();
					} else {
						layer.msg(res.msg);
					}
				});
			});
		}
		
	});
	//监听状态
	layForm.on('switch(status)', function (obj) {
		loading = layer.load(1, {shade: [0.1, '#fff']});
		var field = obj.elem.name;
		var url = $(obj.elem).attr('data-href') ? $(obj.elem).attr('data-href') : 'state';
		$.post(url, {id: obj.value, field: field}, function (res) {
			layer.close(loading);
			if (res.code > 0) {
				layer.msg(res.msg);
			} else {
				layer.msg(res.msg);
			}
		});
	});
	layForm.on('submit(submit)', function (data) {
		data.elem.disabled = true;
		layer.msg('正在请求,请稍候', {icon: 16, shade: 0.01, time: 0});
		$.ajax({
			url    : $(this).parents("form").attr('action'),
			type   : "post",
			data   : data.field,
			success: function (json) {
				data.elem.disabled = false;
				layer.closeAll();
				if (json.code == 1) {
					if (json.url == '' || json.url == undefined) {
						layer.msg(json.msg, {icon: 6});
						return false;
					} else {
						layer.msg(json.msg, {
							icon: 6,
							time: 2000
						}, function () {
							if (window.parent != window) {// 如果是在框架中
								window.parent.location.href = json.url;
							} else {
								window.location.href = json.url;
							}
						});
					}
				} else {
					if (json.url == '' || json.url == undefined) {
						$('.verify').click();
						layer.msg(json.msg, {icon: 5});
						return false;
					} else {
						layer.msg(json.msg, {
							icon: 5,
							time: 2000
						}, function () {
							if (window.parent != window) {// 如果是在框架中
								window.parent.location.href = json.url;
							} else {
								window.location.href = json.url;
							}
						});
					}
					
				}
			},
			error  : function (e) {
				data.elem.disabled = false;
				layer.msg('请求错误');
				return false;
			}
		});
	});
	
	$(document).on('click', '#delAll', function () {
		var url = $(this).attr('data-href');
		var checkStatus = table.checkStatus(tablelist);
		var data = checkStatus.data;
		var ids = [];
		for (var i = 0; i < data.length; i++) {
			ids.push(data[i].id);
		}
		if (ids == '') {
			layer.msg("请选择要操作的数据");
			return false;
		}
		layer.confirm("该操作不可恢复，确定要删除吗", {icon: 3}, function (index) {
			layer.close(index);
			var loading = layer.load(1, {shade: [0.1, '#fff']});
			$.post(url, {ids: ids}, function (res) {
				layer.close(loading);
				if (res.code > 0) {
					layer.msg(res.msg);
					window.location.reload();
				} else {
					layer.msg(res.msg);
				}
			});
		});
	});
	
	$('body').on('click', '#selectAttach', function (data) {
		var url = $(this).attr('data-href');
		iframe = layer.open({
			type   : 2,
			content: url ? url : 'add',
			area   : ['900px', '700px'],
			maxmin : true
		});
	})
})
