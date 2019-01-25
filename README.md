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
$geo_coordinate = new GeoCoordinate();

// GPS坐标转国测局火星坐标
echo $geo_coordinate->gpsToGcj02(25.11624,102.75205);

// 国测局火星坐标转GPS坐标
echo $geo_coordinate->gcj02ToGps(39.114347,116.82339);

~~~



## sources
 * 更多坐标信息请查看相关官方文档
 * 腾讯坐标 https://lbs.qq.com/webservice_v1/guide-convert.html
 * 百度坐标 http://lbsyun.baidu.com/index.php?title=webapi/guide/changeposition