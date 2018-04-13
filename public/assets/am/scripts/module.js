AM.module = {
    init : function() {
        this.loadLists();
        this.bindEvent();
    },
    bindEvent : function() {
        var that = this;
        $('#add-btn').click(function(){
            that.addItem();
        });

        $('#edit-btn').click(function(){
            var ids = [];
            selected = $('#module_tree').jstree().get_selected(true);
            $(selected).each(function(i,row){
                console.log(row.id);
                ids.push(row.id);
            });

            if(ids.length == 0){
                that.alert('必须允许选中一个选项.');
                return;
            }

            mid = ids[0];
            that.editItem(mid);
        });

        $('#save-btn').click(function(){
            that.saveItem();
        });

        $('#batch-del-btn').on('click',function(){
            var ids = [];
            selected = $('#module_tree').jstree().get_selected();
            $(selected).each(function(i,row){
                console.log(row);
                ids.push(row);
            });
            that.delItem(ids.join(','));
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
        myform = $("#infoModal");
        if(type == 'add'){
            if(myform.find('input[name="mid"]').val()){
                this.clearForm(myform);
            }
            myform.find('.modal-title').html('添加模块');
            myform.find('input[name=status]').eq(0).prop('checked',true);
            myform.modal('show');
        }else if(type == 'edit'){
            myform.find('.modal-title').html('修改模块');

            myform.find('input[name="mid"]').val(data.mid);
            myform.find('input[name="mname"]').val(data.mname);
            myform.find('input[name="mtag"]').val(data.mtag);
            myform.find('select[name="parent_id"] option[value=' + data.parent_id + ']').attr("selected", true);
            myform.find('input[name="status"][value=' + data.status + ']').prop("checked", true);
            myform.find('input[name="order_num"]').val(data.order_num);
            myform.find('input[name="icon"]').val(data.icon);

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

        $("#module_tree").jstree({
            "plugins" : [ "dnd", "state", "types","checkbox" ],
            "core" : {
                "themes" : {
                    "responsive": false
                },
                // so that create works
                "check_callback" : true,
                'data' : {
                    'url' : function (node) {
                        return '/module/get_lists';
                    },
                    'data' : function (node) {
                        return { 'parent' : node.parent_id };
                    }
                }
            },
            "types" : {
                "default" : {
                    "icon" : "fa fa-folder icon-state-warning icon-lg"
                },
                "file" : {
                    "icon" : "fa fa-file icon-state-warning icon-lg"
                }
            },
            "state" : { "key" : "status" }

        });



        //this.checkboxs();
    },
    addItem : function(){
        this.showModal('add');
    },
    editItem : function(mid){
        var that = this;
        that.loading('show');
        $.ajax({
            "url":"/module/get_info",
            "data":{mid:mid},
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
    delItem : function(mid){
        var that = this;

        if(!mid){
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
                    "url":"/module/remove",
                    "data":{mid:mid},
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

        saveForm = $('#infoModal');
        mid=saveForm.find("input[name=mid]").val();
        mname=saveForm.find("input[name=mname]").val();
        mtag=saveForm.find("input[name=mtag]").val();
        parent_id=saveForm.find("select[name=parent_id]").val();
        status=saveForm.find("input[name=status]:checked").val();
        order_num=saveForm.find("input[name=order_num]").val();
        icon=saveForm.find("input[name=icon]").val();

        $.ajax({
            "url":"/module/save",
            "data":{mid:mid,mname:mname,mtag:mtag,parent_id:parent_id,status:status,order_num:order_num,icon:icon},
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
                    //alert('数据格式不正确');
                }else if(http_status == 500){
                    that.formError('服务器错误');
                    //alert('服务器错误.');
                }else{
                    that.formError('未知错误');
                    //alert('未知错误.');
                }
            }
        });
    }
};

$(function(){
    AM.module.init();
})