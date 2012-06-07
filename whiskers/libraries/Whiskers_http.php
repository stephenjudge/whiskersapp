<?php

/**
 * HTTP Handling and Utility Functions
 */
class Whiskers_http {

  function getClientInfo() {
    require_once STACKIE_LIB.'/third-party/phpbrowscap/browscap/Browscap.php';
    $bc = new Browscap(STACKIE_LIB.'/third-party/phpbrowscap/browscap/');
    $client = $bc->getBrowser();
    return $client;
  }

  function getClientIP() {
    $sources = array(
      'HTTP_CLIENTADDRESS',
      'HTTP_X_FORWARDED_FOR',
      'HTTP_CLIENT_IP',
      'REMOTE_ADDR',
    );

    foreach ($sources as $source) {
      $ip = preg_replace('/(?:,.*)/', '', getenv($source));
      if ( ! empty($ip)) {
        return trim($ip);
      }
    }
    return FALSE;
  }

  function redirect($url, $code=302) {
    header("Location: " . url($url), $code);
    exit;
  }

  function processURL($url=NULL, $short_url=NULL) {
    if (empty($url)) {
      $url = $_SERVER['REQUEST_URI'];
    }

    # URL -> /:module/:service/:action/:meta
    $parsed = parse_url($url);
    $parts = explode('/', rtrim(ltrim(@$parsed['path'], '/'), '/'));
    if (isset($parsed['query'])) {
      $qs = explode('&', @$parsed['query']);
      array_walk($qs,
        create_function(
          '&$v,$k,&$u',
          '$item = explode(\'=\', $v);
           $u[$item[0]] = $item[1];'
        ), $query
      );
    }

    # meta is whatever is left after the action
    if (count($parts) > 3) {
      $meta = implode('/', array_slice($parts, 3));
    }

    # is this a short url request?
    if ( ! empty($short_url)) {
      if (stristr(WhiskersHttp::here(), $short_url) AND ($parts[0] != 'shorturl')) {
        array_unshift($parts, 'shorturl');
      }
    }

    return array(
      'path'    => $url,
      'protocol'=> isset($parsed['scheme']) ? $parsed['scheme'] : ($_SERVER['SERVER_PORT'] == 80 ? 'http' : 'https'),
      'host'    => isset($parsed['host']) ? $parsed['host'] : $_SERVER['SERVER_NAME'],
      'module'  => @$parts[0],
      'service' => @$parts[1],
      'action'  => @$parts[2],
      'meta'    => @$meta,
      'query'   => @$query,
    );
  }

  function getURL($path, $domain=STACKIE_URL, $lowercased=TRUE) {
    $domain = rtrim($domain, '/');
    $path = ltrim($path, '/');
    $path = $lowercased ? low($path) : $path;
    return $domain .'/'.$path;
  }

  function get($url, &$response, $user=NULL, $pass=NULL) {
    return WhiskersHttp::curl($url, $response, 'GET', NULL, $user, $pass);
  }

  function post($url, &$response, $data=NULL, $user=NULL, $pass=NULL) {
    return WhiskersHttp::curl($url, $response, 'POST', $data, $user, $pass);
  }

  function curl($url, &$response, $method='GET', $data=NULL, $user=NULL, $pass=NULL) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($c, CURLOPT_TIMEOUT, 30);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($c, CURLOPT_URL, $url);
    switch ($method) {
      case 'GET':
        break;
      case 'POST':
        curl_setopt($c, CURLOPT_POST, TRUE);
        break;
      default:
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
    }
    if ( ! empty($user) AND ! empty($pass) ) {
      curl_setopt($c, CURLOPT_USERPWD, $user . ":" . $pass);
    }
    if ( ! empty($data)) {
      curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    }
    $response = curl_exec($c);
    $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close ($c);
    return $code;
  }

  function toQueryString($args) {
    foreach ($args as $k => $v) {
      if (empty($v) AND ($v !== 0)) {
        continue;
      }
      $_args[] = urlencode($k).'='.urlencode($v);
    }
    return implode('&',$_args);
  }

  function error404() {
    header("HTTP/1.0 404 Not Found");
    echo '404';
    // if ($this->config['debug']) pr(debug_backtrace());
    die();
  }

  function rebuildUrl($url) {
    if (empty($url)) {
      return '';
    }
    $parts = parse_url($url);
    if (empty($parts['scheme'])) {
      $parts['scheme'] = 'http';
    }

    $url = $parts['scheme'] .'://'. @$parts['host'];
    if ( ! empty($parts['port'])) {
      $url .= ':'.$parts['port'];
    }

    $url .= @$parts['path'];

    if ( ! empty($parts['query'])) {
      $url .= '?'.$parts['query'];
    }
    return $url;
  }

  function here() {
    return sprintf('%s://%s%s',
      $_SERVER['SERVER_PORT'] == 80 ? 'http' : 'https',
      $_SERVER['SERVER_NAME'],
      $_SERVER['REQUEST_URI']
    );
  }
}

