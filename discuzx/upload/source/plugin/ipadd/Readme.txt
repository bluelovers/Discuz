1、安装插件时会自动建立pre_common_lastip表，记录会员上次登录的IP地址

2、由于Discus!X2.0内置的读取IP的函数对GBK编码的IP数据库支持不太好（乱码），
   所以这里要修改一下源代码，如下：
    source\function\function_misc.php 的第212行：
    return '- '.$ipaddr;
修改为：
    return '- '.iconv('gbk', 'utf-8//IGNORE', $ipaddr);    //将gbk转换为utf8
注：如果使用原有IP数据库，则不用修改

3、在后台可以设置是否显示IP的具体登录地，1为显示，0为关闭

4、由于IP数据库比较大，此插件没有附带，还请自行下载（纯真IP数据库），放在source/plugin/ipadd/ipdata/下（命名为qqwry.dat）。强烈建议自己下载最新的，如果不存在此IP数据库，该插件将调用Discus!X2.0自带的IP数据库。