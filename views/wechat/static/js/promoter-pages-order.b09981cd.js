(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["promoter-pages-order"],{2118:function(e,t,a){"use strict";var r=a("d1c3"),n=a.n(r);n.a},2727:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r={name:"he-no-content-yet",props:{text:{type:String,default:function(){return"暂无内容"}},type:{type:String,default:""},image:{type:String,default:""}},computed:{newImage:function(){return this.image?this.image:this.ipAddress+"/goods-imgae-no.png"}}};t.default=r},"3a9d":function(e,t,a){"use strict";a.r(t);var r=a("f9e4"),n=a.n(r);for(var o in r)["default"].indexOf(o)<0&&function(e){a.d(t,e,(function(){return r[e]}))}(o);t["default"]=n.a},"45b6":function(e,t,a){"use strict";var r=a("99ca").default;Object.defineProperty(t,"__esModule",{value:!0}),t.goods=c,t.promotermaterial=s,t.promotermaterialShare=f,t.recruit=h,t.useAgreement=u,t.agreement=d,t.receiveRecruitToken=l,t.applyMonitoring=m,t.applyPromoter=p,t.personalCenter=v,t.applyAudit=g,t.promoterzone=_,t.dynamicLike=w,t.dynamicDel=y,t.publishDynamic=b,t.dynamicDetail=x,t.dynamicEdit=k,t.searchGoods=P,t.promoterlevel=C,t.promoterorderList=z,t.promoterOrderCount=O,t.promoterChildList=L,t.promoterChildCount=E,t.rankList=N,t.finance=T,t.financeList=S,t.userDetail=A,a("f9ee"),a("c342"),a("211d"),a("ba85");var n=r(a("910f")),o=a("765d"),i=n.default.prototype.$heshop;function c(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{search:"",sort_key:"created_time",sort_value:"ASC"},a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:10;return new Promise((function(r,n){i.promotergoods("get",t).page(e,a).then((function(e){(0,o.transformPageHeaders)(e);var t=e.data,a=e.pagination;r({data:t,pagination:a})})).catch(n)}))}function s(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0,a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"",r=arguments.length>3&&void 0!==arguments[3]?arguments[3]:10;return new Promise((function(n,c){i.promotermaterial("get",{type:t,content:a}).page(e,r).then((function(e){(0,o.transformPageHeaders)(e);var t=e.data,a=e.pagination;n({data:t,pagination:a})})).catch(c)}))}function f(e){return new Promise((function(t,a){i.promotermaterial("post",{id:e},{}).then(t).catch(a)}))}function h(){return new Promise((function(e,t){i.search("post",{include:"setting"},{keyword:"promoter_recruit_make"}).then((function(t){e(t.content)})).catch(t)}))}function u(){return new Promise((function(e,t){i.search("post",{include:"setting"},{keyword:"promoter_setting",content_key:"use_agreement"}).then(e).catch(t)}))}function d(){return new Promise((function(e,t){i.search("post",{include:"setting"},{keyword:"promoter_setting"}).then((function(t){var a=t.content,r=a.agreement_content,n=a.agreement_title;e({agreement_content:r,agreement_title:n})})).catch(t)}))}function l(e){return new Promise((function(t,a){i.promoter("get",{behavior:"recruiting",invite_id:e}).then(t).catch(a)}))}function m(){return new Promise((function(e,t){i.promoter("get",{behavior:"apply_check"}).then(e).catch(t)}))}function p(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[];return new Promise((function(t,a){i.promoter("post",{apply_content:e}).then(t).catch(a)}))}function v(){return new Promise((function(e,t){i.promoter("get").then(e).catch(t)}))}function g(){return new Promise((function(e,t){i.promoter("get",{behavior:"apply_audit"}).then(e).catch(t)}))}function _(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,t=arguments.length>1?arguments[1]:void 0;return new Promise((function(a,r){i.promoterzone("get",{UID:t}).page(e,10).then((function(e){(0,o.transformPageHeaders)(e);var t=e.data,r=e.pagination;a({data:t,pagination:r})})).catch(r)}))}function w(e){return new Promise((function(t,a){i.promoterzone("post",{behavior:"vote"},{zone_id:e}).then(t).then(a)}))}function y(e){return new Promise((function(t,a){i.promoterzone("delete",e).then(t).then(a)}))}function b(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};return new Promise((function(t,a){i.promoterzone("post",e).then(t).catch(a)}))}function x(e){return e=parseInt(e),new Promise((function(t,a){i.promoterzone("get",e).then(t).catch(a)}))}function k(e,t){return e=parseInt(e),new Promise((function(a,r){i.promoterzone("put",e,t).then(a).catch(r)}))}function P(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return new Promise((function(a,r){i.search("post",{include:"goods"},{keyword:t}).page(e,10).then((function(e){(0,o.transformPageHeaders)(e);var t=e.data,r=e.pagination;a({data:t,pagination:r})})).catch(r)}))}function C(){return new Promise((function(e,t){i.promoterlevel("get").then(e).catch(t)}))}function z(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,t=arguments.length>1?arguments[1]:void 0,a=t.time_type,r=void 0===a?"all":a,n=t.time_start,c=void 0===n?"":n,s=t.time_end,f=void 0===s?"":s;return new Promise((function(t,a){i.promoterorder("get",{time_type:r,time_start:c,time_end:f}).page(e,10).then((function(e){(0,o.transformPageHeaders)(e);var a=e.data,r=e.pagination;t({data:a,pagination:r})})).catch(a)}))}function O(e){var t=e.time_type,a=void 0===t?"all":t,r=e.time_start,n=void 0===r?"":r,o=e.time_end,c=void 0===o?"":o;return new Promise((function(e,t){i.promoterorder("get",{time_type:a,time_start:n,time_end:c,behavior:"count"}).then(e).catch(t)}))}function L(e,t){return new Promise((function(a,r){i.promoter("post",{behavior:"search"},{parent:t}).page(e,10).then((function(e){(0,o.transformPageHeaders)(e);var t=e.data,r=e.pagination;a({data:t,pagination:r})})).catch(r)}))}function E(){return new Promise((function(e,t){i.promoter("post",{behavior:"tab"},{}).then(e).catch(t)}))}function N(e){var t=e.ranking_dimension,a=void 0===t?"":t,r=e.ranking_time,n=void 0===r?1:r;return new Promise((function(e,t){i.rank("get",{ranking_dimension:a,ranking_time:n}).then((function(t){e(t)})).catch(t)}))}function T(e){var t=e.price,a=void 0===t?"":t,r=e.type,n=void 0===r?null:r,o=e.extra,c=void 0===o?{}:o;return new Promise((function(e,t){i.finance("post",{model:"promoter"},{price:a,type:n,extra:c}).then(e).catch(t)}))}function S(e,t){var a=t.model,r=void 0===a?"promoter":a,n=t.status,o=void 0===n?null:n,c=t.first_day,s=void 0===c?null:c,f={model:r,status:o};return s&&(f.first_day=s),new Promise((function(e,t){i.finance("get",f).then(e).catch(t)}))}function A(e){return new Promise((function(t,a){i.users("get",{behavior:"simple_info",UID:e}).then(t).catch(a)}))}},"536b":function(e,t,a){var r=a("a1a8");t=r(!1),t.push([e.i,'@charset "UTF-8";\n/* start--主题色--start */\n/* end--主题色--end */.font-family-sc[data-v-f5a26ff8], .he-search .he-switch .flex-sub[data-v-f5a26ff8], .he-search .he-customize[data-v-f5a26ff8], .he-order--total[data-v-f5a26ff8], .he-price--total[data-v-f5a26ff8], .he-order--item .he-sign[data-v-f5a26ff8], .he-user--name[data-v-f5a26ff8], .he-order--sn[data-v-f5a26ff8], .he-item--footer .he-item--price[data-v-f5a26ff8]{font-family:PingFang SC}.font-family-din[data-v-f5a26ff8]{font-family:DIN}.he-card[data-v-f5a26ff8]{background-color:#fff;padding:%?32?%;border-radius:%?16?%;margin-bottom:%?16?%}.iconbtn_arrow[data-v-f5a26ff8]{font-size:%?18?%;color:#bebebe}[data-v-f5a26ff8] .input-placeholder{font-family:PingFang SC;font-size:%?26?%;font-weight:500;color:#999}.he-search[data-v-f5a26ff8]{width:%?750?%;height:%?244?%;background:#fff;border-radius:0 0 %?32?% %?32?%;position:-webkit-sticky;position:sticky;padding:%?32?%;top:0;z-index:10;box-shadow:0 0 %?20?% 0 rgba(0,0,0,.04)}.he-search .he-switch[data-v-f5a26ff8]{height:%?80?%}.he-search .he-switch .flex-sub[data-v-f5a26ff8]{border-radius:%?8?%;background:#f5f5f5;line-height:%?80?%;text-align:center;font-size:%?28?%;color:#262626;font-weight:500}.he-search .he-switch .flex-sub[data-v-f5a26ff8]:not(:last-child){margin-right:%?18?%}[data-theme="red_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{background-color:rgba(230,11,48,.05)!important}[data-theme="purple_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{background-color:rgba(143,45,243,.05)!important}[data-theme="blue_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{background-color:rgba(51,167,255,.05)!important}[data-theme="green_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{background-color:rgba(31,197,81,.05)!important}[data-theme="orange_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{background-color:rgba(255,127,0,.05)!important}[data-theme="golden_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{background-color:rgba(202,164,90,.05)!important}[data-theme="red_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{color:#e60b30!important}[data-theme="purple_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{color:#8f2df3!important}[data-theme="blue_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{color:#33a7ff!important}[data-theme="green_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{color:#1fc551!important}[data-theme="orange_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{color:#ff7f00!important}[data-theme="golden_theme"] .he-search .he-switch .flex-sub.active[data-v-f5a26ff8]{color:#caa45a!important}.he-search .he-customize[data-v-f5a26ff8]{height:%?80?%;background:#f5f5f5;border-radius:%?8?%;padding:%?26?% %?24?% %?26?% %?28?%;font-size:%?28?%;font-weight:500;color:#262626;margin-top:%?20?%}.he-search .he-customize .iconbtn_arrow[data-v-f5a26ff8]{font-size:%?20?%;color:#bebebe}.he-search .he-customize.select-time[data-v-f5a26ff8]{border-style:solid;border-width:%?1?%}[data-theme="red_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{background-color:rgba(230,11,48,.05)!important}[data-theme="purple_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{background-color:rgba(143,45,243,.05)!important}[data-theme="blue_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{background-color:rgba(51,167,255,.05)!important}[data-theme="green_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{background-color:rgba(31,197,81,.05)!important}[data-theme="orange_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{background-color:rgba(255,127,0,.05)!important}[data-theme="golden_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{background-color:rgba(202,164,90,.05)!important}[data-theme="red_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{border-color:#e60b30!important}[data-theme="purple_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{border-color:#8f2df3!important}[data-theme="blue_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{border-color:#33a7ff!important}[data-theme="green_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{border-color:#1fc551!important}[data-theme="orange_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{border-color:#ff7f00!important}[data-theme="golden_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{border-color:#caa45a!important}[data-theme="red_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{color:#e60b30!important}[data-theme="purple_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{color:#8f2df3!important}[data-theme="blue_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{color:#33a7ff!important}[data-theme="green_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{color:#1fc551!important}[data-theme="orange_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{color:#ff7f00!important}[data-theme="golden_theme"] .he-search .he-customize.select-time[data-v-f5a26ff8]{color:#caa45a!important}[data-theme="red_theme"] .he-search .he-customize.select-time .iconbtn_arrow[data-v-f5a26ff8]{color:#e60b30!important}[data-theme="purple_theme"] .he-search .he-customize.select-time .iconbtn_arrow[data-v-f5a26ff8]{color:#8f2df3!important}[data-theme="blue_theme"] .he-search .he-customize.select-time .iconbtn_arrow[data-v-f5a26ff8]{color:#33a7ff!important}[data-theme="green_theme"] .he-search .he-customize.select-time .iconbtn_arrow[data-v-f5a26ff8]{color:#1fc551!important}[data-theme="orange_theme"] .he-search .he-customize.select-time .iconbtn_arrow[data-v-f5a26ff8]{color:#ff7f00!important}[data-theme="golden_theme"] .he-search .he-customize.select-time .iconbtn_arrow[data-v-f5a26ff8]{color:#caa45a!important}.he-body[data-v-f5a26ff8]{padding:0 %?20?%;overflow:hidden}.he-order--total[data-v-f5a26ff8]{font-size:%?28?%;font-weight:500;color:#999;line-height:%?48?%;margin:%?32?% %?12?% 0 %?12?%}.he-total[data-v-f5a26ff8]{padding:0 %?12?%;margin-bottom:%?24?%}.he-total .he-price--total[data-v-f5a26ff8]:last-child{margin-right:%?15?%}.he-price--total[data-v-f5a26ff8]{font-size:%?28?%;font-weight:500;line-height:%?48?%}.he-price--total .he-label[data-v-f5a26ff8]{color:#999}.he-price--total .he-value[data-v-f5a26ff8]{color:#262626}.he-order--item[data-v-f5a26ff8]{width:%?710?%;border-radius:%?8?%;padding:%?24?%}.he-order--item .he-sign[data-v-f5a26ff8]{font-size:%?24?%;font-weight:500;line-height:%?32?%;border:%?1?% solid transparent;padding:0 %?10?%;border-radius:%?16?%}[data-theme="red_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{border-color:#e60b30!important}[data-theme="purple_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{border-color:#8f2df3!important}[data-theme="blue_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{border-color:#33a7ff!important}[data-theme="green_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{border-color:#1fc551!important}[data-theme="orange_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{border-color:#ff7f00!important}[data-theme="golden_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{border-color:#caa45a!important}[data-theme="red_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{color:#e60b30!important}[data-theme="purple_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{color:#8f2df3!important}[data-theme="blue_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{color:#33a7ff!important}[data-theme="green_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{color:#1fc551!important}[data-theme="orange_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{color:#ff7f00!important}[data-theme="golden_theme"] .he-order--item .he-sign[data-v-f5a26ff8]{color:#caa45a!important}.he-user[data-v-f5a26ff8]{height:%?56?%}.he-user--name[data-v-f5a26ff8]{font-size:%?28?%;font-weight:500;color:#222;margin-left:%?16?%}.he-order--sn[data-v-f5a26ff8]{font-size:%?24?%;font-weight:500;color:#999;line-height:%?48?%}.he-item--footer[data-v-f5a26ff8]{border-top:%?1?% solid #e5e5e5;padding-top:%?16?%;margin-top:%?16?%}.he-item--footer .he-item--price[data-v-f5a26ff8]{font-size:%?24?%;font-weight:500;line-height:%?32?%}[data-theme="red_theme"] .he-item--footer .he-item--price:last-child .he-value[data-v-f5a26ff8]{color:#e60b30!important}[data-theme="purple_theme"] .he-item--footer .he-item--price:last-child .he-value[data-v-f5a26ff8]{color:#8f2df3!important}[data-theme="blue_theme"] .he-item--footer .he-item--price:last-child .he-value[data-v-f5a26ff8]{color:#33a7ff!important}[data-theme="green_theme"] .he-item--footer .he-item--price:last-child .he-value[data-v-f5a26ff8]{color:#1fc551!important}[data-theme="orange_theme"] .he-item--footer .he-item--price:last-child .he-value[data-v-f5a26ff8]{color:#ff7f00!important}[data-theme="golden_theme"] .he-item--footer .he-item--price:last-child .he-value[data-v-f5a26ff8]{color:#caa45a!important}.he-item--footer .he-label[data-v-f5a26ff8]{color:#999}.he-item--footer .he-value[data-v-f5a26ff8]{color:#222}',""]),e.exports=t},"571d":function(e,t,a){var r=a("736a");"string"===typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);var n=a("5c0d").default;n("52a22a48",r,!0,{sourceMap:!1,shadowMode:!1})},6204:function(e,t,a){"use strict";var r=a("571d"),n=a.n(r);n.a},"736a":function(e,t,a){var r=a("a1a8");t=r(!1),t.push([e.i,".he-no-content-yet[data-v-6aff7a2e]{font-size:%?26?%;font-family:PingFang SC;font-weight:500;color:#999;line-height:1.3;text-align:center;margin-top:%?40?%;position:relative;z-index:1}.card[data-v-6aff7a2e]{background:#fff;border-radius:%?16?%;width:95%;margin:0 auto;padding-bottom:%?100?%}.he-empty__image[data-v-6aff7a2e]{width:%?320?%;height:%?320?%}",""]),e.exports=t},7423:function(e,t,a){"use strict";var r=a("e3bf"),n=a("aacd").map,o=a("8907"),i=o("map");r({target:"Array",proto:!0,forced:!i},{map:function(e){return n(this,e,arguments.length>1?arguments[1]:void 0)}})},"765d":function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.transformPageHeaders=n,a("cf00"),a("cd8f"),a("5eab"),a("7423"),a("ba85");var r=["X-PAGINATION-TOTAL-COUNT","X-PAGINATION-PER-PAGE","X-PAGINATION-PAGE-COUNT","X-PAGINATION-CURRENT-PAGE"];function n(e){Object.keys(e.headers).forEach((function(t){e.headers[t.toUpperCase()]=e.headers[t],delete e.headers[t]}));var t={current:1,pageCount:1,totalCount:1};r.forEach((function(t){Object.keys(e.headers).map((function(a){t===a&&(e.headers[a]=parseInt(e.headers[a]))}))})),t.current=e.headers["X-PAGINATION-CURRENT-PAGE"],t.pageCount=e.headers["X-PAGINATION-PAGE-COUNT"],t.totalCount=e.headers["X-PAGINATION-TOTAL-COUNT"],e.pagination=t}},a9da:function(e,t,a){"use strict";var r;a.d(t,"b",(function(){return n})),a.d(t,"c",(function(){return o})),a.d(t,"a",(function(){return r}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("v-uni-view",{staticClass:"he-page-content",attrs:{"data-theme":e.theme}},[a("v-uni-view",{staticClass:"he-search"},[a("v-uni-view",{staticClass:"flex he-switch"},[a("v-uni-view",{staticClass:"flex-sub",class:{active:"all"===e.keyword.time_type},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.switchTime("all")}}},[e._v("全部")]),a("v-uni-view",{staticClass:"flex-sub",class:{active:"today"===e.keyword.time_type},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.switchTime("today")}}},[e._v("今日")]),a("v-uni-view",{staticClass:"flex-sub",class:{active:"yesterday"===e.keyword.time_type},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.switchTime("yesterday")}}},[e._v("昨日")]),a("v-uni-view",{staticClass:"flex-sub",class:{active:"month"===e.keyword.time_type},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.switchTime("month")}}},[e._v("本月")])],1),a("v-uni-button",{staticClass:"cu-btn he-customize flex align-center justify-between",class:{"select-time":e.timeSelect},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.routerTimePeriod.apply(void 0,arguments)}}},[e.timeSelect?a("v-uni-text",[e._v(e._s(e.keyword.time_start)+" 至 "+e._s(e.keyword.time_end))]):a("v-uni-text",[e._v("自定义")]),a("v-uni-text",{staticClass:"iconfont iconbtn_arrow"})],1)],1),a("v-uni-view",{staticClass:"he-body"},[a("v-uni-view",{staticClass:"he-order--total"},[e._v("共"+e._s(e.count.all_order_number)+"笔订单")]),a("v-uni-view",{staticClass:"flex justify-between he-total"},[a("v-uni-view",{staticClass:"he-price--total"},[a("v-uni-text",{staticClass:"he-label"},[e._v("待结算佣金")]),a("v-uni-text",{staticClass:"he-value"},[e._v("￥"+e._s(e.count.wait_account))])],1),a("v-uni-view",{staticClass:"he-price--total"},[a("v-uni-text",{staticClass:"he-label"},[e._v("已结算佣金")]),a("v-uni-text",{staticClass:"he-value"},[e._v("￥"+e._s(e.count.commission_amount))])],1)],1),e._l(e.list,(function(t){return a("v-uni-view",{key:t.id,staticClass:"he-order--item he-card"},[a("v-uni-view",{staticClass:"flex justify-between align-start"},[a("v-uni-view",{staticClass:"flex flex-direction"},[a("v-uni-view",{staticClass:"he-user flex align-center"},[a("he-image",{attrs:{"image-style":{borderRadius:"50%"},width:"48",height:"48",src:t.promoterOrder.user.avatar}}),a("v-uni-text",{staticClass:"he-user--name"},[e._v(e._s(t.promoterOrder.user.nickname))])],1),a("v-uni-text",{staticClass:"he-order--sn"},[e._v("订单号:"+e._s(t.promoterOrder.order_sn))])],1),a("v-uni-view",{staticClass:"he-sign"},[e._v(e._s(e._f("filterStatus")(t.promoterOrder.status)))])],1),a("v-uni-view",{staticClass:"he-item--footer flex justify-between"},[a("v-uni-view",{staticClass:"he-item--price"},[a("v-uni-text",{staticClass:"he-label"},[e._v(e._s(1===t.promoterOrder.count_rules?"商品金额":"利润金额")+":")]),a("v-uni-text",{staticClass:"he-value"},[e._v("￥"+e._s(1===t.promoterOrder.count_rules?t.promoterOrder.total_amount:t.promoterOrder.profits_amount))])],1),a("v-uni-view",{staticClass:"he-item--price"},[a("v-uni-text",{staticClass:"he-label"},[e._v("商品佣金:")]),a("v-uni-text",{staticClass:"he-value"},[e._v("￥"+e._s(t.commission))])],1)],1)],1)})),e.list.length>0?a("he-load-more",{attrs:{status:e.loadStatus}}):e._e(),a("v-uni-view",{staticClass:"safe-area-inset-bottom"})],2),e.isNothing?a("he-no-content-yet",{attrs:{image:e.ipAddress+"/order-background-empty.png",text:"暂无相关订单"}}):e._e(),a("he-float-window",{attrs:{"pages-url":"promoter"}})],1)},o=[]},aa66:function(e,t,a){"use strict";a.r(t);var r=a("d827"),n=a("e1cb");for(var o in n)["default"].indexOf(o)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(o);a("6204");var i,c=a("5d80"),s=Object(c["a"])(n["default"],r["b"],r["c"],!1,null,"6aff7a2e",null,!1,r["a"],i);t["default"]=s.exports},b5aa:function(e,t){!function(t){"use strict";var a,r=Object.prototype,n=r.hasOwnProperty,o="function"===typeof Symbol?Symbol:{},i=o.iterator||"@@iterator",c=o.asyncIterator||"@@asyncIterator",s=o.toStringTag||"@@toStringTag",f="object"===typeof e,h=t.regeneratorRuntime;if(h)f&&(e.exports=h);else{h=t.regeneratorRuntime=f?e.exports:{},h.wrap=y;var u="suspendedStart",d="suspendedYield",l="executing",m="completed",p={},v={};v[i]=function(){return this};var g=Object.getPrototypeOf,_=g&&g(g(S([])));_&&_!==r&&n.call(_,i)&&(v=_);var w=P.prototype=x.prototype=Object.create(v);k.prototype=w.constructor=P,P.constructor=k,P[s]=k.displayName="GeneratorFunction",h.isGeneratorFunction=function(e){var t="function"===typeof e&&e.constructor;return!!t&&(t===k||"GeneratorFunction"===(t.displayName||t.name))},h.mark=function(e){return Object.setPrototypeOf?Object.setPrototypeOf(e,P):(e.__proto__=P,s in e||(e[s]="GeneratorFunction")),e.prototype=Object.create(w),e},h.awrap=function(e){return{__await:e}},C(z.prototype),z.prototype[c]=function(){return this},h.AsyncIterator=z,h.async=function(e,t,a,r){var n=new z(y(e,t,a,r));return h.isGeneratorFunction(t)?n:n.next().then((function(e){return e.done?e.value:n.next()}))},C(w),w[s]="Generator",w[i]=function(){return this},w.toString=function(){return"[object Generator]"},h.keys=function(e){var t=[];for(var a in e)t.push(a);return t.reverse(),function a(){while(t.length){var r=t.pop();if(r in e)return a.value=r,a.done=!1,a}return a.done=!0,a}},h.values=S,T.prototype={constructor:T,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=a,this.done=!1,this.delegate=null,this.method="next",this.arg=a,this.tryEntries.forEach(N),!e)for(var t in this)"t"===t.charAt(0)&&n.call(this,t)&&!isNaN(+t.slice(1))&&(this[t]=a)},stop:function(){this.done=!0;var e=this.tryEntries[0],t=e.completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var t=this;function r(r,n){return c.type="throw",c.arg=e,t.next=r,n&&(t.method="next",t.arg=a),!!n}for(var o=this.tryEntries.length-1;o>=0;--o){var i=this.tryEntries[o],c=i.completion;if("root"===i.tryLoc)return r("end");if(i.tryLoc<=this.prev){var s=n.call(i,"catchLoc"),f=n.call(i,"finallyLoc");if(s&&f){if(this.prev<i.catchLoc)return r(i.catchLoc,!0);if(this.prev<i.finallyLoc)return r(i.finallyLoc)}else if(s){if(this.prev<i.catchLoc)return r(i.catchLoc,!0)}else{if(!f)throw new Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return r(i.finallyLoc)}}}},abrupt:function(e,t){for(var a=this.tryEntries.length-1;a>=0;--a){var r=this.tryEntries[a];if(r.tryLoc<=this.prev&&n.call(r,"finallyLoc")&&this.prev<r.finallyLoc){var o=r;break}}o&&("break"===e||"continue"===e)&&o.tryLoc<=t&&t<=o.finallyLoc&&(o=null);var i=o?o.completion:{};return i.type=e,i.arg=t,o?(this.method="next",this.next=o.finallyLoc,p):this.complete(i)},complete:function(e,t){if("throw"===e.type)throw e.arg;return"break"===e.type||"continue"===e.type?this.next=e.arg:"return"===e.type?(this.rval=this.arg=e.arg,this.method="return",this.next="end"):"normal"===e.type&&t&&(this.next=t),p},finish:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var a=this.tryEntries[t];if(a.finallyLoc===e)return this.complete(a.completion,a.afterLoc),N(a),p}},catch:function(e){for(var t=this.tryEntries.length-1;t>=0;--t){var a=this.tryEntries[t];if(a.tryLoc===e){var r=a.completion;if("throw"===r.type){var n=r.arg;N(a)}return n}}throw new Error("illegal catch attempt")},delegateYield:function(e,t,r){return this.delegate={iterator:S(e),resultName:t,nextLoc:r},"next"===this.method&&(this.arg=a),p}}}function y(e,t,a,r){var n=t&&t.prototype instanceof x?t:x,o=Object.create(n.prototype),i=new T(r||[]);return o._invoke=O(e,a,i),o}function b(e,t,a){try{return{type:"normal",arg:e.call(t,a)}}catch(r){return{type:"throw",arg:r}}}function x(){}function k(){}function P(){}function C(e){["next","throw","return"].forEach((function(t){e[t]=function(e){return this._invoke(t,e)}}))}function z(e){function t(a,r,o,i){var c=b(e[a],e,r);if("throw"!==c.type){var s=c.arg,f=s.value;return f&&"object"===typeof f&&n.call(f,"__await")?Promise.resolve(f.__await).then((function(e){t("next",e,o,i)}),(function(e){t("throw",e,o,i)})):Promise.resolve(f).then((function(e){s.value=e,o(s)}),(function(e){return t("throw",e,o,i)}))}i(c.arg)}var a;function r(e,r){function n(){return new Promise((function(a,n){t(e,r,a,n)}))}return a=a?a.then(n,n):n()}this._invoke=r}function O(e,t,a){var r=u;return function(n,o){if(r===l)throw new Error("Generator is already running");if(r===m){if("throw"===n)throw o;return A()}a.method=n,a.arg=o;while(1){var i=a.delegate;if(i){var c=L(i,a);if(c){if(c===p)continue;return c}}if("next"===a.method)a.sent=a._sent=a.arg;else if("throw"===a.method){if(r===u)throw r=m,a.arg;a.dispatchException(a.arg)}else"return"===a.method&&a.abrupt("return",a.arg);r=l;var s=b(e,t,a);if("normal"===s.type){if(r=a.done?m:d,s.arg===p)continue;return{value:s.arg,done:a.done}}"throw"===s.type&&(r=m,a.method="throw",a.arg=s.arg)}}}function L(e,t){var r=e.iterator[t.method];if(r===a){if(t.delegate=null,"throw"===t.method){if(e.iterator.return&&(t.method="return",t.arg=a,L(e,t),"throw"===t.method))return p;t.method="throw",t.arg=new TypeError("The iterator does not provide a 'throw' method")}return p}var n=b(r,e.iterator,t.arg);if("throw"===n.type)return t.method="throw",t.arg=n.arg,t.delegate=null,p;var o=n.arg;return o?o.done?(t[e.resultName]=o.value,t.next=e.nextLoc,"return"!==t.method&&(t.method="next",t.arg=a),t.delegate=null,p):o:(t.method="throw",t.arg=new TypeError("iterator result is not an object"),t.delegate=null,p)}function E(e){var t={tryLoc:e[0]};1 in e&&(t.catchLoc=e[1]),2 in e&&(t.finallyLoc=e[2],t.afterLoc=e[3]),this.tryEntries.push(t)}function N(e){var t=e.completion||{};t.type="normal",delete t.arg,e.completion=t}function T(e){this.tryEntries=[{tryLoc:"root"}],e.forEach(E,this),this.reset(!0)}function S(e){if(e){var t=e[i];if(t)return t.call(e);if("function"===typeof e.next)return e;if(!isNaN(e.length)){var r=-1,o=function t(){while(++r<e.length)if(n.call(e,r))return t.value=e[r],t.done=!1,t;return t.value=a,t.done=!0,t};return o.next=o}}return{next:A}}function A(){return{value:a,done:!0}}}(function(){return this||"object"===typeof self&&self}()||Function("return this")())},d05c:function(e,t,a){"use strict";a.r(t);var r=a("a9da"),n=a("3a9d");for(var o in n)["default"].indexOf(o)<0&&function(e){a.d(t,e,(function(){return n[e]}))}(o);a("2118");var i,c=a("5d80"),s=Object(c["a"])(n["default"],r["b"],r["c"],!1,null,"f5a26ff8",null,!1,r["a"],i);t["default"]=s.exports},d1c3:function(e,t,a){var r=a("536b");"string"===typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);var n=a("5c0d").default;n("999c56e2",r,!0,{sourceMap:!1,shadowMode:!1})},d827:function(e,t,a){"use strict";var r;a.d(t,"b",(function(){return n})),a.d(t,"c",(function(){return o})),a.d(t,"a",(function(){return r}));var n=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("v-uni-view",{staticClass:"he-no-content-yet",class:e.type},[a("v-uni-image",{staticClass:"he-empty__image",attrs:{src:e.newImage}}),a("v-uni-view",[e._v(e._s(e.text))])],1)},o=[]},e1cb:function(e,t,a){"use strict";a.r(t);var r=a("2727"),n=a.n(r);for(var o in r)["default"].indexOf(o)<0&&function(e){a.d(t,e,(function(){return r[e]}))}(o);t["default"]=n.a},e9ff:function(e,t,a){"use strict";function r(e,t,a,r,n,o,i){try{var c=e[o](i),s=c.value}catch(f){return void a(f)}c.done?t(s):Promise.resolve(s).then(r,n)}function n(e){return function(){var t=this,a=arguments;return new Promise((function(n,o){var i=e.apply(t,a);function c(e){r(i,n,o,c,s,"next",e)}function s(e){r(i,n,o,c,s,"throw",e)}c(void 0)}))}}Object.defineProperty(t,"__esModule",{value:!0}),t.default=n,a("f9ee")},f9e4:function(e,t,a){"use strict";var r=a("99ca").default;Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0,a("b5aa"),a("c342"),a("3728"),a("2c7c");var n=r(a("e9ff")),o=r(a("4d92")),i=r(a("aa66")),c=a("45b6"),s=r(a("fe9c")),f={name:"order",components:{heLoadMore:o.default,heNoContentYet:i.default,heFloatWindow:s.default},data:function(){return{loadStatus:"loadmore",list:[],count:{all_order_number:0,commission_amount:"0.00",wait_account:"0.00"},isNothing:!1,page:{current:1},keyword:{time_type:"all",time_start:"",time_end:""}}},computed:{timeSelect:function(e){var t=e.keyword;return t.time_start&&t.time_end}},methods:{switchTime:function(e){var t=this;this.keyword.time_start="",this.keyword.time_end="",this.keyword.time_type=e,this.page.current=1,this.list=[],this.getCount(),this.getList().then((function(){if(t.$h.test.isEmpty(t.list))t.isNothing=!0;else{t.isNothing=!1;var e=t.page,a=e.current,r=e.pageCount;a===r&&(t.loadStatus="nomore")}}))},routerTimePeriod:function(){uni.navigateTo({url:"/pages/other/time-period"})},getList:function(){var e=this;return(0,n.default)(regeneratorRuntime.mark((function t(){var a,r,n,o;return regeneratorRuntime.wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.prev=0,a=JSON.parse(JSON.stringify(e.keyword)),a.time_start&&a.time_end&&(a.time_start=new Date(a.time_start.replace(/\./g,"/")).getTime()/1e3,a.time_end=new Date(a.time_end.replace(/\./g,"/")).getTime()/1e3),t.next=5,(0,c.promoterorderList)(e.page.current,a);case 5:r=t.sent,n=r.data,o=r.pagination,e.list=e.list.concat(n),e.page=o,t.next=13;break;case 11:t.prev=11,t.t0=t["catch"](0);case 13:case"end":return t.stop()}}),t,null,[[0,11]])})))()},getCount:function(){var e=this,t=JSON.parse(JSON.stringify(this.keyword));t.time_start&&t.time_end&&(t.time_start=new Date(t.time_start.replace(/\./g,"/")).getTime()/1e3,t.time_end=new Date(t.time_end.replace(/\./g,"/")).getTime()/1e3),(0,c.promoterOrderCount)(t).then((function(t){e.count=t}))}},onLoad:function(){var e=this;this.getCount(),this.getList().then((function(){if(e.$h.test.isEmpty(e.list))e.isNothing=!0;else{var t=e.page,a=t.current,r=t.pageCount;a===r&&(e.loadStatus="nomore")}}))},onShow:function(){var e=this,t=uni.getStorageSync(this.$storageKey.time_period);t&&(this.keyword.time_start=this.$h.timeFormat(new Date(t.startDate.replace(/\./g,"/")).getTime(),"yyyy.mm.dd"),this.keyword.time_end=this.$h.timeFormat(new Date(t.endDate.replace(/\./g,"/")).getTime(),"yyyy.mm.dd"),this.keyword.time_type="free",this.page.current=1,this.list=[],this.getCount(),this.getList().then((function(){if(e.$h.test.isEmpty(e.list))e.isNothing=!0;else{e.isNothing=!1;var t=e.page,a=t.current,r=t.pageCount;a===r&&(e.loadStatus="nomore")}})),uni.removeStorageSync(this.$storageKey.time_period))},onPullDownRefresh:function(){this.page.current=1,this.list=[],this.getCount(),this.getList(),setTimeout((function(){uni.stopPullDownRefresh()}),1e3)},onReachBottom:function(){var e=this;this.page.pageCount>this.page.current?(this.page.current++,this.loadStatus="loading",this.getList().then((function(){e.loadStatus="loadmore"}))):this.loadStatus="nomore"},filters:{filterStatus:function(e){return 0===e?"待结算":1===e?"已结算":-1===e?"已失效":void 0}}};t.default=f}}]);