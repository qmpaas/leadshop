(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["promoter/promoter-index-detail"],{"1d75":function(e,t,i){"use strict";i("73b8")},"1deb":function(e,t,i){},"5a2a":function(e,t,i){"use strict";i("1deb")},"73b8":function(e,t,i){},"76fe":function(e,t,i){},8799:function(e,t,i){"use strict";i.r(t);var a,n,l,s,o,r,c,u,d=i("4c02"),f=i.n(d),m=i("46d3"),h=i("78e0"),p=i("b96e"),v=i("2233"),b=function(){function e(t,i){Object(h["a"])(this,e),Object(m["a"])(this,"myChart",void 0),this.init(t,i)}return Object(p["a"])(e,[{key:"init",value:function(e,t){var i,a=this,n=document.getElementById(e);this.myChart=v["a"](n),i={tooltip:{trigger:"item"},legend:{bottom:"5%",left:"center"},series:[{name:t,type:"pie",radius:["30%","60%"],label:{show:!1},emphasis:{label:{show:!1,fontSize:"40",fontWeight:"bold"}},center:["50%","45%"],data:[]}]},this.myChart.setOption(i),window.onresize=function(){a.myChart.resize()}}},{key:"setOptions",value:function(e){this.myChart.setOption({series:[{data:e}]})}}]),e}(),g=b,w=i("8572"),y=i("8121"),x=i("1c03"),O=(i("eb62"),i("f5bd"),i("fc2a"),i("0e28")),k={name:"modify-level",data:function(){return{list:[],form:{level:null},rules:{level:[{required:!0,message:"请选择分销商等级"}]}}},props:{value:{type:Boolean,default:!0},info:{type:[Object]}},computed:{showDialog:{get:function(e){var t=e.value;return t},set:function(e){this.$emit("input",e)}}},mounted:function(){var e=this;Object(O["x"])().then((function(t){e.list=t.map((function(t){var i=Object(x["a"])(Object(x["a"])({},t),{},{disabled:!1});return i.level===e.info.level&&(i.disabled=!0),i}))}))},render:function(){var e=this,t=arguments[0],i=this;return t("el-dialog",f()([{attrs:{visible:i.showDialog,title:"修改分销商等级",width:"456px",top:"30vh"}},{on:Object(m["a"])({},"update:visible",(function(e){i.showDialog=e}))}]),[t("el-form",f()([{},{props:{model:i.form,rules:i.rules}},{ref:"form",attrs:{"label-width":"140px"}}]),[t("el-form-item",{attrs:{label:"当前分销商等级"}},[t("span",{class:"level-title"},[i.info.level_name])]),t("el-form-item",{attrs:{label:"修改分销商等级",prop:"level"}},[t("el-select",{class:"le-input--200",model:{value:i.form.level,callback:function(t){e.$set(i.form,"level",t)}}},[i.list.map((function(e,i){return t("el-option",{key:i,attrs:{disabled:e.disabled,label:e.name,value:e.level}})}))])])]),t("div",{slot:"footer"},[t("el-button",{on:{click:function(){return i.showDialog=!1}}},["取消"]),t("el-button",{attrs:{type:"primary"},on:{click:i.submit}},["确定"])])])},methods:{submit:function(){var e=this;this.$refs["form"].validate((function(t){if(!t)return!1;Object(O["g"])(e.info.UID,e.form.level).then((function(){e.showDialog=!1;var t=e.list.find((function(t){return t.level===e.form.level}));e.info.level_name=t.name,e.info.level=t.level,e.form.level=null,e.$message({type:"success",message:"修改分销商等级成功"})}))}))}}},D=k,$=(i("f9d9"),i("4ac2")),j=Object($["a"])(D,a,n,!1,null,"11e71aea",null),_=j.exports,M={name:"modify-name",data:function(){return{realname:this.info.user.realname}},props:{value:{type:Boolean,default:!0},info:{type:[Object]}},computed:{showDialog:{get:function(e){var t=e.value;return t},set:function(e){this.$emit("input",e)}}},render:function(){var e=this,t=arguments[0],i=this;return t("el-dialog",f()([{attrs:{visible:i.showDialog,title:"改姓名",width:"360px",top:"30vh"}},{on:Object(m["a"])({},"update:visible",(function(e){i.showDialog=e}))}]),[t("el-form",{ref:"form",attrs:{"label-width":"140px"}},[t("el-input",{attrs:{maxlength:"20","show-word-limit":!0,placeholder:"请输入姓名"},class:"he-input",model:{value:i.realname,callback:function(t){e.$set(i,"realname",t)}}})]),t("div",{slot:"footer"},[t("el-button",{on:{click:function(){return i.showDialog=!1}}},["取消"]),t("el-button",{attrs:{type:"primary"},on:{click:i.submit}},["确定"])])])},methods:{submit:function(){var e=this;this.$heshop.users("put",{id:this.info.UID,behavior:"setting"},{realname:this.realname}).then((function(){e.info.user.realname=e.realname,e.showDialog=!1,e.$message.success("修改分销商姓名成功")})).catch((function(t){e.$message.error(t.data[0].message)}))}}},I=M,R=(i("1d75"),Object($["a"])(I,l,s,!1,null,"17d62d02",null)),T=R.exports,C={name:"modify-phone",data:function(){return{mobile:this.info.user.mobile}},props:{value:{type:Boolean,default:!0},info:{type:[Object]}},computed:{showDialog:{get:function(e){var t=e.value;return t},set:function(e){this.$emit("input",e)}}},render:function(){var e=this,t=arguments[0],i=this;return t("el-dialog",f()([{attrs:{visible:i.showDialog,title:"改手机号",width:"360px",top:"30vh"}},{on:Object(m["a"])({},"update:visible",(function(e){i.showDialog=e}))}]),[t("el-form",{ref:"form",attrs:{"label-width":"140px"}},[t("el-input",{attrs:{maxlength:"11","show-word-limit":!0,placeholder:"请输入姓名"},class:"he-input",model:{value:i.mobile,callback:function(t){e.$set(i,"mobile",t)}}})]),t("div",{slot:"footer"},[t("el-button",{on:{click:function(){return i.showDialog=!1}}},["取消"]),t("el-button",{attrs:{type:"primary"},on:{click:i.submit}},["确定"])])])},methods:{submit:function(){var e=this;this.$heshop.users("put",{id:this.info.UID,behavior:"setting"},{mobile:this.mobile}).then((function(){e.info.user.mobile=e.mobile,e.showDialog=!1,e.$message.success("修改分销商手机号成功")})).catch((function(t){e.$message.error(t.data.message)}))}}},L=C,E=(i("cc0e"),Object($["a"])(L,o,r,!1,null,"78ce1848",null)),N=E.exports,A={components:{clearOut:w["a"],agreeRefuseApply:y["a"],modifyLevel:_,modifyName:T,modifyPhone:N},render:function(){var e=this,t=arguments[0],i=this;return t("div",{class:"le-main",directives:[{name:"loading",value:i.loading}]},[t("el-breadcrumb",{attrs:{"separator-class":"el-icon-arrow-right"}},[t("el-breadcrumb-item",[t("a",{on:{click:this.routerBack}},["分销列表"])]),t("el-breadcrumb-item",["分销商详情"])]),t("div",{class:"le-card le-card-margin"},[t("div",{class:"le-card-head flex align-center"},[t("span",{class:"le-card-line"}),t("span",["基本信息"])]),t("div",{class:"le-basicinformation flex"},[t("div",{class:"le-information-one flex align-center flex-direction justify-center"},[t("div",[t("el-avatar",{attrs:{size:80,error:function(){return!0}}},[t("img",{attrs:{src:i.detail.user.avatar}})])]),t("span",{class:"le-name"},[i.detail.user.nickname]),t("span",{class:"le-id"},["ID:",i.detail.UID])]),t("div",{class:"le-information-two"},[t("p",{class:"le-information-item"},[t("span",["姓名: "]),t("span",[i.detail.user.realname?i.detail.user.realname:"--"]),t("i",{class:"le-icon le-icon-editor",on:{click:function(){return i.showModifyName=!0}}})]),t("p",{class:"le-information-item"},[t("span",["手机号: "]),t("span",[i.detail.user.mobile?i.detail.user.mobile:"--"]),t("i",{class:"le-icon le-icon-editor",on:{click:function(){return i.showModifyPhone=!0}}})]),t("p",{class:"le-information-item"},[t("span",["申请时间: "]),t("span",[i.detail.apply_time?this.$moment(new Date(1e3*i.detail.apply_time)).format("Y-MM-DD HH:mm:ss"):"--"])]),function(){if(4===i.detail.status)return t("p",{class:"le-information-item flex"},[t("span",["下线关系处理: "]),t("div",[function(){return i.detail.transfer_id?[["转移给指定分销商"],[t("el-button",{on:{click:function(){i.$router.push({path:"/promoter/index-detail/"+i.detail.transfer_id})}},class:"le-transfer",attrs:{type:"text"}},[i.detail.transfer_name])]]:"自由绑定分销商"}()])])}(),function(){if(2===i.detail.status)return t("p",{class:"le-information-item"},[t("span",["加入时间: "]),t("span",[i.detail.join_time?i.$moment(new Date(1e3*i.detail.join_time)).format("Y-MM-DD HH:mm:ss"):"--"])])}()]),t("div",{class:"le-information-three"},[t("p",{class:"le-information-item"},[t("span",["邀请方: "]),t("span",[2!==i.detail.status?"--":i.detail.invite_nickname?i.detail.invite_nickname:"官方邀请"])]),function(){if(2===i.detail.status)return t("p",{class:"le-information-item"},[t("span",["分销商等级: "]),t("span",[i.detail.level_name]),t("i",{class:"le-icon le-icon-editor",on:{click:function(){return i.showModifyLevel=!0}}})])}(),t("p",{class:"le-information-item"},[t("span",["状态: "]),t("span",[2===i.detail.status?"已通过":1===i.detail.status?"待审核":3===i.detail.status?"已拒绝":4===i.detail.status||-2===i.detail.status?"已清退":""])])])])]),function(){if(2===i.detail.status)return t("div",{class:"le-card le-card-margin"},[t("div",{class:"le-card-head flex align-center"},[t("span",{class:"le-card-line"}),t("span",["下线人数"])]),t("div",{class:"flex le-offlinenumber"},[function(){if(i.detail.children.first){var e=i.detail.children.first;return t("div",{class:"flex-sub",on:{click:i.routerOfflineList.bind(i,{type:1,detail:i.detail})}},[t("div",{class:"le-total-text"},["一级下线人数"]),t("div",{class:"le-total-number"},[e.ordinary+e.promoter]),t("div",{class:"le-charts",attrs:{id:"le-offline-first"}})])}}(),function(){if(e.detail.children.second){var a=i.detail.children.second;return t("div",{class:"flex-sub",on:{click:i.routerOfflineList.bind(i,{type:2,detail:i.detail})}},[t("div",{class:"le-total-text"},["二级下线人数"]),t("div",{class:"le-total-number"},[a.ordinary+a.promoter]),t("div",{class:"le-charts",attrs:{id:"le-offline-two"}})])}}(),function(){if(e.detail.children.third){var a=i.detail.children.third;return t("div",{class:"flex-sub",on:{click:i.routerOfflineList.bind(i,{type:3,detail:i.detail})}},[t("div",{class:"le-total-text"},["三级下线人数"]),t("div",{class:"le-total-number"},[a.ordinary+a.promoter]),t("div",{class:"le-charts",attrs:{id:"le-offline-three"}})])}}()])])}(),t("div",{class:"flex le-card-margin"},[t("div",{class:"le-card le-performance"},[t("div",{class:"le-card-head flex align-center"},[t("span",{class:"le-card-line"}),t("span",["业绩统计"])]),t("div",{class:"le-total-box flex align-center flex-direction justify-center"},[t("div",{class:"le-total-text"},["累计销售额"]),t("div",{class:"le-total-number"},[3!==i.detail.status?"¥"+i.detail.sales_amount:"--"])]),t("div",{class:"le-total-box flex align-center flex-direction justify-center"},[t("div",{class:"le-total-text"},["累计邀请分销商"]),t("div",{class:"le-total-number"},[3!==i.detail.status?i.detail.invite_number:"--"])])]),t("div",{class:"le-card le-commission"},[t("div",{class:"le-card-head flex align-center"},[t("span",{class:"le-card-line"}),t("span",["佣金统计"])]),t("div",{class:"le-content flex"},[t("div",{class:"le-data flex align-center flex-direction justify-center"},[t("div",{class:"le-total-text"},["累计佣金"]),t("div",{class:"le-total-number"},[3!==i.detail.status?"¥".concat(i.detail.all_commission_amount):"--"])]),t("div",{class:"le-chart",attrs:{id:"le-commission"}})])])]),function(){if(2===i.detail.status||1===i.detail.status)return t("div",{class:"le-cardpin"},[function(){return 2===i.detail.status?t("el-button",{on:{click:function(){i.showClearOut=!0,i.clearOutInfo={UID:i.detail.UID,mobile:i.detail.user.mobile,status:i.detail.status}}}},["清退"]):1===i.detail.status?[[t("el-button",{on:{click:i.agreeRefuse.bind(i,"refuse")}},["拒绝"])],[t("el-button",{attrs:{type:"primary"},on:{click:i.agreeRefuse.bind(i,"pass")}},["通过"])]]:void 0}()])}(),function(){if(i.showClearOut)return t(w["a"],{on:{confirm:function(e){var t=e.transferName;i.detail.status=4,i.detail.transfer_name=t}},attrs:{info:i.clearOutInfo},model:{value:i.showClearOut,callback:function(t){e.$set(i,"showClearOut",t)}}})}(),function(){if(i.showAgreeRefuse)return t(y["a"],{attrs:{type:i.agreeRefuseType,info:i.agreeRefuseInfo},model:{value:i.showAgreeRefuse,callback:function(t){e.$set(i,"showAgreeRefuse",t)}}})}(),function(){if(2===i.detail.status&&i.showModifyLevel)return t(_,f()([{attrs:{info:i.detail}},{on:Object(m["a"])({},"update:info",(function(e){i.detail=e}))},{model:{value:i.showModifyLevel,callback:function(t){e.$set(i,"showModifyLevel",t)}}}]))}(),function(){if(i.showModifyName)return t(T,f()([{attrs:{info:i.detail}},{on:Object(m["a"])({},"update:info",(function(e){i.detail=e}))},{model:{value:i.showModifyName,callback:function(t){e.$set(i,"showModifyName",t)}}}]))}(),function(){if(i.showModifyPhone)return t(N,f()([{attrs:{info:i.detail}},{on:Object(m["a"])({},"update:info",(function(e){i.detail=e}))},{model:{value:i.showModifyPhone,callback:function(t){e.$set(i,"showModifyPhone",t)}}}]))}()])},data:function(){return{detail:{user:{},children:{},all_commission_amount:"--",invite_number:"--",sales_amount:"--",status:-1},loading:!0,showClearOut:!1,clearOutInfo:null,showAgreeRefuse:!1,agreeRefuseInfo:{},agreeRefuseType:"",showModifyLevel:!1,showModifyName:!1,showModifyPhone:!1}},mounted:function(){var e=this.$route.params.id;this.getDetail(parseInt(e))},methods:{getDetail:function(e){var t=this;this.$heshop.promoter("get",e).then((function(e){t.detail=e,t.$nextTick((function(){t.commissionEchart();var i=t.detail.status;2===i&&(t.offlineFirstEchart(),e.children.second&&t.offlineTwoEchart(),e.children.second&&t.offlineThreeEchart())})),t.loading=!1})).catch((function(e){t.$message.error(e.data.message)}))},routerBack:function(){this.$router.back(-1)},commissionEchart:function(){var e=this,t=new g("le-commission","佣金统计");setTimeout((function(){t.setOptions([{value:e.detail.wait_account,name:"待结算佣金"},{value:e.detail.commission,name:"待提现佣金"},{value:e.detail.is_withdrawal,name:"已提现佣金"}])}),500)},offlineFirstEchart:function(){var e=new g("le-offline-first","下线人数"),t=this.detail.children.first;setTimeout((function(){e.setOptions([{value:t.promoter,name:"分销商"},{value:t.ordinary,name:"普通下线"}])}),500)},offlineTwoEchart:function(){var e=new g("le-offline-two","下线人数"),t=this.detail.children.second;setTimeout((function(){e.setOptions([{value:t.promoter,name:"分销商"},{value:t.ordinary,name:"普通下线"}])}),500)},offlineThreeEchart:function(){var e=new g("le-offline-three","下线人数"),t=this.detail.children.third;setTimeout((function(){e.setOptions([{value:t.promoter,name:"分销商"},{value:t.ordinary,name:"普通下线"}])}),500)},routerOfflineList:function(e){this.$router.push({name:"promoter-index-detail-list",path:"/promoter/index-detail-list",params:e})},agreeRefuse:function(e){this.agreeRefuseType=e,this.showAgreeRefuse=!0,this.agreeRefuseInfo=this.detail,this.agreeRefuseInfo.nickname=this.detail.user.nickname}}},B=A,P=(i("5a2a"),Object($["a"])(B,c,u,!1,null,"5c8fb260",null));t["default"]=P.exports},c5c7:function(e,t,i){},cc0e:function(e,t,i){"use strict";i("c5c7")},f9d9:function(e,t,i){"use strict";i("76fe")}}]);