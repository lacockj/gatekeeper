<?php

# Fetch contents of URL. #
function _fetch($url, $headers = array()) {
  if ( function_exists('curl_init') ) {
    //use cURL to fetch data

    // Defatult Options //
    $options = array(
      CURLOPT_RETURNTRANSFER => true,    // return content
      CURLOPT_HEADER         => false,   // don't return headers
      CURLOPT_ENCODING       => "",      // handle all encodings
      CURLOPT_CONNECTTIMEOUT => 30,      // timeout on connect
      CURLOPT_TIMEOUT        => 30,      // timeout on response
      CURLOPT_SSL_VERIFYPEER => false    // Disabled SSL Cert checks
    );
    // Custom Options //
    foreach($headers as $key => $value) {
      $options[$key] = $value;
    }

    $ch = curl_init( $url );
    curl_setopt_array( $ch, $options );

    $response = curl_exec($ch);
    if ($response === false) {
      _log("cURL error ".curl_errno($ch)." ".curl_error($ch)." getting $url HTTP code ".curl_getinfo($ch, CURLINFO_HTTP_CODE));
    }
    curl_close ($ch);
    return $response;
  } else if ( ini_get('allow_url_fopen') ) {
    //fall back to fopen()
    $response = file_get_contents($url, 'r');
    return $response;
  }
  return false;
}

# Generate a string to random letters and numbers. #
function _genkey ($length=8) {
    $chars = array();
    for ($i = 0; $i < $length; $i++) {
        switch (rand(0,2)) {
            # number
            case 0:
                $chars[] = chr(rand(48,57));
                break;

            # upper-case
            case 1:
                $chars[] = chr(rand(65,90));
                break;

            # lower-case
            case 2:
                $chars[] = chr(rand(97,122));
                break;
        }
    }
    return implode('', $chars);
}

# Get named request parameter, whether GET or POST. #
function _get ( $var , $default=null ) {
    if ( isset($_REQUEST[$var]) ) {
        return (get_magic_quotes_gpc()) ? $_REQUEST[$var] : stripcslashes( $_REQUEST[$var] );
    } else {
        return $default;
    }
}

# Get named request parameter, POST only. #
function _get_posted ( $var, $default=null ) {
    if ( isset($_POST[$var]) ) {
        return (get_magic_quotes_gpc()) ? $_POST[$var] : stripcslashes( $_POST[$var] );
    } else {
        return $default;
    }
}

# Add a line to the debug.log #
function _log ($string, $clear=false) {
    if ($clear) {
        file_put_contents('debug.log', $string . PHP_EOL);
    } else {
        file_put_contents('debug.log', $string . PHP_EOL, FILE_APPEND);
    }
}

# POST data and get results with cURL #
function _post($url, $data=null) {
  // Defatult Options //
  $options = array(
    CURLOPT_RETURNTRANSFER => true,    // return content
    CURLOPT_HEADER         => false,   // don't return headers
    CURLOPT_ENCODING       => "",      // handle all encodings
    CURLOPT_CONNECTTIMEOUT => 30,      // timeout on connect
    CURLOPT_TIMEOUT        => 30,      // timeout on response
    CURLOPT_SSL_VERIFYPEER => false,   // Disabled SSL Cert checks
    CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
    CURLOPT_POST           => true
  );
  // POST Data //
  if ($data != null) {
    if (is_array($data)) {
      $options[CURLOPT_POSTFIELDS] = json_encode($data);
    } else {
      $options[CURLOPT_POSTFIELDS] = $data;
    }
  }

  $ch = curl_init( $url );
  curl_setopt_array( $ch, $options );

  $response = curl_exec($ch);
  if ($response === false) {
    _log("cURL error ".curl_errno($ch)." ".curl_error($ch)." getting $url HTTP code ".curl_getinfo($ch, CURLINFO_HTTP_CODE));
  }
  curl_close ($ch);
  return $response;
}

# Return data and message as JSON. Exit on error. #
function _re ( $data, $message='', $code=200 ) {
  if (version_compare(PHP_VERSION, '5.4') >= 0 ) {
    if (! headers_sent()) header('Content-type: text/json');
    http_response_code($code);
  } else {
    header('Content-type: text/json', true, $code);
  }
  if ($message===null) {
    echo json_encode($data);
  } else {
    echo json_encode(array(
      'data' => $data ,
      'message' => $message
    ));
  }
  if ($code >= 300) exit();
}

?>