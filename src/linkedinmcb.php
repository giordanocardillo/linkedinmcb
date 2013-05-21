<?php

// no direct access
defined('_JEXEC') or die;


class plgContentLinkedInMCB extends JPlugin
{
    public function onContentPrepare($context, &$row, &$params, $page = 0){
		$regex_one='%\{linkedinmcb (http://([a-z]{2}|www)\.linkedin\.com/(in|pub)/([a-zA-Z0-9-\/]{3,}|[0-9]{3,10}))(\|(hover|popup|click|inline))?(\|(true|false))?}%';
		$matches=array();
		preg_match_all($regex_one, $row->text, $matches);
		if(!empty($matches[0])){
			$APIKey=$this->params->get('APIKey');
			$linkedinProfiles=array();
			for($i=0;$i<count($matches[0]);$i++){
				$linkedinProfiles[]=array("ref"=>$matches[1][$i],"displayMode"=>$matches[6][$i],"connections"=>$matches[8][$i]);
			}
			$defaultParams=array();
			$defaultParams['displayMode']=$this->params->get('displayMode');
			$defaultParams['connections']=$this->params->get('connections');
			$replacements=array();
			foreach($linkedinProfiles as $profile){
				$isCompany = false;
				if (preg_match('/$[0-9]{3,10}^/', $profile["ref"])){
					$isCompany = true;
				}
				if(empty($profile["displayMode"])){
					$profile["displayMode"]=$defaultParams["displayMode"];
				}
				if(empty($profile["connections"])){
					$profile["connections"]=$defaultParams["connections"];
				}
				if($profile['displayMode']!="popup"){
					$replace='<script src="http://platform.linkedin.com/in.js" type="text/javascript"></script>';
					if($isCompany){
						$replace.='<script type="IN/CompanyProfile" data-id="'.$profile['ref'].'" data-format="'.$profile['displayMode'].'" data-related="'.$profile["connections"].'"></script>';
					}else{
						$replace.='<script type="IN/MemberProfile" data-id="'.$profile['ref'].'" data-format="'.$profile['displayMode'].'" data-related="'.$profile["connections"].'"></script>';
					}
					$replacements[]=$replace;
				}else{
					$replace='<script type="text/javascript">
								function autoPopup() {
									  var style = "top=10, left=10, width=400, height=250, status=no, menubar=no, toolbar=no scrollbars=no";
									  var popup = window.open("", "", style);
									  var content = popup.document;
									  content.write("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n");
									  content.write("<html xmlns=\"http://www.w3.org/1999/xhtml\">\n");
									  content.write("\t<head>\n");
									  content.write("\t\t<title>LinkedIn</title>\n");
									  content.write("\t</head>\n");
									  content.write("\t<body style=\"text-align: center;\">\n");'."\n";
					if (!empty($APIKey)){
						$replace.='content.write("\t\t<script type=\"text/javascript\" src=\"http://platform.linkedin.com/in.js\">api_key: '.$APIKey.'<\u002Fscript>\n");'."\n";
					}
						$replace.='content.write("\t\t<script src=\"http://platform.linkedin.com/in.js\" type=\"text/javascript\"><\u002Fscript>\n");'."\n";
					if($isCompany){
						$replace.='content.write("\t\t<script type=\"IN/CompanyProfile\" data-id=\"'.$profile['ref'].'\" data-format=\"inline\" data-related=\"'.$profile["connections"].'\"><\u002Fscript>\n");'."\n";
					}else{
						$replace.='content.write("\t\t<script type=\"IN/MemberProfile\" data-id=\"'.$profile['ref'].'\" data-format=\"inline\" data-related=\"'.$profile["connections"].'\"><\u002Fscript>\n");'."\n";
					}
					$replace.='content.write("\t</body>\n");'."\n";
					$replace.='content.write("</html>\n");'."\n";
					$replace.='content.close();}</script>';
					$replace.='<a href="javascript:autoPopup()" style="background: url(http://www.linkedin.com/scds/common/u/img/sprite/sprite_connect_v13.png) -92px -42px no-repeat !important;height: 16px  !important;width: 16px  !important;display: inline-block !important;text-decoration: none !important;vertical-align: middle !important;"></a>';
					$replacements[]=$replace;
				}
			}
			if($profile['displayMode']!="popup"){
				if (!empty($APIKey)){
					echo '<script type="text/javascript" src="http://platform.linkedin.com/in.js">api_key: '.$APIKey.'</script>';
				}
			}
			for($i=0;$i<count($matches[0]);$i++){
					$row->text = str_replace($matches[0][$i], $replacements[$i], $row->text);
			}
		}
    }
}

?>