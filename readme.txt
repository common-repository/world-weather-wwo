=== World weather - WWO ===
Contributors: o----o, interconnectit, spectacula, sanchothefat
Donate link:  
Tags: weather, widget
Requires at least: 3.2
Tested up to: 3.6.1
Stable tag: 1.6

Weather widget.
 
== Description ==
 
Small patch turned into a completely new plugin. The weather forecast is based on http://www.worldweatheronline.com/ API (JSON).
   The plugin is much more light then its ancestor. It has more features and most importantly it works!
 
== Installation ==
 
## The install ##
1. You can install the plugin using the auto-install tool from the WordPress back-end.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You should now see the widget show up under 'widgets' menu. Drop that widget into a sidebar.
 
 
== Frequently Asked Questions ==
 
= Where do you get the weather forecast from? =
 
http://www.worldweatheronline.com/

== How are the day name localized? ==

 The localization is treated by the internal Wordpress logic. What language you have that language you get.	
 
= Why did you make another Weather widget? =
 
Well, we (http://prahounakole.cz) were using the ICIT Weather widget for a while and when it stopped to work I went ahead and "fix" it.  
 
= Can I use the bundled images for free? =
 
 Sure!
 
= Even on commercial project? =
 
 Sure!

= What if I want to use my own icons? =

 It's easy, make your own and put them images in the sub-folder with your custom name to the images folder in this plugin directory.
 Refresh the widget admin screen. You'll find your pack in the "Image set" Weather widget menu.
 (good source of icons is:http://www.iconarchive.com/ or http://www.deviantart.com/)
 
 Check how the bundled images how they are done, and follow this naming structure:
 WORLD WEATHER possible weather cases (image names are 395.png and it thumbnail 395-thumb.png)
 
 
= and what about the weather informations? =

 If you want to use this widget you have to first get the API from (http://www.worldweatheronline.com/register.aspx)..don't wary it's free, just follow the WWO Policy (http://www.worldweatheronline.com/free-weather-feed.aspx?menu=usage).
 
== Screenshots ==
 
 
== Changelog ==

= 1.6 =
 * NEW - new WWO API !! You have to register for new API key !! http://developer.worldweatheronline.com/
 * (the new API has does not counts with the countries .. if your city has name as city in other country, there is no chance how can I help you right now)

= 1.5 =
 * Word "Today" in extended mode is renamed to "actual weather" which actually is what it says.
 * NEW - from now you can define the country as well, it makes sense if there are 2 or more cities with the same name all over the world (not mandatory)
 * NEW - if you there is a error from the server, either because you have added wrong pair of city and state or something, it tells you
 * NEW - debug mode. If you are not sure what's going on, this will output the raw data from WWO, so you know what's behind it
 
 
= 1.4 =
 * NEW - in some cases the name days where wrong, so I added a checkbox, that fixes that issue
= 1.3 =
 * NEW - added localization *.po, *.mo files
= 1.2 =
 * NEW aded day names
= 1.1 =
 * This widget is based on ICIT weather widget. The ICIT Weather widget stopped to work because Google quit their API.
   Small patch turned into a completely new plugin. The weather forecast is based on http://www.worldweatheronline.com/ API (JSON).
   The plugin is much more light then the previous version but with more features. Now you can make your own weather images, put them in the appropriate folder and then choose your own right from the widget.
 
== Upgrade Notice ==
 
 
* ! if you make your own image pack... BACKUP it first! adn then make update!
* This widget is not a fixed ICIT weather widget but new.
