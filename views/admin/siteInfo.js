(function () {
  let origin = window.location.origin;
  window.$_W = {
    AppURL: origin + '/index.php?q=',
    AppWEB: origin + '/index.php?r=wechat',
    AppForm: '',
    AppID: '98c08c25f8136d590c',
    Copyright: 'Powered By Leadshop © 2021',
    AppName: 'leadmall',
    AppAlias: 'fitment',
    AppSecret: '3AYpU16dZ1CY7ejqvrE39B351vanLJVD',
    AppConfig: {
      defaultRoute: 'index/index',
      loginURL: '/api/leadmall/login',
      loginReset: '/api/leadmall/reset',
      loginPage: '/login/index'
    }
  };
})();
