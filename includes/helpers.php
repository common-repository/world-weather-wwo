<?php


function wwo_change_user_agent(){
	$agent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.237 Safari/534.10";
	return $agent;
}


if ( ! function_exists( 'wwo_fetch_wwo_weather' ) ) {
	function wwo_fetch_wwo_weather($state = '', $city = 'Prague', $extended = true, $api_key, $days, $debug) {
		if(!empty($state)){
			$country = ','.urlencode(strtolower( $state ));
		}else{
			$country = '';
		}
		// OLD API
		// $url = sprintf( 'http://free.worldweatheronline.com/feed/weather.ashx?q='.urlencode(strtolower( $city )).$country.'&format=json&num_of_days='.$days.'&key='.$api_key);

		// new API since 1.September 2013
		// http://api.worldweatheronline.com/free/v1/weather.ashx?q=prague&format=json&num_of_days=5&key=zu3ce3h2zt8edh9u9sn3qgwa
		$url = sprintf( 'http://api.worldweatheronline.com/free/v1/weather.ashx?q='.urlencode(strtolower( $city )).$country.'&format=json&num_of_days='.$days.'&key='.$api_key);
		
		// Change the user agent string (Fixes problem with results of some country/city locations not being returned)
		add_filter('http_headers_useragent', 'wwo_change_user_agent');

		// Collect the HTML file
		$response = wp_remote_request( $url );
		// viva JSON, XML sucks
		$obj = json_decode($response['body']);
		// define empty output
		$output = array();
		
		// if we fetched the data sucessfuly 
		// will put the wanted data into an array
		if(!empty($obj)){
			if($debug == 1){
echo '<pre><b>URL</b><br />
'.$url.'
<b>JSON</b><br />';
var_dump($obj);
echo '</pre>';
			}
			
			if(!isset($obj->data->error)){
				$actual_weather = ($obj->data->current_condition);
				$weather = ($obj->data->weather);
			
				// actual weather
				foreach($actual_weather as $val => $key){
					foreach($key as $v => $k){
						$wanted = array('temp_C', 'temp_F','weatherCode');
						if(in_array($v, $wanted)){
							$output['actual_weather'][$v] = $k;
						}
					}; 
				}
				// forecast
				$i = 0;
				foreach($weather as $val => $key){
					foreach($key as $v => $k){
						$wanted = array('tempMaxC', 'tempMaxF','tempMinC','tempMinF','weatherCode','date' );
						if(in_array($v,$wanted)){
							$output['forcast'][$i][$v] = $k;
						}
					
					};
					$i++;
				}
			return $output;
			}else{
				echo 'Seams like have entred incorrect pair of city and country..if not, well I\'m sorry the data are not ready on '.$url;
			}
		}

		
	}
}

