#  地图坐标转换工具

WGS-84：是国际标准，GPS坐标（Google Earth使用、或者GPS模块）
GCJ-02：中国坐标偏移标准，Google Map、高德、腾讯使用
BD-09：百度坐标偏移标准，Baidu Map使用
Web Mercator 网络墨卡托投影坐标，Web Mercator是一个投影坐标系统，其基准面是 WGS 1984 ;  EPSG，即 European Petroleum Standards Group 欧洲石油标准组织


## 使用方法:

1. 载入本工具类

~~~shell
composer require tekintian/geo_utils
~~~

2. 使用本工具

~~~php
<?php

// 载入自动加载： 如果使用框架的话这个步骤可以忽略。
require_once __DIR__ . '/vendor/autoload.php';

use \tekintian\geo_utils\GeoCoordinate;

// 实例化工具
$geoc= new GeoCoordinate();

// GPS坐标转国测局火星坐标
echo $geoc->gpsToGcj02(25.11624,102.75205);

// 国测局火星坐标转GPS坐标
echo $geoc->gcj02ToGps(39.114347,116.82339);

echo "<hr>";
// 百度坐标 26.8807910,100.2284620 转国测局坐标
$gcj=$geoc->bd09ToGcj02(26.8807910,100.2284620);
echo "火星坐标：".$gcj['lat'].", ".$gcj['lng'];
echo "<hr>";
$gps=$geoc->gcj02ToGpsExactly($gcj['lat'],$gcj['lng']);
echo "<br>GPS坐标：".$gps['lat'].", ".$gps['lng'];

~~~

## sources
 * 更多坐标信息请查看相关官方文档
 * 腾讯坐标 https://lbs.qq.com/webservice_v1/guide-convert.html
 * 百度坐标 http://lbsyun.baidu.com/index.php?title=webapi/guide/changeposition
 * 百度坐标拾取反查 http://api.map.baidu.com/lbsapi/getpoint/index.html
 * 高德坐标拾取反查 https://lbs.amap.com/console/show/picker
 * GPS坐标反查【需要翻墙】 http://geohash.org/
 
