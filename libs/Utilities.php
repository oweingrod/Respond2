<?php
	
class Utilities
{
	// gets a query string
	public static function GetQueryString($field){
		
		if(array_key_exists($field, $_GET)){
			$value = $_GET[$field];
			$value = trim($value);
	    	$value = stripslashes($value);
	    	$value = htmlspecialchars($value);
			
	    	return $value;
		}
		else{
			return "";
		} 
		
	}

	// uses curl to execute and retrieve the response from a URL
	public static function GetJsonData($url, $assoc){
		$ch = curl_init();

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_URL, $url);

	    $data = curl_exec($ch);
	    $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);

	    if ($resultCode == 200) {
	        return json_decode($data, $assoc);
	    } else {
	        return false;
	    }
	}
	
	// copies a directory
	public static function CopyDirectory($src, $dst){ 
	    $dir = opendir($src); 
	    @mkdir($dst); 
	    while(false !== ( $file = readdir($dir)) ) { 
	        if (( $file != '.' ) && ( $file != '..' )) { 
	            if ( is_dir($src . '/' . $file) ) { 
	                Utilities::CopyDirectory($src . '/' . $file,$dst . '/' . $file); 
	            } 
	            else { 
	                copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	        } 
	    } 
	    closedir($dir); 
	} 
	
	
	// saves specified content to a file
	public static function SaveContent($dir, $filename, $content){
		$full = $dir.$filename;
		
		if(!file_exists($dir)){
			mkdir($dir, 0777, true);	
		}
		
		$fp = @fopen($full, 'w'); // Generate a new cache file  
		@fwrite($fp, $content); // save the contents of output buffer to the file
		@fclose($fp); 
	}
	
	// saves a file
	public static function SaveFile($dir, $filename, $file){
		$full = $dir.$filename;
		
		if(!file_exists($dir)){
			mkdir($dir, 0777, true);	
		}
		
		if(move_uploaded_file($file, $full)) {
			return true;
		} 
		else{
		    return false;
		}
	}
	
	// saves a image
	public static function SaveImage($dir, $filename, $image){
		$full = $dir.$filename;
		
		if(!file_exists($dir)){
			mkdir($dir, 0777, true);	
		}
		
		$parts = explode(".", $filename); 
		$ext = end($parts); // get extension
		$ext = strtolower($ext); // convert to lowercase
		
		if($ext=='png'){ // save image
			imagepng($image, $full);
		}
		else if($ext=='jpg'){
			imagejpeg($image, $full, 100);
		}
		else if($ext=='gif'){
			imagegif($image, $full);
		}
		
	}
	
	// adapted from mobiforge, http://mobiforge.com/developing/story/lightweight-device-detection-php
	public static function IsMobileDevice(){
		
		$mobile_browser = '0';
 
 		if(isset($_SERVER['HTTP_USER_AGENT'])){
			if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			    $mobile_browser++;
			}
		}
		
		if(isset($_SERVER['HTTP_ACCEPT'])){ 
			if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			    $mobile_browser++;
			}    
		}
		
		if(isset($_SERVER['HTTP_USER_AGENT'])){  
			$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
			$mobile_agents = array(
			    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			    'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			    'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
			    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			    'wapr','webc','winw','winw','xda','xda-');
			 
			if(in_array($mobile_ua,$mobile_agents)) {
			    $mobile_browser++;
			}
		}
		
		if(isset($_SERVER['ALL_HTTP'])){  
			if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
			    $mobile_browser++;
			}
		}
		
		if(isset($_SERVER['HTTP_USER_AGENT'])){ 
			if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
			    $mobile_browser=0;
			}
		}
		
		if(isset($_SERVER['HTTP_USER_AGENT'])){ 
			if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows phone os')>0) {
			    $mobile_browser=1;
			}
		}
		 
		if($mobile_browser>0) {
		   return true;
		}
		else {
		   return false;
		}   
		
	}
	
	// get a date in a format JavaScript can understand, YYYY-MM-DD hours:minutes:seconds
	public static function GetDateForJavaScript($date, $timezone){
		
		$timeZone = new DateTimeZone($site['TimeZone']);
        $now = new DateTime("now", $timeZone);
        $offset = $timeZone->getOffset($now);
		
		if($date!=''){
			$unixDate = (strtotime($date)+$offset);
			$jsfriendly = date('Y-m-d G:i:s ', $unixDate);
			// $jsfriendly = date('F j, Y G:i:s ', $unixDate);
			
			return $jsfriendly;
		}
		else{
			return '';
		}
		
	}
	
	// generate rss
    public static function GenerateRSS($site, $pageType){
        
        $list = Page::GetRSS($site['SiteId'], $pageType['PageTypeId']);
        
        $timeZone = new DateTimeZone($site['TimeZone']);
        $now = new DateTime("now", $timeZone);
        $offset = $timeZone->getOffset($now);
        
        $rss = '<?xml version="1.0" encoding="ISO-8859-1"?>'.
            '<rss version="2.0">'.
              '<channel>'.
              '<title>'.$site['Name'].' - '.$pageType['TypeP'].'</title>'.
              '<link>http://'.$site['Domain'].'</link>'.
              '<description></description>'.
              '<language>en-us</language>'.
              '<copyright>Copyright (C) '.date('Y').' '.$site['Domain'].'</copyright>';
        
        foreach ($list as $row){
            
            $u = (strtotime($row['Created'])+$offset);
          
            $rss = $rss.'<item>'.
                   '<title>'.$row['Name'].'</title>'.
                   '<description><![CDATA['.$row['Description'].']]></description>'.
                   '<link>http://'.$site['Domain'].'/'.strtolower($pageType['FriendlyId']).'/'.strtolower($row['FriendlyId']).'.html</link>'.
                   '<pubDate>'.date('D, d M Y H:i:s T', $u).'</pubDate>'.
                   '</item>';
        }
        
        $rss = $rss.'</channel>';
        $rss = $rss.'</rss>';
        
        return $rss;
    }
      
    // generate site map
    public static function GenerateSiteMap($site){
        
        $list = Page::GetPagesForSite($site['SiteId']);
        
        // get offset for time zone
        $timeZone = new DateTimeZone($site['TimeZone']);
        $now = new DateTime("now", $timeZone);
        $offset = $timeZone->getOffset($now);
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
               '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
               
        date_default_timezone_set('America/Los_Angeles');
    
        foreach ($list as $row){
            
          $u = (strtotime($row['LastModifiedDate'])+$offset);
          
          $pageType = PageType::GetByPageTypeId($row['PageTypeId']);
          
          if($row['PageTypeId']==-1){
            
            $xml = $xml.'<url>'.
                       '<loc>http://'.$site['Domain'].'/</loc>'.
                       '<lastmod>'.date('Y-m-d', $u).'</lastmod>'.
                     '<priority>1.0</priority>'.
                       '</url>';
            
          }
          else{
            $xml = $xml.'<url>'.
                       '<loc>http://'.$site['Domain'].'/'.strtolower($pageType['FriendlyId']).'/'.strtolower($row['FriendlyId']).'</loc>'.
                       '<lastmod>'.date('Y-m-d', $u).'</lastmod>'.
                     '<priority>0.5</priority>'.
                       '</url>';
          }
        }
        
        $xml = $xml.'</urlset>';
        
        return $xml;
    }
      
    // generates a page
    public static function GeneratePage($site, $page, $siteurl, $imageurl, $preview, $root = '../'){
        
        $pageTypeId = $page['PageTypeId'];
        $path = '/';
        
        $pageType = null;
        $type = 'preview';
        
        $pageurl = 'http://'.$site['Domain'];
        
        if($page['PageTypeId']!=-1){
	        $pageType = PageType::GetByPageTypeId($page['PageTypeId']);
	        $pageurl .= '/'.$pageType['FriendlyId'].'/'.$page['FriendlyId'];
        }
        else{
	        $pageurl .= '/'.$page['FriendlyId'];
        }
        
        if($page['PageTypeId']!=-1){
            $pageType = PageType::GetByPageTypeId($pageTypeId);
            $type = $pageType['FriendlyId'];
        }
        
        $rootloc = '';
        $commonloc = '../common/';
        $default_url = '';
      
        if($page['PageTypeId']!=-1 || $preview==true){
            $rootloc = '../';
            $commonloc = '../../common/';
            $path = '/'.strtolower($type).'/'.strtolower($page['FriendlyId']);
            $default_url = $path;
        }
                  
        $siteId = $site['SiteId'];
        $timezone = $site['TimeZone'];
        $siteUniqId = $site['SiteUniqId'];
        
        $siteName = $site['Name'];
        $template = $site['Template'];
        $analyticsId = $site['AnalyticsId'];
 
        $htmlDir = $root.'sites/'.$site['FriendlyId'].'/templates/'.$site['Template'].'/html/';
        $htmlFile = $htmlDir.$page['Layout'].'.html';
        $content = '{{content}}';
    
        if(file_exists($htmlFile)){
            $content = file_get_contents($htmlFile);
            
            if($content==''){
                $content = '{{content}}';
            }
        }
    
        // global constants
        $content = str_replace('{{site}}', $site['Name'], $content);
        $content = str_replace('{{site-url}}', '//'.$site['Domain'], $content);
        $content = str_replace('{{page-url}}', $pageurl, $content);
        $content = str_replace('{{logo}}', $rootloc.'files/'.$site['LogoUrl'], $content);
        
        // replace with constants
        $content = str_replace('{{id}}', $page['FriendlyId'], $content);
        $content = str_replace('{{type}}', $type, $content);
        $content = str_replace('{{name}}', $page['Name'], $content);
        $content = str_replace('{{description}}', $page['Description'], $content);
        $content = str_replace('{{synopsis}}', substr(strip_tags(html_entity_decode($page['Description'])), 0, 200), $content);
        $content = str_replace('{{keywords}}', $page['Keywords'], $content);
        
        // menus
        $delimiter = '#';
		$startTag = '{{menu-';
		$endTag = '}}';
		$regex = $delimiter . preg_quote($startTag, $delimiter) 
		                    . '(.*?)' 
		                    . preg_quote($endTag, $delimiter) 
		                    . $delimiter 
		                    . 's';
		
		preg_match($regex, $content, $matches);
		
		foreach($matches as &$value) {
		    
		    $menuItems = MenuItem::GetMenuItemsForType($site['SiteId'], $value);
		    $menu = '';
		    
		    foreach($menuItems as $menuItem){
		    	$url = $menuItem['Url'];
		    	$name = $menuItem['Name'];
		    	$css = '';
		    	$cssClass = '';
		    	$active = '';
		    	
		    	if($page['PageId']==$menuItem['PageId']){
			    	$css = 'active';
		    	}
		    
			    $css .= ' '.$menuItem['CssClass'];
		    
				if(trim($css)!=''){
					$cssClass = ' class="'.$css.'"';
				}
			
			    $menu .= '<li'.$cssClass.'>';
			    $menu .= '<a href="'.$rootloc.$url.'">'.$name.'</a>';
			    $menu .= '</li>';
		    }
		    
		    $content = str_replace('{{menu-'.$value.'}}', $menu, $content);
		    
		}
		
		// css
		$stylesheet = $rootloc.'css/'.$page['Stylesheet'].'.css';
		$css = '<link href="'.$stylesheet.'" type="text/css" rel="stylesheet" media="screen">'.PHP_EOL;
        
        $content = str_replace('{{css}}', $css, $content);
        
        // css-bootstrap
        $css = '<link href="'.BOOTSTRAP_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap}}', $css, $content);
        
        // css-bootstrap-amelia
        $css = '<link href="'.BOOTSTRAP_AMELIA_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-amelia}}', $css, $content);
        
        // css-bootstrap-cerulean
        $css = '<link href="'.BOOTSTRAP_CERULEAN_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-cerulean}}', $css, $content);
        
        // css-bootstrap-cosmo
        $css = '<link href="'.BOOTSTRAP_COSMO_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-cosmo}}', $css, $content);
        
        // css-bootstrap-cyborg
        $css = '<link href="'.BOOTSTRAP_CYBORG_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-cyborg}}', $css, $content);
        
        // css-bootstrap-flatly
        $css = '<link href="'.BOOTSTRAP_FLATLY_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-flatly}}', $css, $content);
        
        // css-bootstrap-journal
        $css = '<link href="'.BOOTSTRAP_JOURNAL_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-journal}}', $css, $content);
        
        // css-bootstrap-readable
        $css = '<link href="'.BOOTSTRAP_READABLE_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-readable}}', $css, $content);
        
        // css-bootstrap-simplex
        $css = '<link href="'.BOOTSTRAP_SIMPLEX_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-simplex}}', $css, $content);
        
        // css-bootstrap-slate
        $css = '<link href="'.BOOTSTRAP_SLATE_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-slate}}', $css, $content);
        
        // css-bootstrap-spacelab
        $css = '<link href="'.BOOTSTRAP_SPACELAB_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-spacelab}}', $css, $content);
        
        // css-bootstrap-united
        $css = '<link href="'.BOOTSTRAP_UNITED_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-bootstrap-united}}', $css, $content);
        
        // css-fontawesome
        $css = '<link href="'.FONTAWESOME_CSS.'" rel="stylesheet">'.PHP_EOL;
        $content = str_replace('{{css-fontawesome}}', $css, $content);
        
        // css-prettify
        $css = '<link href="'.$rootloc.'css/prettify.css" type="text/css" rel="stylesheet" media="screen">';
        $content = str_replace('{{css-prettify}}', $css, $content);
        
        // js
        $js = '';
        
        if($site['FacebookAppId']!=''){
			$js .= '<meta property="fb:app_id" content="'.$site['FacebookAppId'].'">'.PHP_EOL;
		}
		$js .= '<script type="text/javascript" src="'.JQUERY_JS.'"></script>'.PHP_EOL;
		$js .= '<script type="text/javascript" src="'.BOOTSTRAP_JS.'"></script>'.PHP_EOL;
		$js .= '<script type="text/javascript" src="'.KNOCKOUT_JS.'"></script>'.PHP_EOL;
		$js .= '<script type="text/javascript" src="'.$rootloc.'js/jquery.cookie.js"></script>'.PHP_EOL;
		if(GOOGLE_MAPS_API_KEY != 'YOUR GOOGLE MAPS API KEY'){
			$js .= '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.GOOGLE_MAPS_API_KEY.'&sensor=false"></script>'.PHP_EOL;
		}
		$js .= '<script type="text/javascript" src="'.$rootloc.'js/jquery.respondMap-1.0.1.js"></script>'.PHP_EOL;
		$js .= '<script type="text/javascript" src="'.$rootloc.'js/jquery.respondForm-1.0.1.js"></script>'.PHP_EOL;
		$js .= '<script type="text/javascript" src="'.$rootloc.'js/messages.js"></script>'.PHP_EOL;
		$js .= '<script type="text/javascript" src="'.$rootloc.'js/pageModel.js"></script>'.PHP_EOL;
		$js .= '<script type="text/javascript" src="'.$rootloc.'js/prettify.js"></script>'.PHP_EOL;
		
		$content = str_replace('{{js}}', $js, $content);
		
		// analytics
		$analytics = '';
		
		if($site['AnalyticsId']!=''){
			$analytics = '<script type="text/javascript">'.PHP_EOL.
				'var _gaq = _gaq || [];'.PHP_EOL.
				'_gaq.push([\'_setAccount\', \''.$site['AnalyticsId'].'\']);'.PHP_EOL.
				'_gaq.push([\'_trackPageview\']);'.PHP_EOL.
				'(function() {'.PHP_EOL.
				'var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;'.PHP_EOL.
				'ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';'.PHP_EOL.
				'var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);'.PHP_EOL.
				'})();'.PHP_EOL.
				'</script>';
		}
		
		$content = str_replace('{{analytics}}', $analytics, $content);
		
		// rss
		$rss = '';
		
		if($page['Rss']!=''){
		
			$rss_arr = explode(',', $page['Rss']);
		
			$count = count($rss_arr);
	
			for($x=0; $x<$count; $x++){
				$rss_pageType = PageType::GetByFriendlyId($rss_arr[$x], $site['SiteId']);
				if($rss_pageType!=null){
					$rss .= '<link rel="alternate" type="application/rss+xml" title="'.$site['Name'].' - '.$rss_pageType['TypeP'].' RSS Feed" href="'.$rootloc.'data/'.strtolower($rss_pageType['TypeP']).'.xml">'.PHP_EOL;
				}
				
			}
		}
		
		$content = str_replace('{{rss}}', $rss, $content);
		
		// preview content
        $p_content = '';
        $status = 'publish';
    
        if($preview==true){
            $status = 'draft';
        }
    
        $fragment = $root.'sites/'.$site['FriendlyId'].'/fragments/'.$status.'/'.$page['PageUniqId'].'.html';
    
        if(file_exists($fragment)){
          $p_content = file_get_contents($fragment);
        }

        $p_content = str_replace('sites/'.$site['FriendlyId'].'/', $rootloc, $p_content);
        
        //content and synopsis
        $content = str_replace('{{content}}', $p_content, $content);
          
        $html = Utilities::ParseHTML($site, $page, $content, $preview, $root);
        
        $pageTypeUniqId = '-1';
    
        if($pageType){
            $pageTypeUniqId = $pageType['PageTypeUniqId'];
        }
        
        if($preview==true){
            $pageTypeUniqId = 'preview';
        }
        
        // setup php header
        $header = '<?php '.PHP_EOL.
            '$siteUniqId="'.$site['SiteUniqId'].'";'.PHP_EOL.
            '$siteFriendlyId="'.$site['FriendlyId'].'";'.PHP_EOL.
            '$pageUniqId="'.$page['PageUniqId'].'";'.PHP_EOL.
            '$pageFriendlyId="'.$page['FriendlyId'].'";'.PHP_EOL.
            '$pageTypeUniqId="'.$pageTypeUniqId.'";'.PHP_EOL.
            '?>';
            
		$api = APP_URL;
        
        $inject = '<body data-siteuniqid="'.$site['SiteUniqId'].'" data-sitefriendlyid="'.$site['FriendlyId'].'" data-pageuniqid="'.$page['PageUniqId'].'" data-pagefriendlyid="'.$page['FriendlyId'].'" data-pagetypeuniqid="'.$pageTypeUniqId.'" data-api="'.$api.'"';
        
        $html = str_replace('<body', $inject, $html);
        $html = str_replace('{root}', $rootloc, $html);
        
        return $header.$html;
        
    }
      
    public static function ParseHTML($site, $page, $content, $preview, $root='../'){
    
        $html = str_get_html($content, true, true, DEFAULT_TARGET_CHARSET, false, DEFAULT_BR_TEXT);
    
        $mapcount = 0;
        $pageId = $page['PageId'];
        
        $rootloc = '';
        $commonloc = '../common/';
        
        // set page url
        $pageurl = 'http://'.$site['Domain'];
        
        if($page['PageTypeId']!=-1){
	        $pageType = PageType::GetByPageTypeId($page['PageTypeId']);
	        $pageurl .= '/'.$pageType['FriendlyId'].'/'.$page['FriendlyId'];
        }
        else{
	        $pageurl .= '/'.$page['FriendlyId'];
        }
        
        // set root and common locations
        if($page['PageTypeId']!=-1 || $preview==true){
            $rootloc = '../';
            $commonloc = '../../common/';
        }
        
        $css = $rootloc.'css/'.$page['Stylesheet'].'.css';
    
		if($html == null){
			return '';
		}
    
        foreach($html->find('module') as $el){
          
            if(isset($el->name)){
                $name = $el->name;
            
                if($name=='styles'){
                    $el->outertext = '<link href="'.$css.'" type="text/css" rel="stylesheet" media="screen">'.
                       '<link href="'.BOOTSTRAP_CSS.'" rel="stylesheet">'.
                       '<link href="'.FONTAWESOME_CSS.'" rel="stylesheet">'.
                       '<link href="'.$rootloc.'css/prettify.css" type="text/css" rel="stylesheet" media="screen">';
                }
                else if($name=='header'){
                    ob_start();
                    include $root.'sites/common/modules/header.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
              
                    $el->outertext= $content;
                }
                else if($name=='scripts'){
                    ob_start();
                    include $root.'sites/common/modules/scripts.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
              
                    $el->outertext= $content;
                }
                else if($name=='analytics'){
                    ob_start();
              
                    $webpropertyid = $site['AnalyticsId'];
              
                    include $root.'sites/common/modules/analytics.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
              
                    $el->outertext= $content;
                }
                else if($name=='rss'){
                    ob_start();
              
                    include $root.'sites/common/modules/rss.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='list'){
              
                    $type = $el->type;
                    $label = $el->label;
                    $isAjax = false;
                    $pageNo = 1;
                    $curr = 0;
                    $listid = $el->id;
                    $display = $el->display;
                    $desclength = $el->desclength;
                    $length = $el->length;
                    $orderby = $el->orderby;
                    $groupby = $el->groupby;
                    $pageresults = $el->pageresults;
                  
                    if($el->display == 'blog'){
                      
                        $list = '<div id="'.$listid.'" class="respond-list" data-bind="foreach: '.$listid.'" data-display="'.$el->display.'" data-label="'.$el->label.'" data-pagetypeid="'.$el->type.'" data-length="'.$length.'" data-orderby="'.$orderby.'">'
                                .'<div class="content" data-bind="html:content"></div>'
                                .'<div class="blog-meta">'
                                .'<p>'
                                .'<a data-bind="attr:{\'href\':url}">Permanent Link</a> '
                                .'Last modified by <span data-bind="text:author"></span>'
                                .' on <span data-bind="text:lastModifiedReadable" class="last-modified-date"></span>'
                                .'</p>'
                                .'</div>'
                                .'</div>';  
                                
						if(settype($pageresults, 'boolean') == true){
							
							$list .= '<div class="page-results"><button id="pager-'.$listid.'" class="btn btn-default" data-id="'.$listid.'">Older Posts</button></div>';
							
						}
						
						
                     
                    }
                    else{
                        $list = '<ul id="'.$listid.'" class="respond-list list-group" data-bind="foreach: '.$listid.'" data-display="'.$el->display.'" data-label="'.$el->label.'" data-pagetypeid="'.$el->type.'" data-length="'.$length.'" data-orderby="'.$orderby.'">'
                                .'<li class="list-group-item">'
                                	.'<a class="pull-left thumbnail" data-bind="attr:{\'href\':url}, visible: hasImage">'
                                	.'<img data-bind="attr: {\'src\': thumb}">'
                                	.'</a>'
                                	.'<h4><a data-bind="attr:{\'href\':url}, text:name"></a></h4>'
									.'<small data-bind="visible: hasCallout, text: callout"></small>'
									.'<p data-bind="text:desc"></p>'
								.'</li>'
                                .'</ul>';  
                                
                       if(settype($pageresults, 'boolean') == true){
							
							$list .= '<div class="page-results"><button id="pager-'.$listid.'" class="btn btn-default" data-id="'.$listid.'">More...</button></div>';
							
						}
                    }
                    
                    $el->outertext = $list;
              
                }
                else if($name=='featured'){
					
                    $id = $el->id;
                    $pageName = $el->pagename;
                    $pageUniqId = $el->pageuniqid;
                  
                    $featured = '<div id="'.$id.'" data-pageuniqid="'.$pageUniqId.'" data-pagename="'.$pageName.'" class="featured-content"></div>';  
                    
                    $el->outertext = $featured	;
              
                }
                else if($name=='menu'){
    
                    if(isset($el->type)){
                        $type = $el->type;
                    }
                    else{
                        $type = 'primary';
                    }
                    
                    $el->outertext = '<?php $type="'.$type.'"; include "'.$commonloc.'modules/menu.php"; ?>';
              
                }
                else if($name=='footer'){
                    ob_start();
                    
                    $copy = $el->innertext;
                    
                    include $root.'sites/common/modules/footer.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='slideshow'){
                    $id = $el->id;
                    $width = $el->width;
                    $height = $el->height;
                    $imgList = $el->innertext;
                    ob_start();
                    include $root.'sites/common/modules/slideshow.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='gallery'){
                    $id = $el->id;
                    $imgList = $el->innertext;
                    ob_start();
                    include $root.'sites/common/modules/gallery.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='html'){
                	$h = $el->innertext;
                	
                	$h = str_replace('&lt;', '<', $h);
                	$h = str_replace('&gt;', '>', $h);
                
                    $el->outertext = $h;
                }
                else if($name=='youtube' || $name=='vimeo'){
                    $el->outertext= $el->innertext;
                }
                else if($name=='file'){
                    $file = $el->file;
                    $description = $el->description;
                    ob_start();
                    include $root.'sites/common/modules/file.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='form'){
                    $form = $el->innertext;
                    $file = $el->file;
                    $description = $el->description;
                    ob_start();
                    include $root.'sites/common/modules/form.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='map'){
                    $address = $el->address;
                    ob_start();
                    include $root.'sites/common/modules//map.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='like'){
                    $username = $el->username;
                    ob_start();
                    include $root.'sites/common/modules/like.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='comments'){
                    ob_start();
                    include $root.'sites/common/modules/comments.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else if($name=='byline'){
                    ob_start();
                    include $root.'sites/common/modules/byline.php'; // loads the module
                    $content = ob_get_contents(); // holds the content
                    ob_end_clean();
                    
                    $el->outertext= $content;
                }
                else{ 
                    // do nothing
                }
            
            }
        }
    
        foreach($html->find('plugin') as $el){
    
            $attrs = $el->attr;
    
            $p_vars = '';
            
            foreach($attrs as $key => &$val){
                ${$key} = $val; // set variable
    
                $p_vars .= '$'.$key.'="'.$val.'";';
            }
    
            $id = $el->id;
            $name = $el->name;
          
            if($render=='publish'){
                ob_start();
                include $root.'plugins/'.$type.'/render.php'; // loads the module
                $content = ob_get_contents(); // holds the content
                ob_end_clean();
                
                $el->outertext= $content;
            }
            else if($render=='runtime'){
                $list = '<?php '.
                    $p_vars.
                    'include "'.$rootloc.'plugins/'.$type.'/render.php"; ?>';
                
                $el->outertext = $list;
            }
    
        }
        
        return $html;
    }
    
    // send welcome email
    public static function SendEmailFromFile($to, $from, $subject, $replace, $file, $root='../'){
    
    
    	$full_file = $root.$file;
	    
	    if(file_exists($full_file)){
            $content = file_get_contents($full_file);
            
            // walk through and replace values in associative array
            foreach ($replace as $key => &$value) {
			    
			    $content = str_replace($key, $value, $content);
			    $subject = str_replace($key, $value, $subject);
			    
			}
			
			Utilities::SendEmail($to, $from, $subject, $content);
            
        }
	    
	    
    }

	// sends an email
    public static function SendEmail($to, $from, $subject, $content){
    
    	// send an email
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . $from . "\r\n" .
            		'Reply-To: ' . $from . "\r\n";
        
        mail($to, $subject, html_entity_decode($content), $headers); // send email
    
    }

}
	
?>