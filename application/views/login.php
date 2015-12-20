<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>获取二维码</title>
    <link href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<div class="container">
    <form action="#">
        <div class="form-group">
            <label for="tea_id">工号</label>
            <input type="text" class="form-control" id="tea_id" placeholder="工号">
        </div>
        <div class="form-group">
            <label for="flag">密码</label>
            <input type="password" class="form-control" id="flag" placeholder="密码">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>
<script>
    $(document).ready(function() {
        $("button").click(function(event) {
            /* Act on the event */
            // var tea_id = $("#tea_id").val();
            // var flag = $("#flag").val();

            alert("tea_id");

        });

    });
</script>
<script src="http://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="qrcode.min.js"></script>
</body>
</html>