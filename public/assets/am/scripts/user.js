AM.user = {
    init : function() {
        this.loadLists();
        this.bindEvent();
    },
    bindEvent : function() {
        var that = this;
        $('#add-btn').click(function(){
            that.addItem();
        });

        $('#item_list').on('click','.edit-btn',function(){
            uid = $(this).attr('data-id');
            that.editItem(uid);
        });

        $('#save-btn').click(function(){
            that.saveItem();
        });

        $('#item_list').on('click','.del-btn',function(){
            uid = $(this).attr('data-id');
            that.delItem(uid);
        });

        $('#batch-del-btn').on('click',function(){
            var uids = [];
            $('#item_list tbody tr .checkboxes:checked').each(function(){
                uids.push($(this).val());
            });
            that.delItem(uids.join(','));
        });
    },
    loading : function(action){
        $('#loading').modal(action);
    },
    alert : function(message){
        $('#alert-modal .modal-body').html(message);
        $('#alert-modal').modal('show');
    },
    checkboxs : function(){
        $('#item_list').find('.group-checkable').change(function () {
            var set = jQuery(this).attr("data-set");
            var checked = jQuery(this).is(":checked");
            jQuery(set).each(function () {
                if (checked) {
                    $(this).prop("checked", true);
                    $(this).parents('tr').addClass("active");
                } else {
                    $(this).prop("checked", false);
                    $(this).parents('tr').removeClass("active");
                }
            });
        });

        $('#item_list').on('change', 'tbody tr .checkboxes', function () {
            $(this).parents('tr').toggleClass("active");
        });
    },
    clearForm : function(myform){
        myform.find('input[type=text]').val('');
        myform.find('input[type=hidden]').val('');
        myform.find('select').each(function(){
            $(this).find('option').eq(0).attr("selected", true);
        });
    },
    showModal : function(type,data){
        $('#form_alert').html('');
        myform = $("#editModal");
        if(type == 'add'){
            if(myform.find('input[name="uid"]').val()){
                this.clearForm(myform);
            }
            myform.find('.modal-title').html('添加用户');
            myform.find('input[name=username]').attr('disabled',false);
            myform.find('input[name=status]').eq(0).prop('checked',true);
            myform.modal('show');
        }else if(type == 'edit'){
            myform.find('.modal-title').html('修改用户');
            myform.find('input[name=username]').attr('disabled',true);

            myform.find('input[name="uid"]').val(data.uid);
            myform.find('input[name="username"]').val(data.username);
            myform.find('input[name="mobile"]').val(data.mobile);
            myform.find('select[name="role"] option[value=' + data.role + ']').attr("selected", true);
            myform.find('input[name="status"][value=' + data.status + ']').prop("checked", true);

            myform.modal('show');
        }

    },
    formError : function(message){
        App.alert({
            container: '#form_alert',
            place: 'prepend',
            type: 'danger',
            message: message,
            reset: true,
            focus: true,
            close: true,
            closeInSeconds: 10000,
            icon : 'fa fa-warning'
        });
    },
    formSuccess : function(){
        App.alert({
            container: '#form_alert',
            place: 'prepend',
            type: 'success',
            message: '保存成功',
            reset: true,
            focus: true,
            close: true,
            closeInSeconds: 10000,
            icon : 'fa fa-check'
        });
    },
    loadLists : function(){

        $('#item_list').DataTable({
            ajax: {
                //指定数据源
                url: "/user/get_lists"
            },
            language: {
                "lengthMenu": "每页 _MENU_ 条记录",
                "zeroRecords": "没有找到记录",
                "info": "第 _PAGE_ 页 ( 总共 _PAGES_ 页 )",
                "infoEmpty": "无记录",
                "infoFiltered": "(从 _MAX_ 条记录过滤)"
            },
            //每页显示数据
            pageLength: 10,
            columns: [
                {
                    "data": "uid"
                },
                {
                    "data": "username"
                },
                {
                    "data": "role"
                },
                {
                    "data": "mobile"
                },
                {
                    "data": "status"
                },
                {
                    "data": "created_at"
                },
                {
                    "data": null
                }
            ],
            "order": [[ 5, "desc" ]],
            "columnDefs": [
                {
                    'targets' : [0,1,2,3,4,6],    //除第2，第7列外，都默认不排序
                    'orderable' : false
                },
                {
                    "render": function(data, type, row, meta) {
                        //渲染 把数据源中的标题和url组成超链接
                        return '<td data-id="'+row['uid']+'"><label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input type="checkbox" class="checkboxes" value="'+data+'" /><span></span></label></td>';
                    },
                    //指定是第三列
                    "targets": 0
                },
                {
                    "render": function(data, type, row, meta) {
                        if(data == '启用'){
                            str = '<span class="label label-sm label-success">'+data+'</span>';
                        }else{
                            str = '<span class="label label-sm label-danger">'+data+'</span>';
                        }
                        return '<td>'+str+'<td>';

                    },
                    "targets": 4
                },
                {
                    "render": function(data, type, row, meta) {
                        return '<td><div class=" btn-group-sm btn-group-solid" ><a role="button" class="btn blue btn-sx edit-btn" data-toggle="modal" data-id="'+row['uid']+'"> <i class="fa fa-pencil"></i> 修改 </a>  <a class="btn red btn-sx del-btn" role="button" data-id="'+row['uid']+'"> <i class="fa fa-remove"></i>  删除 </a></div><td>';

                    },
                    "targets": 6
                }
            ]

        });

        this.checkboxs();
    },
    addItem : function(){
        this.showModal('add');
    },
    editItem : function(uid){
        var that = this;
        that.loading('show');
        $.ajax({
            "url":"/user/get_info",
            "data":{uid:uid},
            "dataType":"json",
            "type":"POST",
            "success":function (rdata) {
                that.loading('hide');
                that.showModal('edit',rdata);
            },
            "error":function(res){
                that.loading('hide');

                http_status = res.status;
                json_data = res.responseJSON;
                if(http_status == 400){
                     that.alert(json_data.message+'('+json_data.code+')');
                }else if(http_status == 500){
                     that.alert('服务器错误.');
                }else{
                     that.alert('网络错误.');
                }
            }
        });
    },
    delItem : function(uid){
        var that = this;

        if(!uid){
            that.alert('请选择要删除的对象。');
            return;
        }
        bootbox.confirm({
            title: '操作确认',
            message: '确定要删除吗？删除后将无法恢复。',
            buttons: {
                confirm: {
                    label: "确定"
                },
                cancel: {
                    label: "取消"
                }
            },
            callback: function (result) {
                if (!result)
                    return;

                that.loading('show');
                $.ajax({
                    "url":"/user/remove",
                    "data":{uid:uid},
                    "dataType":"json",
                    "type":"POST",
                    "success":function (rdata) {
                        that.loading('hide');
                        window.location.reload();
                    },
                    "error":function(res){
                        http_status = res.status;
                        json_data = res.responseJSON;
                        that.loading('hide');
                        if(http_status == 400){
                             that.alert(json_data.message+'('+json_data.code+')');
                        }else if(http_status == 500){
                             that.alert('服务器错误.');
                        }else{
                             that.alert('网络错误.');
                        }
                    }
                });
            }
        });
    },
    saveItem : function(){
        var that = this;
        that.loading('show');

        saveForm = $('#editModal');
        uid=saveForm.find("input[name=uid]").val();
        username=saveForm.find("input[name=username]").val();
        password=saveForm.find("input[name=password]").val();
        role=saveForm.find("select[name=role]").val();
        mobile=saveForm.find("input[name=mobile]").val();
        status=saveForm.find("input[name=status]:checked").val();

        $.ajax({
            "url":"/user/save",
            "data":{uid:uid,username:username,password:password,role:role,mobile:mobile,status:status},
            "dataType":"json",
            "type":"POST",
            "success":function (rdata) {
                console.log(rdata);
                that.loading('hide');
                that.formSuccess();

                setTimeout(function(){
                    window.location.reload();
                },1000);
                //window.location.reload();
            },
            "error":function(res){
                console.log(res);
                that.loading('hide');
                http_status = res.status;
                json_data = res.responseJSON;
                if(http_status == 400){
                    that.formError(json_data.message+'('+json_data.code+')');
                    // that.alert('数据格式不正确');
                }else if(http_status == 500){
                    that.formError('服务器错误');
                    // that.alert('服务器错误.');
                }else{
                    that.formError('未知错误');
                    // that.alert('未知错误.');
                }
            }
        });
    }
};

$(function(){
    AM.user.init();
})