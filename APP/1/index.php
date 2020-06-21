<?php
function redirect($url)
{
    //Header("HTTP 302 Found");
	//if (isset($_REQUEST['code'])) $url = APP_REG_URL;
    Header("Location: ".$url);
    die();
}

$step = 1; //default 1

if (isset($_REQUEST['config'])) $step = 0;
if (isset($_REQUEST['portal'])) $step = 1;
if (isset($_REQUEST['code']))$step = 2;

if(file_exists(__DIR__ . '/config.json')){
	$config = json_decode(file_get_contents(__DIR__ . '/config.json'),true);
/*Vars***********************************/
define('APP_ID', $config['app_id']); // take it from Bitrix24 after adding a new application
define('APP_SECRET_CODE', $config['app_secret']); // take it from Bitrix24 after adding a new application
define('APP_REG_URL', $config['app_redirect_url']); // the same URL you should set when adding a new application in Bitrix24
$domain = $config['bitrix_domain'];
$server_domain = $domain;
$savetime = 20; //seconds to be sure that access_token it is valid
/*End Vars*******************************/
} else {
	$step = 0;
}
//$domain = isset($_REQUEST['portal']) ? $_REQUEST['portal'] : ( isset($_REQUEST['domain']) ? $_REQUEST['domain'] : 'empty');

$btokenRefreshed = null;

$arScope = array('user');

switch ($step) {
    case 1:
        // we need to get the first authorization code from Bitrix24 where our application is _already_ installed
		if(file_exists(__DIR__ . '/access.json')){
			$settings = json_decode(file_get_contents(__DIR__ . '/access.json'),true);
			if($settings['expires'] > time() + $savetime){
				$arAccessParams = $settings;
				$step = 2;
			} else {
				requestCode($domain);
			}
		}else{
			requestCode($domain);
		}
        //requestCode($domain);
        break;

    case 2:
        //we've got the first authorization code and use it to get an access_token and a refresh_token (if you need it later)
        //echo "step 2 (getting an authorization code):<pre>";
        //print_r($_REQUEST);
        //echo "</pre><br/>";

        //$arAccessParams = requestAccessToken($_REQUEST['code'], $_REQUEST['server_domain']);
		
		if(file_exists(__DIR__ . '/access.json'))
		{
			$settings = json_decode(file_get_contents(__DIR__ . '/access.json'),true);
			//$settings = file_get_contents(__DIR__ . '/settings.json');
			//var_dump($settings['expires']);
			if($settings['expires'] > time() + $savetime){
				$arAccessParams = $settings;
			}else{
				$arAccessParams = requestAccessToken($_REQUEST['code'], $_REQUEST['server_domain']);
				$tofile = json_encode($arAccessParams, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
				file_put_contents(__DIR__ . '/access.json', $tofile);
			}
		}else{
			$arAccessParams = requestAccessToken($_REQUEST['code'], $_REQUEST['server_domain']);
			$tofile = json_encode($arAccessParams, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
			file_put_contents(__DIR__ . '/access.json', $tofile);
		}
		//time()
		//$tofile = json_encode($arAccessParams, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
		//file_put_contents(__DIR__ . '/settings.json', $tofile);
        //echo "step 3 (getting an access token):<pre>";
        //print_r($arAccessParams);
        //echo "</pre><br/>";

        //$arCurrentB24User = executeREST($arAccessParams['client_endpoint'], 'user.current', array(),$arAccessParams['access_token']);
		//print_r($arCurrentB24User);
		
		/*$contacts = executeREST($arAccessParams['client_endpoint'], 'crm.contact.list', array(
			'SELECT' => ['ID','ASSIGNED_BY_ID']
		),$arAccessParams['access_token']);
		print_r($contacts);*/
		
		/*Execute Rest APIS
		**
		**
		*/
		
        break;
    default:
        break;
}

/*Execute Rest APIS
		**
		**
		*/
$arCurrentB24User = executeREST($arAccessParams['client_endpoint'], 'user.current', array(
),$arAccessParams['access_token']);
//print_r($arCurrentB24User);

$contacts = executeREST($arAccessParams['client_endpoint'], 'crm.contact.list', array(
	'SELECT' => ['ID','ASSIGNED_BY_ID']
),$arAccessParams['access_token']);
//print_r($contacts);
/*
*
*/

//var_dump($arCurrentB24User);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quick start. Local server-side application in Bitrix24</title>
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		
	<style>
  		h2 {color:blue;}
  		p {color:green;}
	</style>
</head>
<body>
<?php
	if ($step == 0) {
?>
	<div class="container-fluid">
	<div class="alert alert-primary" role="alert">
		<h2>It is posible your aplication it is not configure to work whit Bitrix24 yet:</h2>
	</div>
    <form action="config.php" method="post" styles>
		<div class="form-group">
			<label>APP_ID:</label>
        	<input type="text" class="form-control" name="app_id" placeholder="APP_ID" value='<?php echo $config['app_id']; ?>'>
			<small id="ID" class="form-text text-muted">ID de la aplicacion en Bitrix24.</small>
		</div>
		<div class="form-group">
			<label>APP_SECRET:</label><br>
			<input type="text" class="form-control" name="app_secret" placeholder="APP_SECRET" value='<?php echo $config['app_secret']; ?>'>
			<small id="SECRET" class="form-text text-muted">Secret de la aplicacion en Bitrix24.</small>
		</div>
		<div class="form-group">
			<label>APP_REDIRECT_URL:</label><br>
			<input type="text" class="form-control" name="app_redirect_url" placeholder="APP_REDIRECT_URL" value='<?php echo $config['app_redirect_url']; ?>'>
			<small id="URL" class="form-text text-muted">URL de redireccion en Bitrix24.</small>
		</div>
		<div class="form-group">
			<label>BITRIX_DOMAIN.COM:</label><br>
			<input type="text" class="form-control" name="bitrix_domain" placeholder="BITRIX_DOMAIN.COM" value='<?php echo $config['bitrix_domain']; ?>'>
			<small id="DOMAIN" class="form-text text-muted">Domain of Bitrix24 server.</small>
		</div>
        <input type="submit" class="btn btn-primary" value="Submit">
    </form>
	</div>
<?php
} elseif ($step == 1) {
	echo '<div class="alert alert-primary" role="alert">';
	echo 'step 1 (redirecting to Bitrix24):<br/>';
	echo '</div>';
} elseif ($step == 2){
	echo '<div class="alert alert-primary" role="alert">';
	echo $arCurrentB24User["result"]["NAME"] . " " . $arCurrentB24User["result"]["LAST_NAME"] . ': <br/>';
	echo '</div>';
	echo '<div class="alert alert-success" role="alert">';
	echo 'The result of my REST APIS:';
	print_r($contacts['result']);
	echo '</div>';
?>

<table class="table">
	<tr>
      <td>ID</td>
	  <td>ASSIGNED_BY_ID</td>
    </tr>	
	<?php foreach ($contacts['result'] as $row): array_map('htmlentities', $row); ?>
    <tr>
      <td><?php echo implode('</td><td>', $row); ?></td>
    </tr>
	<?php endforeach; ?>
</table>	
	
<?php	
}
?>
	
	<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	
</body>
</html>

<?php

function executeHTTPRequest ($queryUrl, array $params = array()) {
    $result = array();
    $queryData = http_build_query($params);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));

    $curlResult = curl_exec($curl);
    curl_close($curl);

    if ($curlResult != '') $result = json_decode($curlResult, true);

    return $result;
}

function requestCode ($domain) {
    $url = 'https://' . $domain . '/oauth/authorize/' .
        '?client_id=' . urlencode(APP_ID);
    redirect($url);
}

function requestAccessToken ($code, $server_domain) {
    $url = 'https://' . $server_domain . '/oauth/token/?' .
        'grant_type=authorization_code'.
        '&client_id='.urlencode(APP_ID).
        '&client_secret='.urlencode(APP_SECRET_CODE).
        '&code='.urlencode($code);
    return executeHTTPRequest($url);
}

function executeREST ($rest_url, $method, $params, $access_token) {
    $url = $rest_url.$method.'.json';
    return executeHTTPRequest($url, array_merge($params, array("auth" => $access_token)));
}

?>