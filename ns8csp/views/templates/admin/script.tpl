{*
*  @author    NS8.com <support@ns8.com>
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @copyright 2018 NS8.com
*}

{literal}
<script>
  if (!Aggregator){var Aggregator=function(o){
    var d=document,p=Aggregator.prototype;this.options=o;this.q=[];d.cookie='__na_c=1';p.p=function(c){return function(){
    this.q.push([c,arguments])}};p.setPerson=p.p(4,arguments);p.logEvent=p.p(0,arguments);p.logPageview=p.p(1,arguments);
    p.ready=p.p(2,arguments);p.logOutbound=p.p(3,arguments);p.updatePerson=p.p(5,arguments);p.updateSession=p.p(6,arguments);
    p.updateEvent=p.p(7,arguments);p.push=p.p(8,arguments);var s=d.createElement('script');s.type='text/javascript';
    s.async=true;(function(i){s.onreadystatechange=function(){if(s.readyState=='loaded'||s.readyState=='complete'){i.run();}};
    s.onload=function(){i.run();}})(this);e=location.protocol=='https:';s.src='http'+(e?'s://':'://')+
    (e&&navigator.userAgent.indexOf('MSIE')>-1?'a-{0}.ns8ds.com':'a-{0}.cdn.ns8ds.com').replace('{0}',o.projectId)+
    '/web?t='+Math.floor((new Date()*.00001)/36);var e=d.getElementsByTagName('script')[0];e.parentNode.insertBefore(s,e);
}}
{/literal}

  var ns8protect = new Aggregator({ldelim}
    "timing": true,
    "protect": true,
    "projectId": "{$projectId|escape:'quotes':'UTF-8'}",
    "hostId": "{$shopId|escape:'quotes':'UTF-8'}"
    {rdelim});
  ns8protect.logPageview();
</script>