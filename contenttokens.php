<?php

require_once 'contenttokens.civix.php';


function contenttokens_civicrm_tokens( &$tokens ){  

	$tokens['content'] = array();
	$types_in_use = contenttokens_getContentTypesInUse();
	
	foreach( $types_in_use as $cur_type){
	/*
		$tokens['content'] = array(
	  	'content.type___story___day_7' =>   'Content: Stories created in the last 7 days',
	  	'content.type___story___day_14' =>  'Content: Stories created in the last 14 days',
	  	'content.type___story___day_30' =>  'Content: Stories created in the last 30 days', 
	  	'content.type___story___week_3' =>  'Content: Stories created in the last 3 weeks', 
	  	'content.type___story___month_3' => 'Content: Stories created in the last 3 months',  
	  	);
	  	*/
	  	// by day
	  	$key = "content.type___".$cur_type."___day_7" ;
	  	$label = "Content of type '$cur_type' created in the last 7 days"; 
  		 $tokens['content'][$key] = $label; 
  		 
  		 // by week
  		 $key = "content.type___".$cur_type."___week_4" ;
	  	$label = "Content of type '$cur_type' created in the last 4 weeks"; 
  		 $tokens['content'][$key] = $label; 
  		 
  		 // by month
  		 $key = "content.type___".$cur_type."___month_3" ;
	  	$label = "Content of type '$cur_type' created in the last 3 months"; 
  		 $tokens['content'][$key] = $label; 
  		 
	  	}

               $categories_in_use = contenttokens_getCategoriesInUse() ; 
               foreach(  $categories_in_use as $cid => $cur_category){
               	    $cur_category_clean =  contenttoken_clean_string_for_token( $cur_category); 
               	   // print "<Br><br>$cur_category_clean"; 
               	    // by day
                    $key = "content.category___".$cid."___".$cur_category_clean."___day_7" ;
	  	$label = "Content with category '$cur_category' created in the last 7 days"; 
  		 $tokens['content'][$key] = $label; 

		 // by week
                    $key = "content.category___".$cid."___".$cur_category_clean."___week_4" ;
	  	$label = "Content with category '$cur_category' created in the last 4 weeks"; 
  		 $tokens['content'][$key] = $label; 
  		 
  		  // by month
                    $key = "content.category___".$cid."___".$cur_category_clean."___month_3" ;
	  	$label = "Content with category '$cur_category' created in the last 3 months"; 
  		 $tokens['content'][$key] = $label; 



              }
	  	
	     $feeds_in_use  = contenttokens_getFeedsInUse();
	     foreach( $feeds_in_use as $fid => $cur_feed){
	     
	          $cur_feed_clean = contenttoken_clean_string_for_token( $cur_feed); 
	          $key = "content.feed___".$fid."___".$cur_feed_clean."___month_3";
	          
	          $label = "Content from feed '$cur_feed' created in the last 3 months"; 
  		 $tokens['content'][$key] = $label; 

	          
	     
	     
	     }
	     	
	  	
	}
	
	
  function contenttoken_clean_string_for_token( $param){
  
       $tmp_clean = str_replace( " ", "_",  $param) ; 
       $tmp_clean = str_replace( "'", "_",  $tmp_clean) ; 
       $tmp_clean = str_replace( ",", "_",  $tmp_clean) ; 
        $tmp_clean = str_replace( "-", "_",  $tmp_clean) ; 
         $tmp_clean = str_replace( "/", "_",  $tmp_clean) ; 
       $tmp_clean = str_replace( "\\", "_",  $tmp_clean) ; 
  
  	return $tmp_clean;
  }	
		
  function contenttokens_civicrm_tokenValues( &$values, &$contactIDs, $job = null, $tokens = array(), $context = null) {
           
           
  
  	 while( $cur_token_raw = current( $tokens['content'] )){
  	 
  	       $config = CRM_Core_Config::singleton();
                 $drupal_version =  contenttokens_getDrupalVersion();
  	      
  	       $website_host_name = $_SERVER['SERVER_NAME']; 
	        $ssl_in_use = $_SERVER['HTTPS'];
		if( strlen($ssl_in_use) > 0){
			$protocol = "https://"; 
		}else{
			$protocol = "http://";
		}
		
		 $url_beginning = $protocol.$website_host_name."/"; 
	 
	        $cck_types_in_use = contenttokens_getContentTypesInUse();
	         $categories_in_use = contenttokens_getCategoriesInUse() ; 
	 
  	 
	 	$tmp_key = key($tokens['content']); 
	 	
	 	$font_size = "10px";
	 	// CiviCRM is buggy here, if token is being used in CiviMail, we need to use the key 
	 	// as the token. Otherwise ( PDF Letter, one-off email, etc) we
	 	// need to use the value. 
	 	$cur_token = ''; 
	 	if(  is_numeric( $tmp_key)){
	 		 $cur_token = $cur_token_raw;
	 	}else{
	 		// Its being used by CiviMail.
	 		$cur_token = $tmp_key;
	 	}
	 	
	 	$token_to_fill = 'content.'.$cur_token;
	 	//print "<br><br>Token to fill: ".$token_to_fill."<br>"; 
	 	
	 	$token_as_array = explode("___",  $cur_token ); 
	 	
	 	
	 	$partial_token =  $token_as_array[0];
	 	
	 	
	 	
	 	if( $partial_token == 'type' || $partial_token == 'category' || $partial_token == 'feed'){
	 	
                    if(  $partial_token == 'type' && in_array( $token_as_array[1], $cck_types_in_use) ){
	 	       $cck_type = $token_as_array[1];
	 	        $token_date = $token_as_array[2];
		    }else if( $partial_token == 'category' && array_key_exists($token_as_array[1], $categories_in_use)  ){
  			  $category_id  = $token_as_array[1];
  			   $token_date = $token_as_array[3];

		    }else if( $partial_token == 'feed'){
		    	$feed_id  = $token_as_array[1];
  			   $token_date = $token_as_array[3];
  			   
		    }else{
			  // SHould skip this 
			}

   
	 	  
	         
	           
	            $date_array = explode("_", $token_date);
	            $date_unit = $date_array[0];
	            $date_number = $date_array[1];
	            
	             
	            
	            $tmp_content_html = ""; 
	            if( is_numeric( $date_number) && ( $date_unit == 'day'  || $date_unit == 'week' || $date_unit == 'month'  )){ 
	            // get content data 
                        $cms_db = contenttokens_getUserFrameworkDatabaseName(); 
	            
	              if( $config->userFramework=="Drupal" || $config->userFramework=="Drupal6" ){ 
	              
	              if( $drupal_version  == "6"){
	              	$revision_tb = "$cms_db.node_revisions"; 
	              	$source  = "src";
	              	$alias = "dst"; 
	              	if ($partial_token == 'type'){
	              	 	$sql = "SELECT t1.nid as nid, t1.url_alias, rv.title as title, rv.teaser  , 
	              	 	t1.created, DATE( FROM_UNIXTIME(t1.created )) as formatted_create_date
				FROM 
				(SELECT max(nr.vid) as vid, nr.nid, n.created,   ifnull(alias.dst, concat( 'node/' , n.nid) ) as url_alias
				 FROM $revision_tb nr join $cms_db.node n ON n.nid = nr.nid
				 LEFT JOIN $cms_db.url_alias alias ON CONCAT('node/' , n.nid ) = alias.src
				WHERE n.status = 1 AND n.type = '$cck_type'
				AND DATE( FROM_UNIXTIME(n.created )) > date_sub( now() , INTERVAL $date_number $date_unit)
				GROUP BY nr.nid )
				as t1
				LEFT JOIN $revision_tb rv ON t1.vid = rv.vid
				ORDER BY t1.created DESC";
			}else if($partial_token == 'category' ){
				 
				
				$sql = "SELECT t1.nid as nid, t1.url_alias, rv.title as title, rv.teaser  , 
				t1.created, DATE( FROM_UNIXTIME(t1.created )) as formatted_create_date
				FROM 
				(SELECT max(nr.vid) as vid,  tn.nid,  n.created, ifnull(alias.dst, concat( 'node/' , n.nid) ) as url_alias 
				FROM $cms_db.term_node tn  
				JOIN $cms_db.node n ON tn.nid = n.nid 
				JOIN $revision_tb nr ON n.nid = nr.nid
				LEFT JOIN $cms_db.url_alias alias ON CONCAT('node/' , n.nid ) = alias.src 
				where n.status = 1 AND tn.tid = $category_id
				AND DATE( FROM_UNIXTIME(n.created )) > date_sub( now() , INTERVAL $date_number $date_unit) 
				group by n.nid) as t1
				LEFT JOIN $revision_tb rv ON t1.vid = rv.vid
				ORDER BY t1.created DESC ";	
			}else if( $partial_token == 'feed'){
			   if( module_exists( "aggregator")){ 
			
			//  substring( description, 1, 250) as teasertest
			   $sql = "SELECT title as title, link as full_url, DATE( FROM_UNIXTIME( timestamp )) as formatted_create_date
			           FROM $cms_db.aggregator_item where fid = $feed_id 
			           AND DATE( FROM_UNIXTIME( timestamp )) > date_sub( now() , INTERVAL $date_number $date_unit) 
			           ORDER BY timestamp DESC ";
                            }
			
			
			
			}
	              
	              }else{
	                // Drupal7 +
	                $revision_tb = "$cms_db.node_revision"; 
	              	$source  = "source";
	              	$alias = "alias"; 
	              	if ($partial_token == 'type'){
                          $sql = "SELECT t1.nid as nid, t1.url_alias, rv.title as title ,  t1.created, DATE( FROM_UNIXTIME(t1.created )) as formatted_create_date
				FROM 
				(SELECT max(nr.vid) as vid, nr.nid, n.created,   ifnull(alias.alias, concat( 'node/' , n.nid) ) as url_alias
				 FROM $revision_tb nr join $cms_db.node n ON n.nid = nr.nid
				 LEFT JOIN $cms_db.url_alias alias ON CONCAT('node/' , n.nid ) = alias.source
				WHERE n.status = 1 AND n.type = '$cck_type'
				AND DATE( FROM_UNIXTIME(n.created )) > date_sub( now() , INTERVAL $date_number $date_unit)
				GROUP BY nr.nid )
				as t1
				LEFT JOIN $revision_tb rv ON t1.vid = rv.vid
				ORDER BY t1.created DESC";	
			}else if($partial_token == 'category'){
			
			       $sql_getvocab = "SELECT vocab.machine_name FROM $cms_db.taxonomy_term_data t 
			       			JOIN $cms_db.taxonomy_vocabulary vocab ON t.vid = vocab.vid
			       			WHERE t.tid =  $category_id "; 
			       	$dao_vocab =& CRM_Core_DAO::executeQuery( $sql_getvocab,   CRM_Core_DAO::$_nullArray ) ;
			       	if( $dao_vocab->fetch()){
			       	  $vocab_table_name = "field_data_field_".$dao_vocab->machine_name; 
			       	  $vocab_field_name = "field_".$dao_vocab->machine_name."_tid"; 
			       	
			       	}
			       	$dao_vocab->free(); 
			       			
				
				$sql = "SELECT t1.nid as nid, t1.url_alias, rv.title as title , 
				t1.created , DATE( FROM_UNIXTIME(t1.created )) as formatted_create_date
				FROM 
				(SELECT tn.revision_id as vid,  tn.entity_id as nid,  n.created, ifnull(alias.alias, concat( 'node/' , n.nid) ) as url_alias 
				FROM $cms_db.$vocab_table_name tn  
				JOIN $cms_db.node n ON tn.entity_id = n.nid AND tn.entity_type = 'node' 
				JOIN $revision_tb nr ON n.nid = nr.nid
				LEFT JOIN $cms_db.url_alias alias ON CONCAT('node/' , n.nid ) = alias.source 
				where n.status = 1 AND tn.$vocab_field_name = $category_id
				AND tn.entity_type = 'node'
				AND tn.deleted <> 1
				AND DATE( FROM_UNIXTIME(n.created )) > date_sub( now() , INTERVAL $date_number $date_unit) 
				group by n.nid) as t1
				LEFT JOIN $revision_tb rv ON t1.vid = rv.vid
				ORDER BY t1.created DESC ";
			
			}else if($partial_token == 'feed' ){
			  if( module_exists( "aggregator")){ 
			    // substring( description, 1, 250) as teasertest
				 $sql = "SELECT title as title, link as full_url , DATE( FROM_UNIXTIME( timestamp )) as formatted_create_date
			           FROM $cms_db.aggregator_item where fid = $feed_id 
			           AND DATE( FROM_UNIXTIME( timestamp )) > date_sub( now() , INTERVAL $date_number $date_unit) 
			            ORDER BY timestamp DESC  ";
                           }
			
			
			}                   
	              }
	           
	           
	           }else if($config->userFramework=="Wordpress" ){
	           
	           	if ($partial_token == 'type'){
                           $sql = "SELECT p.ID as nid, p.guid as url_alias, p.post_title as title ,p.post_content as teaser,  p.post_date as formatted_create_date 
                           	FROM $cms_db.wp_posts p
                                where p.post_type = '$cck_type'
                                AND p.post_date > date_sub( now() , INTERVAL $date_number $date_unit)
                                AND p.post_status = 'publish'
                                ORDER BY p.post_date DESC"; 
                        }else if($partial_token == 'category'){
                        
                           $sql = "SELECT p.ID as nid,  p.guid as url_alias, p.post_title as title ,p.post_content as teaser,  p.post_date as formatted_create_date,
                                t.term_name from
				$cms_db.wp_posts p join  $cms_db.wp_term_relationships tr on p.id = tr.object_id 
				join $cms_db.wp_terms t ON t.term_id =  tr.term_taxonomy_id 
				WHERE t.term_id = $category_id
				AND p.post_date > date_sub( now() , INTERVAL $date_number $date_unit)
				AND p.post_status = 'publish'
                                ORDER BY p.post_date DESC"; 
			
			
			} 



                   }else if($config->userFramework=="Joomla"){
			// TODO: Figure this out for Joomla


                  }
	           
	        //    print "<br>SQL: ".$sql;	        
		   
		       
		     $tmp_content_html = ""; 

                     if( strlen( $sql) > 0 ){
		     $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
		     $read_more_label = ts('Read More'); 
		          
  		     while($dao->fetch()){
  		     	$nid = $dao->nid;
  		     	$content_title = $dao->title;
  		     	
  		     	
  		     	$url_alias = $dao->url_alias; 
  		     	$formatted_create_date = $dao->formatted_create_date;
  		     	
  		     	  
  		     	  if ($config->userFramework=="Drupal" || $config->userFramework=="Drupal6"){
  		     	   
  		     	  if( $drupal_version  == "7" && $partial_token <> 'feed' ){
  		     	  	// $content_teaser = render(node_view(node_load($nid), 'teaser'));
  		     	  	
  		     	  	$node = node_view(node_load($nid),'teaser');
  		     	  	 $content_teaser =  $node['body'][0]['#markup']; 
  		     	  
  		     	  }else if( $drupal_version  == "6"){
  		     	  	$content_teaser = $dao->teaser; 
  		     	  }
  		     	  }else if($config->userFramework=="Wordpress" ){
  		     	  
  		     	  	$content_teaser = $dao->teaser; 
  		     	  
  		     	  }else if($config->userFramework=="Joomla" ){
  		     	  
  		     	  }
  		     	  if( $partial_token == 'feed'){
  		     	  	$full_url = $dao->full_url; 
  		     	  }else{
  		     	  	$full_url =  $url_beginning.$url_alias; 
  		     	  }
  		     	 
  		     	 // Convert any relative paths to absolute paths. 
  		     	$content_ready_to_use =  str_ireplace( "src='/" , "src='".$url_beginning , $content_teaser ); 
  		     	$content_ready_to_use =  str_ireplace( 'src="/' , 'src="'.$url_beginning , $content_ready_to_use ); 
  		     	
  		     	$content_ready_to_use =  str_ireplace( "href='/", "href='".$url_beginning , $content_ready_to_use);
  		     	$content_ready_to_use =  str_ireplace( 'href="/', 'href="'.$url_beginning , $content_ready_to_use);
  		     	
  		     	// need to deal with anchor-type urls, such as <a href="#sectionb">
  		     	$content_ready_to_use =  str_ireplace( "href='#", "href='".$full_url."#" , $content_ready_to_use);
  		     	$content_ready_to_use =  str_ireplace( 'href="#', 'href="'.$full_url."#" , $content_ready_to_use);
  		     			     	

  		     	$tmp_content_html = $tmp_content_html."<br><div><b><a href='$full_url'>$content_title</a></b><br>".$content_ready_to_use.
                         "<br><a href='$full_url'>".$read_more_label."</a></div>"; 
  		     		 
  		     }
  		     $dao->free(); 
                     }

		     foreach ( $contactIDs as $cid ) {
		    // Populate the token value for this contact. 
		      $values[$cid][$token_to_fill] =  $tmp_content_html;
		          
	            }
	            
	            }
                 }
                 
	     next($tokens['content']);    
	
	      
	      
	 }     
  
  
  
  }
  
  
  
  
  
  
  
  function contenttokens_getFeedsInUse(){
  
        $feeds_in_use = array(); 
        $cms_db = contenttokens_getUserFrameworkDatabaseName(); 

  	$config = CRM_Core_Config::singleton();
        // print "<br><br>";
	// print_r( $config) ; 
	if ($config->userFramework=="Drupal" || $config->userFramework=="Drupal6"){
	  if( module_exists( "aggregator")){ 
	
		    // get all aggregator feeds that are used 
		    $sql = "SELECT fid as feed_id, title as feed_title FROM $cms_db.aggregator_feed";
		    
		      $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
		     while($dao->fetch()){
		     	$feeds_in_use[$dao->feed_id] = $dao->feed_title; 
		     }
		     $dao->free(); 
    // print "<br>$sql"; 
    	   }
    
    }else if( $config->userFramework=="Wordpress"){
        // TODO: Figure out how to get this info from WordPress


    }else if( $config->userFramework=="Joomla" ){
       // TODO: Figure out how to get this info from Joomla.


     } 
     return $feeds_in_use;
  
  
  
  
  }
  
  
  
  
  function contenttokens_getContentTypesInUse(){
  
       $types = array(); 

  	$config = CRM_Core_Config::singleton();
        // print "<br><br>";
	// print_r( $config) ; 
	if ($config->userFramework=="Drupal" || $config->userFramework=="Drupal6"){
	
    // get all CCK content types that are used by published content
      $cms_db = contenttokens_getUserFrameworkDatabaseName(); 
     
      
    $sql = "SELECT type FROM $cms_db.node n where status = 1 GROUP BY type";
    
      $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
     while($dao->fetch()){
     	$types[] = $dao->type; 
     }
     $dao->free(); 
    // print "<br>$sql"; 
    
    }else if( $config->userFramework=="Wordpress"){
         $sql = "SELECT p.post_type as type FROM $cms_db.wp_posts p WHERE p.post_status = 'publish' group by post_type"; 
         $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
     while($dao->fetch()){
     	$types[] = $dao->type; 
     }

     $dao->free(); 


    }else if( $config->userFramework=="Joomla" ){
       // TODO: Figure out how to get this info from Joomla.


     } 
     return $types; 
  
  
  }
  
  
  function contenttokens_getDrupalVersion(){
  
  $drupal_version_tmp = "";
  // userFramework
  $config = CRM_Core_Config::singleton();
	$cms_str = $config->userFramework;
	
	if( $cms_str == "Drupal6" ){
		$drupal_version_tmp = "6";
	
	}else if( $cms_str == "Drupal" ){
		$drupal_version_tmp = "7";
	
	}
	
	return $drupal_version_tmp;
  
  
  }
  
  function contenttokens_getCategoriesInUse(){
      $terms = array(); 
       $cms_db = contenttokens_getUserFrameworkDatabaseName(); 

  	$config = CRM_Core_Config::singleton();
        
	  $drupal_version =  contenttokens_getDrupalVersion();

	if ($config->userFramework=="Drupal" || $config->userFramework=="Drupal6"){
	
	  if(  $drupal_version  == "6"){
             $sql = "SELECT  t.tid as category_id,  concat( v.name , '-', t.name)  as category_term_name 
             FROM $cms_db.term_node  tn 
             JOIN  $cms_db.term_data t ON tn.tid = t.tid
             JOIN $cms_db.vocabulary v ON v.vid = t.vid
             GROUP BY t.tid" ;
         //    print "<br><br>$sql";
             $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
             while($dao->fetch()){
                $cid = $dao->category_id;
     	        $terms[$cid] = $dao->category_term_name; 
              }
            $dao->free(); 
            
            }else if( $drupal_version  == "7"){
            	 $sql = "SELECT  t.tid as category_id, concat( v.name , '-', t.name)  as category_term_name 
            	 FROM $cms_db.taxonomy_term_data t
            	 JOIN $cms_db.taxonomy_vocabulary v ON t.vid = v.vid
             GROUP BY t.tid" ;
             $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
             while($dao->fetch()){
                $cid = $dao->category_id;
     	        $terms[$cid] = $dao->category_term_name; 
              }
            $dao->free(); 
            
            }
            
            
            
    
    
    }else if( $config->userFramework=="Wordpress"){
        $sql = "SELECT t.term_id as category_id ,  t.name as  category_term_name  from
				$cms_db.wp_posts p join  $cms_db.wp_term_relationships tr on p.id = tr.object_id 
				join $cms_db.wp_terms t ON t.term_id = tr.term_taxonomy_id 
				p.post_status = 'publish'
				GROUP BY t.term_id";
				
	     $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
             while($dao->fetch()){
                $cid = $dao->category_id;
     	        $terms[$cid] = $dao->category_term_name; 
              }
            $dao->free(); 


    }else if( $config->userFramework=="Joomla" ){
       // TODO: Figure out how to get this info from Joomla.


     } 
  // print "<br><br>categories:<br>";
  // print_r( $terms); 

    return $terms; 
  
  }
  
  function contenttokens_getUserFrameworkDatabaseName(){
  	// ['userFrameworkDSN'] => mysql://dev1_username:mypassword@localhost/dev1_main?new_link=true
	
	$cms_db_name = "";
	$config = CRM_Core_Config::singleton();
	$cms_dsn_str = $config->userFrameworkDSN ;
	
	//print_r( $config); 
	
	$cms_tmp1 = explode( '@', $cms_dsn_str) ; 
	$cms_tmp2 = explode( '/', $cms_tmp1[1]);
	$cms_tmp3 = explode( '?', $cms_tmp2[1]);
	
	//print "<br><br>".$cms_tmp2[1];
	if( strlen($cms_tmp3[0]) > 0){
		$cms_db_name = $cms_tmp3[0]; 
	}
  	return $cms_tmp3[0];
  }
  	
/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contenttokens_civicrm_config(&$config) {
  _contenttokens_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function contenttokens_civicrm_xmlMenu(&$files) {
  _contenttokens_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contenttokens_civicrm_install() {
  return _contenttokens_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function contenttokens_civicrm_uninstall() {
  return _contenttokens_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contenttokens_civicrm_enable() {
  return _contenttokens_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function contenttokens_civicrm_disable() {
  return _contenttokens_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function contenttokens_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _contenttokens_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function contenttokens_civicrm_managed(&$entities) {
  return _contenttokens_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function contenttokens_civicrm_caseTypes(&$caseTypes) {
  _contenttokens_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function contenttokens_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _contenttokens_civix_civicrm_alterSettingsFolders($metaDataFolders);
}