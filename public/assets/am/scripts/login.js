AM.login = {
    init : function() {
        this.handleLogin();
    },
    handleLogin : function() {
        var that = this;
        $('.login-form').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            rules: {
                username: {
                    required: true
                },
                password: {
                    required: true
                },
                remember: {
                    required: false
                }
            },

            messages: {
                username: {
                    required: "Username is required."
                },
                password: {
                    required: "Password is required."
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit
                $('.alert-danger', $('.login-form')).show();
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            success: function (label) {
                label.closest('.form-group').removeClass('has-error');
                label.remove();
            },

            errorPlacement: function (error, element) {
                error.insertAfter(element.closest('.input-icon'));
            },

            submitHandler: function (form) {
                //form.submit();
                that.login();
            }
        });

        $('.login-form input').keypress(function (e) {
            if (e.which == 13) {
                if ($('.login-form').validate().form()) {
                    //$('.login-form').submit();
                    that.login();
                }
                return false;
            }
        });
    },
    login : function(){
        var that = this;

        $(".alert").hide();

        username=$("input[name=username]").val();
        password=$("input[name=password]").val();
        remember=$("input[name=remember]:checked").val();

        $.ajax({
            "url":"/login/submit",
            "data":{username:username,password:password,remember:remember},
            "dataType":"json",
            "type":"POST",
            "success":function (rdata) {
                window.location.href='/';
            },
            "error":function(res){
                http_status = res.status;
                json_data = res.responseJSON;

                if(http_status == 400){
                    $(".alert").children('span').html(json_data.message+'('+json_data.code+')');
                    $(".alert").show();

                }else if(http_status == 500){
                    $(".alert").children('span').html('服务器错误.');
                    $(".alert").show();

                }else{
                    $(".alert").children('span').html('网络错误.');
                    $(".alert").show();
                }
            }
        });
    }
};

$(function(){
    AM.login.init();
})