<?php

namespace tekintian\geo_utils;

/**
 * 地图坐标转换工具
 *
 * WGS-84：是国际标准，GPS坐标（Google Earth使用、或者GPS模块）
 * GCJ-02：中国坐标偏移标准，Google Map、高德、腾讯使用
 * BD-09：百度坐标偏移标准，Baidu Map使用
 * Web Mercator 网络墨卡托投影坐标，Web Mercator是一个投影坐标系统，其基准面是 WGS 1984 ; 
 * 
 * 更多坐标信息请查看相关官方文档
 * 腾讯坐标 https://lbs.qq.com/webservice_v1/guide-convert.html
 * 百度坐标 http://lbsyun.baidu.com/index.php?title=webapi/guide/changeposition
 * 百度坐标拾取反查 http://api.map.baidu.com/lbsapi/getpoint/index.html
 * 高德坐标拾取反查 https://lbs.amap.com/console/show/picker
 * GPS坐标反查【需要翻墙】 http://geohash.org/
 * @Author: tekintian
 * @Date:   2019-01-25 14:36:30
 * @Last Modified 2019-01-25
 */
class GeoCoordinate
{
    private $PI = 3.14159265358979324;
    private $x_pi = 0;

    public function __construct()
    {
        $this->x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    }

    /**
     * GPS坐标转国测局火星坐标
     * WGS-84 to GCJ-02
     * @param  [type] $gps_lat [纬度]
     * @param  [type] $gps_lng [经度]
     * @return [type]         [description]
     */
    public function gpsToGcj02($gps_lat, $gps_lng) {
        //if ($this->outOfChina($gps_lat, $gps_lng))
        if (!$this->isInChina($gps_lat, $gps_lng))
            return array('lat' => $gps_lat, 'lng' => $gps_lng);

        $d = $this->delta($gps_lat, $gps_lng);
        return array('lat' => $gps_lat + $d['lat'],'lng' => $gps_lng + $d['lng']);
    }

   /**
    * 国测局火星坐标转GPS坐标
    * GCJ-02 to WGS-84
    * @param  [type] $gcj_lat [description]
    * @param  [type] $gcj_lng [description]
    * @return [type]         [description]
    */
    public function gcj02ToGps($gcj_lat, $gcj_lng) {
        //if ($this->outOfChina($gcj_lat, $gcj_lng))
        if (!$this->isInChina($gcj_lat, $gcj_lng))
            return array('lat' => $gcj_lat, 'lng' => $gcj_lng);
        
        $d = $this->delta($gcj_lat, $gcj_lng);
        return array('lat' => $gcj_lat - $d['lat'], 'lng' => $gcj_lng - $d['lng']);
    }
    /**
     * 国测局坐标转GPS坐标 精确算法
     * GCJ-02 to WGS-84 exactly
     * @param  [type] $gcj_lat [description]
     * @param  [type] $gcj_lng [description]
     * @return [type]         [description]
     */
    public function gcj02ToGpsExactly($gcj_lat, $gcj_lng) {
        $initDelta = 0.01;
        $threshold = 0.000000001;
        $dLat = $initDelta; $dLng = $initDelta;
        $mLat = $gcj_lat - $dLat; $mLon = $gcj_lng - $dLng;
        $pLat = $gcj_lat + $dLat; $pLon = $gcj_lng + $dLng;
        $gps_lat = 0; $gps_lng = 0; $i = 0;
        while (TRUE) {
            $gps_lat = ($mLat + $pLat) / 2;
            $gps_lng = ($mLon + $pLon) / 2;
            $tmp = $this->gpsToGcj02($gps_lat, $gps_lng);
            $dLat = $tmp['lat'] - $gcj_lat;
            $dLng = $tmp['lng'] - $gcj_lng;
            if ((abs($dLat) < $threshold) && (abs($dLng) < $threshold))
                break;

            if ($dLat > 0) $pLat = $gps_lat; else $mLat = $gps_lat;
            if ($dLng > 0) $pLon = $gps_lng; else $mLon = $gps_lng;

            if (++$i > 10000) break;
        }
        //console.log(i);
        return array('lat' => $gps_lat, 'lng'=> $gps_lng);
    }
    /**
     * 国测局坐标转百度坐标
     * GCJ-02 to BD-09
     * 中国正常坐标GCJ-02(火星，高德) 坐标转换成 BD-09(百度) 坐标
     * 腾讯地图用的也是GCJ02坐标
     * @param  [double] $gcj_lat [纬度]
     * @param  [double] $gcj_lng [经度]
     * @return [type]         [description]
     */
    public function gcj02ToBd09($gcj_lat, $gcj_lng) {
        $x = $gcj_lng; $y = $gcj_lat;  
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $this->x_pi);  
        $theta = atan2($y, $x) + 0.000003 * cos($x * $this->x_pi);  
        $bd_lng = $z * cos($theta) + 0.0065;  
        $bd_lat = $z * sin($theta) + 0.006; 
        return array('lat' => $bd_lat,'lng' => $bd_lng);
    }
    /**
     * 百度坐标转国测局坐标
     * BD-09 to GCJ-02
     * BD-09(百度) 坐标转换成  GCJ-02(火星，高德) 坐标
     * @param  [type] $bd_lat [百度纬度]
     * @param  [type] $bd_lng [百度经度]
     * @return [type]        [description]
     */
    public function bd09ToGcj02($bd_lat, $bd_lng)
    {
        $x = $bd_lng - 0.0065; $y = $bd_lat - 0.006;  
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $this->x_pi);  
        $theta = atan2($y, $x) - 0.000003 * cos($x * $this->x_pi);  
        $gcj_lng = $z * cos($theta);
        $gcj_lat = $z * sin($theta);
        return array('lat' => $gcj_lat, 'lng' => $gcj_lng);
    }
    /**
     * WGS-84 to Web mercator
     * $mercator_lat -> y $mercator_lng -> x
     * @param  [type] $gps_lat [description]
     * @param  [type] $gps_lng [description]
     * @return [type]         [description]
     */
    public function gpsToMercator($gps_lat, $gps_lng)
    {
        $x = $gps_lng * 20037508.34 / 180.;
        $y = log(tan((90. + $gps_lat) * $this->PI / 360.)) / ($this->PI / 180.);
        $y = $y * 20037508.34 / 180.;
        return array('lat' => $y, 'lng' => $x);
        /*
        if ((abs($gps_lng) > 180 || abs($gps_lat) > 90))
            return NULL;
        $x = 6378137.0 * $gps_lng * 0.017453292519943295;
        $a = $gps_lat * 0.017453292519943295;
        $y = 3189068.5 * log((1.0 + sin($a)) / (1.0 - sin($a)));
        return array('lat' => $y, 'lng' => $x);
        //*/
    }
    /**
     * 
     * Web mercator to WGS-84
     * $mercator_lat -> y $mercator_lng -> x
     * @param  [type] $mercator_lat [description]
     * @param  [type] $mercator_lng [description]
     * @return [type]              [description]
     */
    public function mercatorToGps($mercator_lat, $mercator_lng)
    {
        $x = $mercator_lng / 20037508.34 * 180.;
        $y = $mercator_lat / 20037508.34 * 180.;
        $y = 180 / $this->PI * (2 * atan(exp($y * $this->PI / 180.)) - $this->PI / 2);
        return array('lat' => $y, 'lng' => $x);
        /*
        if (abs($mercator_lng) < 180 && abs($mercator_lat) < 90)
            return NULL;
        if ((abs($mercator_lng) > 20037508.3427892) || (abs($mercator_lat) > 20037508.3427892))
            return NULL;
        $a = $mercator_lng / 6378137.0 * 57.295779513082323;
        $x = $a - (floor((($a + 180.0) / 360.0)) * 360.0);
        $y = (1.5707963267948966 - (2.0 * atan(exp((-1.0 * $mercator_lat) / 6378137.0)))) * 57.295779513082323;
        return array('lat' => $y, 'lng' => $x);
        //*/
    }
    /**
     * two point's distance
     * @param  [type] $lat1 [纬度]
     * @param  [type] $lng1 [经度]
     * @param  [type] $lat2 [description]
     * @param  [type] $lng2 [description]
     * @return [type]       [description]
     */
    public function distance($lat1, $lng1, $lat2, $lng2)
    {
        $earthR = 6371000.;
        $x = cos($lat1 * $this->PI / 180.) * cos($lat2 * $this->PI / 180.) * cos(($lng1 - $lng2) * $this->PI / 180);
        $y = sin($lat1 * $this->PI / 180.) * sin($lat2 * $this->PI / 180.);
        $s = $x + $y;
        if ($s > 1) $s = 1;
        if ($s < -1) $s = -1;
        $alpha = acos($s);
        $distance = $alpha * $earthR;
        return $distance;
        /*
        $earthRadius = 6367000; //approximate radius of earth in meters  

        $lat1 = ($lat1 * $this->PI ) / 180;  
        $lng1 = ($lng1 * $this->PI ) / 180;  

        $lat1 = ($lat1 * $this->PI ) / 180;  
        $lng2 = ($lng2 * $this->PI ) / 180;  


        $calcLongitude = $lng2 - $lng1;  
        $calcLatitude = $lat1 - $lat1;  
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat1) * pow(sin($calcLongitude / 2), 2);    
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));  
        $calculatedDistance = $earthRadius * $stepTwo;  

        return round($calculatedDistance);  
         */
    }
    /**
     * [delta description]
     * @param  [type] $lat [description]
     * @param  [type] $lng [description]
     * @return [type]      [description]
     */
    private function delta($lat, $lng)
    {
        // Krasovsky 1940
        //
        // a = 6378245.0, 1/f = 298.3
        // b = a * (1 - f)
        // ee = (a^2 - b^2) / a^2;
        $a = 6378245.0;//  a: 卫星椭球坐标投影到平面地图坐标系的投影因子。
        $ee = 0.00669342162296594323;//  ee: 椭球的偏心率。
        $dLat = $this->transformLat($lng - 105.0, $lat - 35.0);
        $dLng = $this->transformLng($lng - 105.0, $lat - 35.0);
        $radLat = $lat / 180.0 * $this->PI;
        $magic = sin($radLat);
        $magic = 1 - $ee * $magic * $magic;
        $sqrtMagic = sqrt($magic);
        $dLat = ($dLat * 180.0) / (($a * (1 - $ee)) / ($magic * $sqrtMagic) * $this->PI);
        $dLng = ($dLng * 180.0) / ($a / $sqrtMagic * cos($radLat) * $this->PI);
        return array('lat' => $dLat, 'lng' => $dLng);
    }
    /**
     * [rectangle description]
     * @param  [type] $lng1 [description]
     * @param  [type] $lat1 [description]
     * @param  [type] $lng2 [description]
     * @param  [type] $lat2 [description]
     * @return [type]       [description]
     */
    private function rectangle($lng1, $lat1, $lng2, $lat2) {
        return array(
            'west' => min($lng1, $lng2),
            'north' => max($lat1, $lat2),
            'east' => max($lng1, $lng2),
            'south' => min($lat1, $lat2),
        );
    }
    /**
     * [isInRect description]
     * @param  [type]  $rect [description]
     * @param  [type]  $lng  [description]
     * @param  [type]  $lat  [description]
     * @return boolean       [description]
     */
    private function isInRect($rect, $lng, $lat) {
        return $rect['west'] <= $lng && $rect['east'] >= $lng && $rect['north'] >= $lat && $rect['south'] <= $lat;
    }
    /**
     * [isInChina description]
     * @param  [type]  $lat [description]
     * @param  [type]  $lng [description]
     * @return boolean      [description]
     */
    private function isInChina($lat, $lng) {
        //China region - raw data
        //http://www.cnblogs.com/Aimeast/archive/2012/08/09/2629614.html
        $region = array(
            $this->rectangle(79.446200, 49.220400, 96.330000,42.889900),
            $this->rectangle(109.687200, 54.141500, 135.000200, 39.374200),
            $this->rectangle(73.124600, 42.889900, 124.143255, 29.529700),
            $this->rectangle(82.968400, 29.529700, 97.035200, 26.718600),
            $this->rectangle(97.025300, 29.529700, 124.367395, 20.414096),
            $this->rectangle(107.975793, 20.414096, 111.744104, 17.871542),
        );

        //China excluded region - raw data
        $exclude = array(
            $this->rectangle(119.921265, 25.398623, 122.497559, 21.785006),
            $this->rectangle(101.865200, 22.284000, 106.665000, 20.098800),
            $this->rectangle(106.452500, 21.542200, 108.051000, 20.487800),
            $this->rectangle(109.032300, 55.817500, 119.127000, 50.325700),
            $this->rectangle(127.456800, 55.817500, 137.022700, 49.557400),
            $this->rectangle(131.266200, 44.892200, 137.022700, 42.569200),
        );
        for ($i = 0; $i < count($region); $i++)
            if ($this->isInRect($region[$i], $lng, $lat))
            {
                for ($j = 0; $j<count($exclude); $j++)
                    if ($this->isInRect($exclude[$j], $lng, $lat))
                        return false;
                return true;
            }
        return false;
    }
    /**
     * [outOfChina description]
     * @param  [type] $lat [description]
     * @param  [type] $lng [description]
     * @return [type]      [description]
     */
    private function outOfChina($lat, $lng)
    {
        if ($lng < 72.004 || $lng > 137.8347)
            return TRUE;
        if ($lat < 0.8293 || $lat > 55.8271)
            return TRUE;
        return FALSE;
    }
    /**
     * [transformLat description]
     * @param  [type] $x [description]
     * @param  [type] $y [description]
     * @return [type]    [description]
     */
    private function transformLat($x, $y) {
        $ret = -100.0 + 2.0 * $x + 3.0 * $y + 0.2 * $y * $y + 0.1 * $x * $y + 0.2 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * $this->PI) + 20.0 * sin(2.0 * $x * $this->PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($y * $this->PI) + 40.0 * sin($y / 3.0 * $this->PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($y / 12.0 * $this->PI) + 320 * sin($y * $this->PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }
    /**
     * [transformLng description]
     * @param  [type] $x [description]
     * @param  [type] $y [description]
     * @return [type]    [description]
     */
    private function transformLng($x, $y) {
        $ret = 300.0 + $x + 2.0 * $y + 0.1 * $x * $x + 0.1 * $x * $y + 0.1 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * $this->PI) + 20.0 * sin(2.0 * $x * $this->PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($x * $this->PI) + 40.0 * sin($x / 3.0 * $this->PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($x / 12.0 * $this->PI) + 300.0 * sin($x / 30.0 * $this->PI)) * 2.0 / 3.0;
        return $ret;
    }

}