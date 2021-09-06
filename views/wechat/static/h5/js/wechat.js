import Vue from 'vue';

function isWechat() {
  let mic = window.navigator.userAgent.toLowerCase().match(/micromessenger/i);
  return mic && mic[0] === 'micromessenger' ? 1 : 0;
}

function call() {
  let whiteList = ['/pages/order/submit'];
  let pathname = window.location.pathname;
  pathname = pathname.split('?')[0];
  if (whiteList.indexOf(pathname) > -1) {
    init();
  }
}

function init(
  callback,
  jsApiList = [
    'chooseWXPay',
    'hideAllNonBaseMenuItem',
    'showMenuItems',
    'onMenuShareAppMessage',
    'updateTimelineShareData',
    'onMenuShareTimeline',
    'hideMenuItems'
  ]
) {
  if (isWechat()) {
    let url = window.location.href.split('#')[0];
    Vue.prototype.$heshop
      .jssdk('get', {
        url: url
      })
      .then(e => {
        let config = {
          debug: false,
          appId: e.appid,
          timestamp: e.timestamp,
          nonceStr: e.noncestr,
          signature: e.signature,
          jsApiList: jsApiList,
          openTagList: ['wx-open-subscribe', 'wx-open-launch-weapp']
        };
        jWeixin.config(config);
        jWeixin.error(err => {
          console.error('config fail:', err);
        });
        jWeixin.ready(() => {
          if (callback) callback(jWeixin);
        });
      });
  }
}

function chooseWXPay({ timestamp, nonceStr, packAge, signType, paySign, success, fail, cancel }) {
  jWeixin.chooseWXPay({
    timestamp: timestamp,
    nonceStr: nonceStr,
    package: packAge,
    signType: signType,
    paySign: paySign,
    success: function (res) {
      success && success(res);
    },
    fail: function (err) {
      fail && fail(err);
    },
    cancel: function (res) {
      cancel && cancel(res);
    },
    complete: function () {}
  });
}

function hideAllNonBaseMenuItem() {
  init(function (jssdk) {
    jssdk.hideAllNonBaseMenuItem();
  });
}

// 批量显示功能按钮接口
function showMenuItems({ menuList = [] }) {
  init(
    function (jssdk) {
      jssdk.showMenuItems({
        menuList: menuList // 要显示的菜单项
      });
    },
    ['showMenuItems']
  );
}

// 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮
function hideMenuItems({ menuList = [] }) {
  init(
    function (jssdk) {
      jssdk.hideMenuItems({
        menuList: menuList
      });
    },
    ['hideMenuItems']
  );
}

// 分享
function updateShareData({ title = '', desc = ' ', path = '', imageUrl = '' }) {
  let { origin, pathname } = window.location;
  init(
    function (jssdk) {
      // 分享给用户
      jssdk.onMenuShareAppMessage({
        title: title, // 分享标题
        desc: desc, // 分享描述
        link: origin + pathname + '?r=wechat#' + path, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
        imgUrl: imageUrl, // 分享图标
        type: 'link' // 如果type是music或video，则要提供数据链接，默认为空
      });
      // 分享到朋友圈
      jssdk.updateTimelineShareData({
        title: title, // 分享标题
        link: origin + pathname + '?r=wechat#' + path, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
        imgUrl: imageUrl // 分享图标
      });
      // 兼容分享到朋友圈（官网已经说废弃）
      jssdk.onMenuShareTimeline({
        title: title, // 分享标题
        link: origin + pathname + '?r=wechat#' + path, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
        imgUrl: imageUrl // 分享图标
      });
    },
    ['onMenuShareAppMessage', 'updateTimelineShareData', 'onMenuShareTimeline']
  );
}

// 获取定位
function getLocation({ success }) {
  init(
    function (jssdk) {
      jssdk.getLocation({
        type: 'wgs84',
        success(response) {
          success && success(response);
        },
        fail() {}
      });
    },
    ['getLocation']
  );
}

function downloadImage() {}

export default {
  isWechat,
  showMenuItems,
  hideAllNonBaseMenuItem,
  chooseWXPay,
  updateShareData,
  init,
  call,
  hideMenuItems,
  getLocation
};
