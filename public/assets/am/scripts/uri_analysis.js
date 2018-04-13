AM.uri_analysis = {
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
    getXAxis: function() {

        d = new Date();
        year = d.getFullYear();
        month = d.getMonth() + 1;
        //console.log(d,year,month);
        d = new Date(year,month,1).toJSON().substring(0,10);
        last_day = new Date(Date.parse(d.replace(/-/g,  "/"))).getDate();
        //console.log(d,last_day);
        xAxis = [];
        for(i = 1;i<= last_day;i++){
            xAxis.push(month+'-'+i);
        }
        return xAxis;
    },
    echartsLine : function(data){
        var myChart1 = echarts.init(document.getElementById("echarts_line"));
        myChart1.setOption({
            title: {
                text: 'API平均耗时月趋势图'
            },
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['平均耗时']
            },
            toolbox: {
                show: false
            },
            calculable: true,
            xAxis: [{
                type: 'category',
                boundaryGap: false,
                data: data['xdata']
            }],
            yAxis: [{
                type: 'value',
                axisLabel: {
                    formatter: '{value}'
                }
            }],
            series: [{
                name: '平均耗时',
                type: 'line',
                data: data['time_t']
            }]
        });

        $(window).resize(function() {
            myChart1.resize();
        });
    },
    loadLists : function(){
        var that = this;

        var pid = $('#pid').val();
        var uri = $('#uri').val();

        $.ajax({
            "url":"/uri_analysis/get_line",
            "data":{pid:pid,uri:uri},
            "dataType":"json",
            "type":"POST",
            "success":function (rdata) {
                data = [];
                //req_t_arr = [];
                time_t_arr = [];
                $(rdata.data).each(function(i,row){
                    //req_t_arr[row['dateline']] = row['req_t'];
                    time_t_arr[row['dateline']] = row['time_t'];
                });

                data['xdata'] = that.getXAxis();
                //data['req_t']  = [];
                data['time_t'] = [];
                $(data['xdata']).each(function(k,d){
                    //data['req_t'].push(req_t_arr[d] ? req_t_arr[d] : 0);
                    data['time_t'].push(time_t_arr[d] ? time_t_arr[d] : 0);
                });

                that.echartsLine(data);
            },
            "error":function(res){
                http_status = res.status;
                json_data = res.responseJSON;

                if(http_status == 400){
                    that.alert(json_data.message+'('+json_data.code+')');
                }else if(http_status == 500){
                    that.alert('服务器错误');
                }else{
                    that.alert('网络错误');
                }
            }
        });

        $('#item_list').DataTable({
            ajax: {
                //指定数据源
                url: "/uri_analysis/get_lists?pid="+pid+"&uri="+uri
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
                    "data": "uri"
                },
                {
                    "data": "time"
                },
                {
                    "data": "dateline"
                },
                {
                    "data": "pid"
                }
            ],
            "order": [[ 1, "desc" ]],
            "columnDefs": [
                {
                    'targets' : [0,2,3],
                    'orderable' : false
                },
                {
                    "render": function(data, type, row, meta) {
                        return '<td><div class=" btn-group-sm btn-group-solid" > <a class="btn green btn-sx view-btn" role="button" href="'+row['xhprof_id']+'"> 查看 </a></div><td>';

                    },
                    "targets": 3
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
                    "url":"/uri_analysis/remove",
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
    AM.uri_analysis.init();
})