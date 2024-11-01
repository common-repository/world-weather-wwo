<?php
/*
 Plugin Name: World Weather - WWO
 Plugin URI: 
 Description: Weather widget.
 Version: 1.6
 Author: 2046 & Interconnect IT, James R Whitehead, Robert O'Rourke
 Author URI: http://2046.cz 
*/

/*
 2046: rebuilded the widget from XML Google dead API to World Weather online JSON API

 Pete: Fixed the Zurich issue by changing the useragent, guess someone in Zuric
	upset Google with WordPress. :D
 James: Changed the class name on the extended forecast LI so it is prefixed
	with the word condition. Problems arose when the weather was "clear", too
	many themes have a class of clear that's there to force open float
	containers,	mine included.
 Rob: Changed the google API call to use get_locale() which means it returns
	translated day names, conditions etc... when WPLANG is set or when 'locale'
	filter is used. In multisite WPLANG is in the options tables with same name.

	Checks unit system to determine if f_to_c() needs calling.

	Image handling & fallback are filterable now incase google change their icon
	URLs again.
*/


global $wp_version;

if ( ! class_exists( 'world_weather_wwo' ) && version_compare( phpversion( ), 5.0, 'ge' ) && version_compare( $wp_version, 3.0, 'ge' ) ) {

	// Define some fixed elements
	define ( 'world_weather_2046_DOM', 'world_weather_2046' );
	define ( 'world_weather_2046_PTH', dirname( __FILE__ ) );
	define ( 'world_weather_2046_URL', plugins_url( '', __FILE__ ) );

	// Load translation files if they exist
	$locale = get_locale( );
	if ( file_exists( world_weather_2046_PTH . '/lang/' . world_weather_2046_DOM . '-' . $locale . '.mo' ) )
		load_textdomain( world_weather_2046_DOM, world_weather_2046_PTH . '/lang/' . world_weather_2046_DOM . '-' . $locale . '.mo' );

	// Load in the helper functions
	include( world_weather_2046_PTH . '/includes/helpers.php' );

	add_action( 'widgets_init', array( 'world_weather_wwo', '_init' ), 1 );

	class world_weather_wwo extends WP_Widget {

		var $defaults = array(
			  'title' => '',
			  'city' => 'Prague',
			  'state' => 'Czech republic',
			  'frequency' => 60,
			  'celsius' => true,
			  'days' => 1,
			  'display' => 'compact',
			  'credit' => true,
			  'data' => array( ),
			  'updated' => 0,
			  'fixdate' => 0,
			  'errors' => false,
			  'clear_errors' => false,
			  'css' => true,
			  'image_directory' => '2046',
			  'api' => '',
			  'debug' => 0
			);

		/*
		 Basic constructor.
		*/
		function world_weather_wwo( ) {
			$widget_ops = array( 'classname' => __CLASS__, 'description' => __( 'Show the weather from a location you specify.', world_weather_2046_DOM ) );
			$this->WP_Widget( __CLASS__, __( 'World weather', world_weather_2046_DOM), $widget_ops);

			//$this->images = apply_filters('world_weather_wwo_images', $this->images );
			
		}



		function form( $instance  ) {
			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );
			
			//get all the possible imgae subdirectories
			$directory = plugin_dir_path(__FILE__)."images/";
 			$directory_array = array();
			if ($handle = opendir($directory)) {
			    while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
				    $directory_array[] = $entry;
				}
			    }
			    closedir($handle);
			}
			
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'api' ); ?>"><?php _e( 'WORLD WEATHER ONLINE - API:', world_weather_2046_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'api' ); ?>" name="<?php echo $this->get_field_name( 'api' ); ?>" type="text" value="<?php echo esc_attr( $api ); ?>" />
				<em><?php _e( 'Get yours on: <a target="_blank" href="http://www.worldweatheronline.com/register.aspx">Weatheronline.com</a>', world_weather_2046_DOM ); ?></em>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', world_weather_2046_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				<em><?php _e( 'This will override the display of the city name.', world_weather_2046_DOM ); ?></em>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php _e( 'City, town, postcode or zip code:', world_weather_2046_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'city' ); ?>" name="<?php echo $this->get_field_name( 'city' ); ?>" type="text" value="<?php echo esc_attr( $city ); ?>" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'state' ); ?>"><?php _e( 'Country', world_weather_2046_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'state' ); ?>" name="<?php echo $this->get_field_name( 'state' ); ?>" type="text" value="<?php echo esc_attr( $state ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php _e( 'Widget display:', world_weather_2046_DOM )?></label>
				<select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>" class="widefat">
					<option <?php selected( $display, 'compact' ); ?> value="compact"><?php _e('Compact', world_weather_2046_DOM); ?></option>
					<option <?php selected( $display, 'extended' ); ?> value="extended"><?php _e('Extended', world_weather_2046_DOM); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'image_directory' ); ?>"><?php _e( 'Image set', world_weather_2046_DOM ); ?></label>
				<select id="<?php echo $this->get_field_id( 'image_directory' ); ?>" name="<?php echo $this->get_field_name( 'image_directory' ); ?>" class="widefat">
					<?php 
					foreach($directory_array as $dir){
						$selected = '';
						if ($dir == $image_directory ){
							$selected = ' selected="selected"';
						};
						echo '<option'.$selected. ' value="'.$dir . '">' . $dir.'</option>';
					}
					 ?>
				</select>
				<em><?php _e('Each icon set is released under certain rights. Check the plugin description for more info.', world_weather_2046_DOM); ?></em>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'days' ); ?>"><?php _e( 'Show forecast for:', world_weather_2046_DOM )?></label>
				<select id="<?php echo $this->get_field_id( 'days' ); ?>" name="<?php echo $this->get_field_name( 'days' ); ?>" class="widefat"><?php
				for( $i=1; $i<8; $i++ ) { ?>
					<option <?php selected($days,$i); ?> value="<?php echo $i; ?>"><?php printf( $i==1 ? __('Today only', world_weather_2046_DOM) : __('%s days', world_weather_2046_DOM), $i); ?></option><?php
				} ?></select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'frequency' ); ?>"><?php _e( 'How often do we check the weather (mins):', world_weather_2046_DOM )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'frequency' ); ?>" name="<?php echo $this->get_field_name( 'frequency' ); ?>" type="text" value="<?php echo esc_attr( $frequency ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'celsius' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'celsius' ); ?>" id="<?php echo $this->get_field_id( 'celsius' ); ?>" value="1" <?php echo checked( $celsius ); ?>/>
					<?php _e( 'Show temperature in celsius', world_weather_2046_DOM );?>
				</label>
			</p>
			<!--<p>
				<label for="<?php echo $this->get_field_id( 'css' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'css' ); ?>" id="<?php echo $this->get_field_id( 'css' ); ?>" value="1" <?php echo checked( $css ); ?>/>
					<?php _e( 'Output CSS', world_weather_2046_DOM );?>
				</label>
			</p>-->
			<p>
				<label for="<?php echo $this->get_field_id( 'credit' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'credit' ); ?>" id="<?php echo $this->get_field_id( 'credit' ); ?>" value="1" <?php echo checked( $credit ); ?>/>
					<?php _e( 'If you do not have the commercial license from <a href="http://www.worldweatheronline.com/">WWO</a> you ought link to them!</br>This checkbox will do it for you.', world_weather_2046_DOM );?>
				</label>
			</p>

			<p><em><?php printf( $updated > 0 ? __( 'Last updated "%1$s". Current server time is "%2$s".', world_weather_2046_DOM ) : __( 'Will update when the frontend is next loaded. Current server time is %2$s.', world_weather_2046_DOM ), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $updated), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time( ) ) ); ?></em></p> 
			<p>
				<label for="<?php echo $this->get_field_id( 'fixdate' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'fixdate' ); ?>" id="<?php echo $this->get_field_id( 'fixdate' ); ?>" value="1" <?php echo checked( $fixdate ); ?>/>
					<?php _e( 'If the name days are duplicate them selves, check it.', world_weather_2046_DOM );?>
				</label>
			</p>
			<p>
					<strong><?php _e('Debug') ?></strong>
				<label for="<?php echo $this->get_field_id( 'debug' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'debug' ); ?>" id="<?php echo $this->get_field_id( 'debug' ); ?>" value="1" <?php echo checked( $debug ); ?>/>
					<?php _e( 'If you think the data are wrong, this will render the JSON info from the WWO so you can check out wat is going on..', world_weather_2046_DOM );?>
				</label>
			</p>
			<?php
			if ( ! empty( $instance[ 'errors' ] ) ) { ?>
			<div style="background-color: #FFEBE8;border:solid 1px #C00;padding:5px">
				<p><?php printf( __( 'The last error occured at "%s" with the message "%s".', world_weather_2046_DOM ), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $instance[ 'errors' ][ 'time' ] ), $instance[ 'errors' ][ 'message' ] ) ?></p>
				<label for="<?php echo $this->get_field_id( 'clear_errors' ); ?>"><?php _e( 'Clear errors: ', world_weather_2046_DOM );?>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'clear_errors' ); ?>" id="<?php echo $this->get_field_id( 'clear_errors' ); ?>" value="1" />
				</label>
			</div>
			<?php
			}
		}


		function update( $new_instance, $old_instance = array( ) ) {
			global $iso3166;

			$instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
			$instance[ 'city' ] = sanitize_text_field( isset( $new_instance[ 'city' ] ) ? $new_instance[ 'city' ] : $this->defaults[ 'city' ] );
			$instance[ 'state' ] = sanitize_text_field( isset( $new_instance[ 'state' ] ) ? $new_instance[ 'state' ] : $this->defaults[ 'state' ] );
			$instance[ 'frequency' ] = intval( $new_instance[ 'frequency' ] ) > 0 ? intval( $new_instance[ 'frequency' ] ) : $this->defaults[ 'frequency' ] ;
			$instance[ 'days' ] = intval( $new_instance[ 'days' ] ) > 0 ? intval( $new_instance[ 'days' ] ) : $this->defaults[ 'days' ] ;
			$instance[ 'display' ] = isset( $new_instance[ 'display' ] ) ? $new_instance[ 'display' ] : $this->defaults[ 'display' ] ;
			$instance[ 'celsius' ] = isset( $new_instance[ 'celsius' ] ) && ( bool ) $new_instance[ 'celsius' ] ? true : false;
			$instance[ 'credit' ] = isset( $new_instance[ 'credit' ] ) && ( bool ) $new_instance[ 'credit' ] ? true : false;
			$instance[ 'css' ] = isset( $new_instance[ 'css' ] ) && ( bool ) $new_instance[ 'css' ] ? true : false;
			$instance[ 'updated' ] = 0;
			$instance[ 'fixdate' ] = $new_instance[ 'fixdate' ];
			$instance[ 'data' ] = isset( $new_instance[ 'city' ], $old_instance[ 'city' ], $new_instance[ 'country' ], $old_instance[ 'country' ] ) && $new_instance[ 'city' ] == $old_instance[ 'city' ] && $new_instance[ 'country' ] == $old_instance[ 'country' ] ? $old_instance[ 'data' ] : array( );
			$instance[ 'api' ] = $new_instance[ 'api' ];
			$instance[ 'debug' ] = $new_instance[ 'debug' ];
			$instance[ 'image_directory' ] = $new_instance[ 'image_directory' ];
			if ( isset( $old_instance[ 'errors' ], $instance[ 'clear_errors '] ) && ! $instance[ 'clear_errors '] )
				$instance[ 'errors' ] = $old_instance[ 'errors' ];
			else
				$instance[ 'errors' ] = array( );

			return $instance;
		}

		function widget( $args, $instance  ) {

			extract( $args, EXTR_SKIP );

			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );

			// Update
			if ( empty( $data ) || intval( $updated ) + ( intval( $frequency ) * 60 ) < time( ) ) {
				// We need to run an update on the data
				$all_args = get_option( $this->option_name );
				$results = wwo_fetch_wwo_weather( $state, $city,  $display == 'compact' || $days > 1 ? true : false , $api,$days, $debug );
				
				if ( ! is_wp_error( $results ) ) {
					$data = $results;
					$all_args[ $this->number ][ 'data' ] = $results;
					// mark the updateed time
					$updated = $all_args[ $this->number ][ 'updated' ] = time( );

					if( ! update_option( $this->option_name, $all_args ) )
						add_option( $this->option_name, $all_args );
				} else {
					// If we're looking for somewhere that's not there then we need to drop the cached data
					if ( $results->get_error_code( ) == 'bad_location' )
						unset( $data );
					$this->add_error( $results );
				}
			}

			// if we have any data to processssssssss
			if ( ! empty( $data ) ) { 
				echo $before_widget;
				?>
				<div class="wwo-weather-wrapper">

					<?php 
					// echo title if any
					if(!empty($title)){
						echo '<h4>'.$title.'<h4>';
					} 
					/*
					Extended - shows the actual weather
					actual_weather = array( temp_C, temp_F, weatherCode)
				
					Compact - shows the forcast only
					forcast = array(tempMaxC,tempMaxF, tempMinC, tempMinF, weatherCode)
					 */ 
					 $plugin_directory = plugin_dir_url(__FILE__);


					// extended view
					if ( $display == 'extended' ) { 
						//mydump($data[ 'actual_weather' ]['weatherCode']);
						$image = $plugin_directory. 'images/'.$image_directory.'/' .$data[ 'actual_weather' ]['weatherCode'].'.png';
						?>

						<div class="weather-icon">
							<img src="<?php if(!empty($image)){ echo $image;} ?>" alt="" />
						</div>
						<span class="weather-dayname"><?php _e('Actual weather') ?></span>
						<div class="weather-temperature"><?php echo $celsius ? $data[ 'actual_weather' ][ 'temp_C' ] . '&deg;C' : $data[ 'actual_weather' ][ 'temp_F' ] . '&deg;F' ; ?></div>

					<?php } 
					
					
					
					
					?>
					<ul class="weather-forecast">
					<?php // handle compact mode or subsequent days
					if ( $display == 'compact' || $days > 1 ) {
						$fix_date_val = 0;
						foreach($data['forcast'] as $each_day){
							$image = $plugin_directory. 'images/' .$image_directory.'/'.  $each_day['weatherCode'].'-thumb.png';
							$i = 0; ?>
								<li class="" title="<?php esc_attr_e( $day_data[ 'condition' ] ); ?>">
									<div class="weather-icon-thumb">
										<img src="<?php if(!empty($image)){ echo $image;} ?>" alt="" />
									</div>
									<!--<div class="weather-day"><strong><?php echo $i == 0 ? __('Today', world_weather_2046_DOM) : $day; ?></strong></div>-->
									<div class="weather-hilo">
										<?php 
										//~  increase by day
										

										if($fixdate == 0){
											$t_name = __(new DateTime($each_day[ 'date' ]));
											$day_name = (string)($t_name->format('D'));
											$localized_day = __($day_name);
										}else{
											$localized_day = __(date('D', time()+$fix_date_val));
										}
										echo '<span class="weather-dayname">'.$localized_day .'</span>'; ?>
										<span class="weather-high"><?php echo $celsius == true ? $each_day[ 'tempMaxC' ]  : $each_day[ 'tempMaxF' ]; echo $celsius ?  '<span class="deg">&deg;<span class="celsius">C</span></span>' : '<span class="deg">&deg;<span class="farenheit">F</span></span>'; ?></span>
										<span class="weather-separator">/</span>
										<span class="weather-low"><?php echo $celsius == true ? $each_day[ 'tempMinC' ] : $each_day[ 'tempMinF' ]; echo $celsius ?  '<span class="deg">&deg;<span class="celsius">C</span></span>' : '<span class="deg">&deg;<span class="farenheit">F</span></span>'; ?></span>
									</div>
								</li>
							<?php 
							//~ fix for date, increase by day
							$fix_date_val = $fix_date_val + 86400;
							$i++; 
						}
					} ?>
					</ul>
					<!-- <?php printf( __( 'Last updated at %1$s on %2$s', world_weather_2046_DOM ), date( get_option( 'time_format' ), $updated ), date( get_option( 'date_format' ), $updated ) ) ; ?> -->
				</div> <?php

				if ( $credit )
					echo '<p class="WWO-credit-link">'. __('Weather by <a href="http://www.worldweatheronline.com/" title="http://www.worldweatheronline.com/">World Weather Online</a><span class="by_2046"><br />plugin by <a href="http://2046.cz">2046</a>', world_weather_2046_DOM) .'</span></p>';

				echo $after_widget;
			}
		}

		// convert farenheit to celsius
		function f_to_c( $deg ) {
			return round( (5/9)*($deg-32) );
		}


		function add_error( $error  = '') {
			$all_args = get_option( $this->option_name );
			$all_args[ $this->number ][ 'errors' ] = array( 'time' => time( ), 'message' => is_wp_error( $error ) ? $error->get_error_message( ) : ( string ) $error );

			if( ! update_option( $this->option_name, $all_args ) )
				add_option( $this->option_name, $all_args );
		}

		function _init (){
			register_widget( __CLASS__ );
		}

	}
}
/*
function mydump($a){
	echo '<pre>';
		var_dump($a);
	echo '</pre>';
}
*/

