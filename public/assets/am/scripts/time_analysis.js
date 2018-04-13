AM.time_analysis = {
    init : function() {
        this.loadLists();
        this.bindEvent();
    },
    bindEvent : function() {
        var that = this;
        $('#item_list').on('click','.del-btn',function(){
            ids = $(this).attr('data-id');
            that.delItem(ids);
        });

        $('#batch-del-btn').on('click',function(){
            var ids = [];
            $('#item_list tbody tr .checkboxes:checked').each(function(){
                ids.push($(this).val());
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

    loadLists : function(){
        var that = this;

        $('#item_list').DataTable({
            ajax: {
                //指定数据源
                url: "/time_analysis/get_lists"
            },
            language: {
                "lengthMenu": "每页 _MENU_ 条记录",
                "zeroRecords": "没有找到记录",
                "info": "第 _PAGE_ 页 ( 总共 _PAGES_ 页 )",
                "infoEmpty": "无记录",
                "infoFiltered": "(从 _MAX_ 条记录过滤)"
            },
            bPaginate:false,
            bFilter:false,
            bInfo:false,
            //每页显示数据
            pageLength: 10,
            columns: [
                {
                    "data": "pname"
                },
                {
                    "data": "req_y"
                },
                {
                    "data": "req_t"
                },
                {
                    "data": null
                },
                {
                    "data": "time_y"
                },
                {
                    "data": "time_t"
                },
                {
                    "data": "time_r"
                },
                {
                    "data": "time_max"
                },
                {
                    "data": "time_min"
                },
                {
                    "data": "dateline"
                },
                {
                    "data": "lid"
                }
            ],
            "order": [[ 5, "desc" ]],
            "columnDefs": [
                {
                    'targets' : [0,1,2,3,6,7,8,9,10],    //除第2，第7列外，都默认不排序
                    'orderable' : false
                },
                {
                    "render": function ( data, type, row ) {
                        if(row['req_t'] && !row['req_y'])
                            rate = 100;
                        else if(!row['req_t'] && !row['req_y'])
                            rate = 0;
                        else
                            rate = Math.round( ( row['req_t'] - row['req_y'] ) /  row['req_y'] * 10000 )/100;
                        return rate+'%';
                    },
                    "targets": 3
                },
                {
                    "render": function ( data, type, row ) {
                        if(row['time_t'] && !row['time_y'])
                            rate = 100;
                        else if(!row['time_t'] && !row['time_y'])
                            rate = 0;
                        else
                            rate = Math.round( ( row['time_t'] - row['time_y'] ) /  row['time_y'] * 10000 )/100;
                        return rate+'%';
                    },
                    "targets": 6
                },
                {
                    "render": function(data, type, row, meta) {
                        return '<td><div class=" btn-group-sm btn-group-solid" > <a class="btn green btn-sx view-btn" role="button" href="/api_analysis?pid='+row['pid']+'"> 查看 </a></div><td>';

                    },
                    "targets": 10
                }
            ]

        });

        this.checkboxs();
    },
    delItem : function(lid){
        var that = this;

        if(!lid){
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
                    "url":"/time_analysis/remove",
                    "data":{lid:lid},
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
                            alert(json_data.message+'('+json_data.code+')');
                        }else if(http_status == 500){
                            alert('服务器错误.');
                        }else{
                            alert('网络错误.');
                        }
                    }
                });
            }
        });
    },

};

$(function(){
    AM.time_analysis.init();
})