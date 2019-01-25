<?php

namespace tekintian\geo_utils;

/**
 * 根据地理坐标获取国家、省份、城市，及周边数据类(利用百度Geocoding API实现)
 * 
 * https://restapi.amap.com/v3/geocode/regeo?key=您的key&location=116.481488,39.990464&poitype=&radius=1000&extensions=base&batch=false&roadlevel=0
 * 
 * Public  getGeoInfo 根据地址获取国家、省份、城市及周边数据
 * Private toCurl              使用curl调用API
 * @see https://lbs.amap.com/api/webservice/guide/api/georegeo
 * @Author: Tekin
 * @Date:   2019-01-25 21:53:42
 * @Last Modified 2019-01-25
 * @Last Modified time: 2019-01-25 23:56:40
 */
class Cjc02GeoCode
{
	/**
	 * [getGeoInfo description]
	 * @param  [type]  $key        [高德Key]
	 * @param  [type]  $location   [经纬度坐标  116.481488,39.990464 ]
	 * @param  string  $poitype    [返回附近POI类型]
	 * @param  integer $radius     [搜索半径 radius取值范围在0~3000，默认是1000。单位：米]
	 * @param  string  $extensions [extensions 参数默认取值是 base，也就是返回基本地址信息；extensions 参数取值为 all 时会返回基本地址信息、附近 POI 内容、道路信息以及道路交叉口信息。]
	 * @param  boolean $batch      [批量查询控制]
	 * @param  integer $roadlevel  [道路等级 当roadlevel=0时，显示所有道路 当roadlevel=1时，过滤非主干道路，仅输出主干道路数据]
	 * @param  string $output  [可选输入内容包括：JSON，XML。设置 JSON 返回结果数据将会以JSON结构构成；如果设置 XML 返回结果数据将以 XML 结构构成]
	 * @return [type]              [description]
	 */
	public static function getGeoInfo($key,$location,$poitype='',$radius=1000,$extensions='base',$batch=false,$roadlevel=0,$output='json')
	{
		$url='https://restapi.amap.com/v3/geocode/regeo';
		$param = array(
                'key' => $key,
                'location' => $location,
                'poitype' => $poitype,
                'radius' => $radius,
                'extensions' => $extensions,
                'batch' => $batch,
                'roadlevel' => $roadlevel,
                'output' => $output
        );
        // 请求百度api
        $response = self::getCurl($url, $param);
        $result = array(); 
        if($response){
            $result = json_decode($response, true);
        }
        return $result;
	}
	/**
     * 使用curl调用百度Geocoding API
     * @param  String $url    请求的地址
     * @param  Array  $param  请求的参数
     * @return JSON
     */
    private static function getCurl($url, $param=array()){

        $ch = curl_init();

        if(substr($url,0,5)=='https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));

        $response = curl_exec($ch);

        if($error=curl_error($ch)){
            return false;
        }

        curl_close($ch);

        return $response;
    }
}