<?php

require_once 'contenttokens.civix.php';


function contenttokens_civicrm_tokens( &$tokens ){  

	$tokens['content'] = array();
	$types_in_use = contenttokens_getContentTypesInUse();
	
	foreach( $types_in_use as $cur_type){
	/*
		$tokens['content'] = array(
	  	'content.type___story___day_7' =>   'Content: Stories changed in the last 7 days',
	  	'content.type___story___day_14' =>  'Content: Stories changed in the last 14 days',
	  	'content.type___story___day_30' =>  'Content: Stories changed in the last 30 days', 
	  	'content.type___story___week_3' =>  'Content: Stories changed in the last 3 weeks', 
	  	'content.type___story___month_3' => 'Content: Stories changed in the last 3 months',  
	  	);
	  	*/
	  	// by day
	  	$key = "content.type___".$cur_type."___day_7" ;
	  	$label = "Content of type '$cur_type' changed in the last 7 days"; 
  		 $tokens['content'][$key] = $label; 
  		 
  		 // by week
  		 $key = "content.type___".$cur_type."___week_4" ;
	  	$label = "Content of type '$cur_type' changed in the last 4 weeks"; 
  		 $tokens['content'][$key] = $label; 
  		 
  		 // by month
  		 $key = "content.type___".$cur_type."___month_3" ;
	  	$label = "Content of type '$cur_type' changed in the last 3 months"; 
  		 $tokens['content'][$key] = $label; 
  		 
	  	}
	  	
	  	
	}
	
	
	
		
  function contenttokens_civicrm_tokenValues( &$values, &$contactIDs, $job = null, $tokens = array(), $context = null) {
           
  
  	 while( $cur_token_raw = current( $tokens['content'] )){
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
	 	
	 	
	 	if( $partial_token == 'type'){
	 	
	 	    $cck_type = $token_as_array[1];
	 	    if( in_array( $cck_type, $cck_types_in_use) ){	
	        
	            $token_date = $token_as_array[2];
	            $date_array = explode("_", $token_date);
	            $date_unit = $date_array[0];
	            $date_number = $date_array[1];
	            
	              $drupal_db = contenttokens_getUserFrameworkDatabaseName(); 
	            
	            $tmp_content_html = ""; 
	            if( is_numeric( $date_number) && ( $date_unit == 'day'  || $date_unit == 'week' || $date_unit == 'month'  )){ 
	            // get content data 
	         
	              
	              if( $drupal_version  == "6"){
	              	$revision_tb = "$drupal_db.node_revisions"; 
	              	$source  = "src";
	              	$alias = "dst"; 
	              	 $sql = "SELECT t1.nid, t1.url_alias, rv.title, rv.teaser  , t1.changed, DATE( FROM_UNIXTIME(t1.changed )) as formatted_change_date
				FROM 
				(SELECT max(nr.vid) as vid, nr.nid, n.changed,   ifnull(alias.dst, concat( 'node/' , n.nid) ) as url_alias
				 FROM $revision_tb nr join $drupal_db.node n ON n.nid = nr.nid
				 LEFT JOIN $drupal_db.url_alias alias ON CONCAT('node/' , n.nid ) = alias.src
				WHERE n.status = 1 AND n.type = '$cck_type'
				AND DATE( FROM_UNIXTIME(n.changed )) > date_sub( now() , INTERVAL $date_number $date_unit)
				GROUP BY nr.nid )
				as t1
				LEFT JOIN $revision_tb rv ON t1.vid = rv.vid
				ORDER BY t1.changed DESC";
	              
	              }else{
	                $revision_tb = "$drupal_db.node_revision"; 
	              	$source  = "source";
	              	$alias = "alias"; 
                          $sql = "SELECT t1.nid, t1.url_alias, rv.title,  t1.changed, DATE( FROM_UNIXTIME(t1.changed )) as formatted_change_date
				FROM 
				(SELECT max(nr.vid) as vid, nr.nid, n.changed,   ifnull(alias.alias, concat( 'node/' , n.nid) ) as url_alias
				 FROM $revision_tb nr join $drupal_db.node n ON n.nid = nr.nid
				 LEFT JOIN $drupal_db.url_alias alias ON CONCAT('node/' , n.nid ) = alias.source
				WHERE n.status = 1 AND n.type = '$cck_type'
				AND DATE( FROM_UNIXTIME(n.changed )) > date_sub( now() , INTERVAL $date_number $date_unit)
				GROUP BY nr.nid )
				as t1
				LEFT JOIN $revision_tb rv ON t1.vid = rv.vid
				ORDER BY t1.changed DESC";	                   
	              }
	           
	           
	          //  print "<br>SQL: ".$sql;	        
		   
		       $tmp_content_html = ""; 
		          $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
		          $read_more_label = ts('Read More'); 
		          
  		     while($dao->fetch()){
  		     	$nid = $dao->nid;
  		     	$content_title = $dao->title;
  		     	
  		     	$url_alias = $dao->url_alias; 
  		     	$formatted_change_date = $dao->formatted_change_date;
  		     	
  		     	  
  		     	  
  		     	  if( $drupal_version  == "7" ){
  		     	  	// $content_teaser = render(node_view(node_load($nid), 'teaser'));
  		     	  	
  		     	  	$node = node_view(node_load($nid),'teaser');
  		     	  	 $content_teaser =  $node['body'][0]['#markup']; 
  		     	  
  		     	  }else if( $drupal_version  == "6"){
  		     	  	$content_teaser = $dao->teaser; 
  		     	  }
  		     	 
  		     
  		     	$full_url =  $url_beginning.$url_alias; 		     	

  		     	$tmp_content_html = $tmp_content_html."\n<br><br><b><a href='$full_url'>$content_title</a></b><br>$content_teaser".
                         "<br><a href='$full_url'>$read_more_label</a>"; 
  		     		 
  		     }
  		     $dao->free(); 


		     foreach ( $contactIDs as $cid ) {
		    // Populate the token value for this contact. 
		      $values[$cid][$token_to_fill] =  $tmp_content_html;
		          
	            }
	            
	            }
                 }
                 }
	     next($tokens['content']);    
	
	      
	      
	 }     
  
  
  
  }
  
  function contenttokens_getContentTypesInUse(){
  
       $types = array(); 

  	$config = CRM_Core_Config::singleton();
	
	if ($config->userSystem->is_drupal){
	
    // get all CCK content types that are used by published content
      $drupal_db = contenttokens_getUserFrameworkDatabaseName(); 
     //  $drupal_version =  contenttokens_getDrupalVersion();

      
    $sql = "SELECT type FROM $drupal_db.node n where status = 1 GROUP BY type";
    
      $dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
     while($dao->fetch()){
     	$types[] = $dao->type; 
     }
     $dao->free(); 
    // print "<br>$sql"; 
    
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