(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-98842c66"],{"2de1":function(i,t,e){"use strict";e.r(t);var l,o,a=e("5530"),s=e("2638"),r=e.n(s),n=(e("5319"),e("ac1f"),e("a9e3"),e("841c"),e("2f62")),m=Object(n["createNamespacedHelpers"])("setting"),c=m.mapMutations,d={data:function(){var i=this,t=function(t,e,l){i.form.is_pic_limit||e||l("请输入图片大小限制"),l()},e=function(t,e,l){i.form.is_video_limit||e||l("请输入视频大小限制"),l()};return{loading:!0,submitLoading:!1,form:{is_pic_limit:!1,pic_limit:2,is_video_limit:!1,video_limit:5},rules:{pic_limit:[{required:!0,validator:t}],video_limit:[{required:!0,validator:e}]}}},render:function(){var i=this,t=arguments[0],e=this;return t("div",{class:"le-main"},[t("el-form",r()([{class:"le-card",ref:"ruleForm"},{props:{model:this.form,rules:this.rules,labelWidth:"190px"}},{directives:[{name:"loading",value:this.loading||this.submitLoading}]}]),[t("div",{class:"le-prompt"},["温馨提示：为了保障服务器性能及用户体验，请谨慎填写限制！此处的限制需要与服务器的限制相匹配，否则将无法生效。"]),t("el-form-item",{attrs:{label:"图片大小限制",prop:"pic_limit"}},[t("el-radio-group",r()([{},{on:{change:function(i){i&&(e.form.pic_limit=null)}}},{model:{value:i.form.is_pic_limit,callback:function(t){i.$set(i.form,"is_pic_limit",t)}}}]),[t("el-radio",{attrs:{label:!1}},["限制"]),t("el-radio",{attrs:{label:!0}},["无限制"])]),t("div",{class:"le-form-input"},[t("el-input",r()([{attrs:{maxlength:10}},{on:{input:function(i){i=i.replace(/\D|^0/g,""),e.form.pic_limit=i}}},{attrs:{value:this.form.pic_limit,disabled:this.form.is_pic_limit}}]),[t("template",{slot:"append"},["MB"])])])]),t("el-form-item",{attrs:{label:"视频大小限制",prop:"video_limit"}},[t("el-radio-group",r()([{},{on:{change:function(i){i&&(e.form.video_limit=null)}}},{model:{value:i.form.is_video_limit,callback:function(t){i.$set(i.form,"is_video_limit",t)}}}]),[t("el-radio",{attrs:{label:!1}},["限制"]),t("el-radio",{attrs:{label:!0}},["无限制"])]),t("div",{class:"le-form-input"},[t("el-input",r()([{attrs:{maxlength:10}},{on:{input:function(i){i=i.replace(/\D|^0/g,""),e.form.video_limit=i}}},{attrs:{value:this.form.video_limit,disabled:this.form.is_video_limit}}]),[t("template",{slot:"append"},["MB"])])])])]),t("div",{class:"le-cardpin"},[t("el-button",{attrs:{type:"primary",disabled:this.loading,loading:this.submitLoading},on:{click:this.submit}},["保存"])])])},mounted:function(){this.getSetting()},methods:Object(a["a"])(Object(a["a"])({},c(["setStorage"])),{},{submit:function(){var i=this;this.$refs["ruleForm"].validate((function(t){if(!t)return!1;var e=JSON.parse(JSON.stringify(i.form));e.is_pic_limit&&(e.pic_limit=-1),e.is_video_limit&&(e.video_limit=-1),e.pic_limit=Number(e.pic_limit),e.video_limit=Number(e.video_limit),delete e.is_pic_limit,delete e.is_video_limit,i.submitLoading=!0,i.$heshop.setting("post",{keyword:"storage_limit",content:e}).then((function(){i.submitLoading=!1,i.setStorage(e),i.$message.success("保存成功")})).catch((function(t){i.submitLoading=!1,i.$message.error(t.data.message)}))}))},getSetting:function(){var i=this;this.$heshop.search("post",{include:"setting"},{keyword:"storage_limit"}).then((function(t){var e=t.content,l=e.pic_limit,o=e.video_limit;-1===l?i.form.is_pic_limit=!0:i.form.pic_limit=l,-1===o?i.form.is_video_limit=!0:i.form.video_limit=o,i.loading=!1})).catch((function(t){i.loading=!1,i.$message.error(t.data.message)}))}})},u=d,p=(e("9d47"),e("2877")),_=Object(p["a"])(u,l,o,!1,null,"4cdde00a",null);t["default"]=_.exports},"9d47":function(i,t,e){"use strict";e("a584")},a584:function(i,t,e){}}]);