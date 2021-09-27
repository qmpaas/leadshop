<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>applet</title>
    <script>document.addEventListener('DOMContentLoaded', function () {
        document.documentElement.style.fontSize = document.documentElement.clientWidth / 20 + 'px';
    });

    let origin = window.location.origin;
    window.siteinfo = {
        "uniacid": "3",
        "acid": "3",
        "multiid": "0",
        "version": "1.1.5",
        "AppURL": origin + "/index.php",
        "siteroot": origin + "/index.php",
        "design_method": "3",
        "tabBar": {
            "color": "#ccc",
            "selectedColor": "#ff4c92",
            "borderStyle": "black",
            "backgroundColor": "#ffffff",
            "list": []
        }
    }

    <?php if (!empty($tabBar)) {
        echo "window.siteinfo.tabBar = " . json_encode($tabBar);
    }?>

    var coverSupport =
        'CSS' in window &&
        typeof CSS.supports === 'function' &&
        (CSS.supports('top: env(a)') || CSS.supports('top: constant(a)'));
    document.write(
        '<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0' +
        (coverSupport ? ', viewport-fit=cover' : '') +
        '" />'
    );</script>
    <link rel="stylesheet" href="/assets/wechat/static/index.f4fc78fe.css">
</head>
<body>
<noscript><strong>Please enable JavaScript to continue.</strong></noscript>
<div id="app"></div>
<script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
<script src="/assets/wechat/static/js/chunk-vendors.310890d9.js"></script>
<script src="/assets/wechat/static/js/index.3200d5b5.js"></script>
</body>
</html>
