<?php
include 'Hybridauth/autoload.php';
include 'config.php';

use Hybridauth\Hybridauth;

$hybridauth = new Hybridauth($config);
$adapters = $hybridauth->getConnectedAdapters();

//function buildOverpassApiUrl($bounds, $overpassQuery)
function buildOverpassApiUrl($overpassQuery)
{
  //$bbox = $bounds->getSouth() + ',' + $bounds->getWest() + ',' + $bounds->getNorth() + ',' + $bounds->getEast();
  $bbox = "46.01174695991618,14.387283325195314,46.130839162824444,14.634475708007814";

  $nodeQuery = 'node[' . $overpassQuery . '](' . $bbox . ');';
  $wayQuery = 'way[' . $overpassQuery . '](' . $bbox . ');';
  $relationQuery = 'relation[' . $overpassQuery . '](' . $bbox . ');';
  $query = '?data=[out:json][timeout:15];(' . $nodeQuery . $wayQuery . $relationQuery . ');out center;';
  $baseUrl = 'https://overpass-api.de/api/interpreter';
  $resultUrl = $baseUrl . $query;

  return $resultUrl;
}

function getLocations()
{
  // http://overpass-api.de/api/interpreter/?data=(node[amenity=restaurant](bbox);way[amenity=restaurant](bbox);rel[amenity=restaurant](bbox););(._;%3E;);out%20center;&bbox=14.427296157835,46.020814448889,14.536129470825,46.139649267349

  //$poiUrl = buildOverpassApiUrl($refs->map->mapObject->getBounds(), "amenity~'bar|cafe|pub|restaurant'");
  $poiUrl = buildOverpassApiUrl("amenity~'bar|cafe|pub|restaurant'");

  $poiUrl = str_replace(" ", "%20", $poiUrl);

  $html = file_get_contents($poiUrl);

  $result = json_decode($html);
  $elements = $result->elements;
  
  $ids = [];
  $locations = [];

  foreach($elements as $element)
  {
    if (!array_key_exists($element->id, $ids))
    {
      $ids[$element->id] = new stdClass();

      $name = "";
      if(property_exists($element->tags, "name"))
      {
        $name = $element->tags->name;
      }
      else if(property_exists($element->tags, "amenity"))
      {
        $name = $element->tags->amenity;
      }

      if(property_exists($element, "lat") && property_exists($element, "lon"))
      {
        $locationNode = [ "address" => $name, "marker" => [$element->lat, $element->lon] ];
        array_push($locations, $locationNode);
        $ids[$element->id] = $locationNode;
      }
      else if(property_exists($element, "center") && property_exists($element->center, "lat") && property_exists($element->center, "lon"))
      {
        $locationWay = [ "address" => $name, "marker" => [$element->center->lat, $element->center->lon] ];
        array_push($locations, $locationWay);
        $ids[$element->id] = $locationWay;        
      }
    }
  }

  $_SESSION['ids'] = $ids;
  $_SESSION['locations'] = $locations;
}

function initialize()
{
  if (!isset($_SESSION['initialized']))
  {
    $_SESSION['initialized'] = true;

    getLocations();

    //echo 'alert("initialized")';
  }
}

  /*
  // How to send a GET request from PHP?
  $xml = file_get_contents("http://www.example.com/file.xml");

  // How do I send a POST request with PHP?
  $url = 'http://server.com/path';
  $data = array('key1' => 'value1', 'key2' => 'value2');
  // use key 'http' even if you send the request to https://...
  $options = array(
      'http' => array(
          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
          'method'  => 'POST',
          'content' => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) {
    // Handle error
  }
  var_dump($result);
  /**/

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-139586901-6"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-139586901-6');
    </script>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-WZDBQRG');</script>
    <!-- End Google Tag Manager -->

    <script src="https://cdn.jsdelivr.net/npm/vue-recaptcha@1.2.0/dist/vue-recaptcha.min.js"></script>

    <script src="https://www.google.com/recaptcha/api.js?onload=vueRecaptchaApiLoaded&render=explicit" async defer></script>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5"/>
    <meta name="theme-color" content="#448AFF">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="#448AFF">
    <meta name="apple-mobile-web-app-title" content="Riichi mahjong, Go, Shogi">
    <meta name="description" content="Play Riichi mahjong, Go, Shogi in Ljubljana, Slovenia.">

    <title>Play Riichi mahjong, Go, Shogi in Ljubljana, Slovenia</title>

    <meta property="og:type" content="website" />
    <meta property="og:title" content="Riichi mahjong, Go, Shogi" />
    <meta property="og:description" content="Play Riichi mahjong, Go, Shogi in Ljubljana, Slovenia." />
    <meta property="og:image" content="https://play.riichi-mahjong-go-shogi.si/thumbnail.png" />
    <meta property="og:url" content="https://play.riichi-mahjong-go-shogi.si/" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="Riichi mahjong, Go, Shogi">
    <meta name="twitter:description" content="Play Riichi mahjong, Go, Shogi in Ljubljana, Slovenia.">
    <meta name="twitter:image" content="https://play.riichi-mahjong-go-shogi.si/thumbnail.png">

    <link rel="manifest" href="manifest.json">
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <link rel='stylesheet' href="https://fonts.googleapis.com/css?family=Roboto Mono">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.5.1/dist/leaflet.css">
    <link rel="stylesheet" href="index.css?v=0.1" type="text/css"/>
    <!--
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/vue-ctk-date-time-picker@1.4.1/dist/vue-ctk-date-time-picker.css">
    -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/vue-datetime@1.0.0-beta.10/dist/vue-datetime.min.css">
  </head>
  <body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WZDBQRG"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <script>
      <?php initialize(); ?>
    </script>

    <style>
      .g-signin-button {
        display: inline-block;
        width: 100%;
        max-width: 340px;
        text-align: center;

        color: white;
        background-color: red;
        font-size: 16px;
        border-radius: 10px;
        margin: 5px;
        padding: 10px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
      }
    </style>
    <style>
      .fb-signin-button {
        display: inline-block;
        width: 100%;
        max-width: 340px;
        text-align: center;

        color: white;
        background-color: #3b5998;
        font-size: 16px;
        border-radius: 10px;
        margin: 5px;
        padding: 10px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
      }
    </style>

    <div id="app">

      <img src="icon128x128.png" alt="logo">
      
      <div id="inner">
        <h1>Riichi mahjong, Go, Shogi</h1>

        <!--
        <div class="wikipedia">Play <a target="_blank" rel="noopener noreferrer" href="https://en.wikipedia.org/wiki/Japanese_Mahjong">Mahjong</a> in <a target="_blank" rel="noopener noreferrer" href="https://en.wikipedia.org/wiki/Ljubljana">Ljubljana, Slovenia</a></div>
        <div class="wikipedia">Play <a target="_blank" rel="noopener noreferrer" href="https://en.wikipedia.org/wiki/Go_(game)">Go</a> in <a target="_blank" rel="noopener noreferrer" href="https://en.wikipedia.org/wiki/Ljubljana">Ljubljana, Slovenia</a></div>
        <div class="wikipedia">Play <a target="_blank" rel="noopener noreferrer" href="https://en.wikipedia.org/wiki/Shogi">Shogi</a> in <a target="_blank" rel="noopener noreferrer" href="https://en.wikipedia.org/wiki/Ljubljana">Ljubljana, Slovenia</a></div>
        -->

        <h2>About me</h2>

        <div class="game">Hi, there! My name is Urban and I am a fan of table top games played in Japan:</div>
        <div class="game">- <a href="mahjong.html">Mahjong</a></div>
        <div class="game">- <a href="go.html">Go</a></div>
        <div class="game">- <a href="shogi.html">Shogi</a></div>
        <div class="game">If you would like to play any of these games, but don't have anyone to play with (or you are interested in learning how to play), you can choose a date and place and I will reply to you.</div>

        <h2>1.) Sign in:</h2>

        <div style="font-size: 12pt;">

        <?php if ($adapters) : ?>
            <?php foreach ($adapters as $name => $adapter) : ?>
                <p>
                    <strong><?php print $adapter->getUserProfile()->displayName; ?></strong>
                    <?php print $adapter->getUserProfile()->email; ?> from
                    <i><?php print $name; ?></i>
                    <span>(<a href="<?php print $config['callback'] . "?logout={$name}"; ?>">Log Out</a>)</span>
                </p>
            <?php endforeach; ?>
        <?php else: ?>
          <?php foreach ($hybridauth->getProviders() as $name) : ?>
              <?php if (!isset($adapters[$name])) : ?>
                  <a class="<?php print $name ?>" href="<?php print $config['callback'] . "?provider={$name}"; ?>">
                      <i class="<?php print $config['providers'][$name]['icon']; ?>"></i>
                      Sign in with <strong><?php print $name; ?></strong>
                  </a>
              <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
        </div>

        <h2>2.) Select a game:</h2>

        <div class="game">I want to play:</div>
        <div class="game">
          <input type="checkbox" id="mahjong" value="Mahjong" v-model="checkedGames">
          <label for="mahjong">Mahjong</label>
        </div>
        <div class="game">
          <input type="checkbox" id="go" value="Go" v-model="checkedGames">
          <label for="go">Go</label>
        </div>
        <div class="game">
          <input type="checkbox" id="shogi" value="Shogi" v-model="checkedGames">
          <label for="shogi">Shogi</label>
        </div>

        <!--
        <div>
          <span>to play:</span>
          <span v-for="game in checkedGames"> {{game}}</span>
        </div>
        -->
        
        <h2>3.) Pick date and time:</h2>

        <div class="game">Date:</div>
        <datetime ref="picker" type="date" v-model="dateTime" input-id="datetime" v-on:close="dateSelected" ></datetime>

        <!--
        <vue-ctk-date-time-picker ref="picker" label="Pick a date!" v-model="dateTime" range></vue-ctk-date-time-picker>
        -->

        <div v-if="dateTime != ''">
          <div class="game">Time:</div>
          <datetime ref="picker" type="time" v-model="dateTime" input-id="datetime" :min-datetime="minDatetime" :max-datetime="maxDatetime"></datetime>
        </div>

        <h2>4.) Pick a location:</h2>

        <select v-model="locationIndex" id="location">
          <option v-for="(location, index) in locations" v-bind:value="index">{{ location.address }}</option>
        </select>

        <template>
          <l-map :zoom="zoom" :center="center" style="height: 500px;" ref="map">
            <l-tile-layer :url="url"></l-tile-layer>
            <template v-for="(location, index) in locations">
              <l-marker :lat-lng="location.marker" v-on:click="markerClick(index)" :icon="location.icon">
                <v-popup :content="location.address"></v-popup>
              </l-marker>
            </template>
          </l-map>
        </template>

        <!--
        <h2>4.) Sign in:</h2>

        <div v-if="!signedIn">
          <template>
            <g-signin-button
              :params="googleSignInParams"
              @success="onGoogleSignInSuccess"
              @error="onGoogleSignInError">
              <i class="fab fa-google"></i>
              Sign in with Google
            </g-signin-button>
          </template>
          <template>
            <fb-signin-button
              :params="facebookSignInParams"
              @success="onFacebookSignInSuccess"
              @error="onFacebookSignInError">
              <i class="fab fa-facebook-f"></i>
              Sign in with Facebook
            </fb-signin-button>
          </template>
        </div>
        <button v-if="signedIn" id="sign-out" v-on:click="signOut()">Sign out: {{userName}}</button>
        -->

        <script>
        function formatDate(date) {
          var day = date.getDate();
          day = day < 10 ? ' ' + day : day;
          var month = date.getMonth() + 1;
          month = month < 10 ? '0' + month : month;
          var hours = date.getHours();
          hours = hours < 10 ? ' ' + hours : hours;
          var minutes = date.getMinutes();
          minutes = minutes < 10 ? '0' + minutes : minutes;
          return day + "." + month + "." + date.getFullYear() + " " + hours + ':' + minutes;
        }
        </script>

        <h2>5.) Confirm:</h2>

        <?php if ($adapters) : ?>
          <vue-recaptcha
            v-if="checkedGames.length != 0 && dateTime != '' && locationIndex != -1"
            style="display: inline-block; padding: 10px"
            ref="recaptcha"
            @verify="onVerify"
            @expired="onExpired"
            :sitekey="sitekey">
          </vue-recaptcha>
        <?php else: ?>
          <div class="game">You have to sign in!</div>
        <?php endif; ?>

        <div class="game" v-if="checkedGames.length == 0">You have to choose a game!</div>
        <div class="game" v-if="checkedGames.length != 0">Game:<span v-for="game in checkedGames"> {{game}}</span></div>

        <div class="game" v-if="dateTime == ''">You have to choose a date!</div>
        <div class="game" v-if="dateTime != ''">Date: {{formatDate(new Date(dateTime))}}</div>

        <div class="game" v-if="locationIndex == -1">You have to choose a location!</div>
        <div class="game" v-if="locationIndex != -1">Location: {{locations[locationIndex].address}}</div>

        <!--
        <button @click="resetRecaptcha"> Reset ReCAPTCHA </button>
        -->
      </div>
    </div>

    <noscript>Sorry, your browser does not support JavaScript!</noscript>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/cookie-bar/cookiebar-latest.min.js?theme=altblack"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/1.5.1/vue-resource.min.js"></script>
    <!--
    <script src="https://cdn.jsdelivr.net/npm/vue-ctk-date-time-picker@1.4.1/dist/vue-ctk-date-time-picker.umd.min.js" charset="utf-8"></script>
    -->
    <script src="https://cdn.jsdelivr.net/npm/luxon@1.13.2/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/weekstart@1.0.0/dist/commonjs/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-datetime@1.0.0-beta.10/dist/vue-datetime.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.5.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue2-leaflet@2.1.1/dist/vue2-leaflet.min.js"></script>

    <script src="https://apis.google.com/js/api:client.js"></script>
    <script src="vue-google-signin-button.min.js"></script>
    <script>
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '2277365205653600',
          cookie     : true,  // enable cookies to allow the server to access the session
          xfbml      : true,  // parse social plugins on this page
          version    : 'v2.8' // use graph api version 2.8
        });
      };
      (function(doc, tag, id) {
        var js, firstjs = doc.getElementsByTagName(tag)[0];
        if (doc.getElementById(id)) {
          return;
        }
        js = doc.createElement(tag);
        js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        firstjs.parentNode.insertBefore(js, firstjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>
    <script src="vue-facebook-signin-button.min.js"></script>
    <script src="index.js"></script>
	  <script>
      if ("serviceWorker" in navigator) {
        window.addEventListener("load", function() {
          navigator.serviceWorker.register("worker.js").then(function(registration) {
            console.log("ServiceWorker registration successful with scope: ", registration.scope);
          }, function(err) {
            console.log("ServiceWorker registration failed: ", err);
          });
        });
      }
    </script>
  </body>
</html>