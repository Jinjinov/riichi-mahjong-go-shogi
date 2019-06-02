
//Vue.component('vue-ctk-date-time-picker', window['vue-ctk-date-time-picker']);

Vue.component('l-map', window.Vue2Leaflet.LMap);
Vue.component('l-tile-layer', window.Vue2Leaflet.LTileLayer);
Vue.component('l-marker', window.Vue2Leaflet.LMarker);

var vm = new Vue({
  el: '#app',
  data: {
    dateTime: null,

    zoom: 12,
    center: L.latLng(46.054647,14.502405),
    url:'https://{s}.tile.osm.org/{z}/{x}/{y}.png',

    defaultIcon: L.icon({
      iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
      shadowUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34],
      shadowSize: [41, 41]
    }),
    selectedIcon: L.icon({
      iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
      shadowUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34],
      shadowSize: [41, 41]
    }),

    locationIndex: -1,
    locations:
    [
      { address: "Center", marker: L.latLng(46.054647,14.502405) },
      { address: "Dravlje", marker: L.latLng(46.095757,14.466376) }
    ],

    sitekey: '6LdXgqMUAAAAAOSLkNGWDN_jrd1EfGGVQTeVYwMU',

    checkedGames: [],

    googleSignInParams: {
      client_id: '633407910434-th0re56p064dbn05m3iiv96v83ftnk7n.apps.googleusercontent.com'
    },
    facebookSignInParams: {
      scope: 'email',
      return_scopes: true
    },

    userName: '',
    googleUserId: 0,
    facebookUserId: 0,
    userEmail: '',
    userImageUrl: '',

    signedIn: false,
  },
  components: {
    'vue-recaptcha': VueRecaptcha
  },
  watch: {
    locationIndex: function (newLocation, oldLocation) {
      if(oldLocation != -1) {
        this.locations[oldLocation].icon = this.defaultIcon;
      }
      this.locations[newLocation].icon = this.selectedIcon;
    }
  },
  methods: {
    markerClick: function (index) {
      //if(this.locationIndex != -1) {
      //  this.locations[this.locationIndex].icon = this.defaultIcon;
      //}
      this.locationIndex = index;
      //this.locations[this.locationIndex].icon = this.selectedIcon;
    },

    // recaptcha:

    onSubmit: function () {
      this.$refs.invisibleRecaptcha.execute()
    },
    onVerify: function (response) {
      console.log('Verify: ' + response)

      var postParam = { };

      postParam['g-recaptcha-response'] = response;

      this.$http.post('verify.php', postParam, {emulateJSON: true}).then(
        function(response) {
          if(response.body.success == true) {
            alert(response.body.message);
          }
          if(response.body.success == false) {
            alert(response.body.message);
          }
        },
        function(response) {
          console.log(response);
        }
      );
    },
    onExpired: function () {
      console.log('Expired')
    },
    resetRecaptcha () {
      this.$refs.recaptcha.reset() // Direct call reset method
    },

    // sign in:

    signOut: function() {
      if(this.googleUserId == 0 && this.facebookUserId == 0) {
        alert('this.googleUserId == 0 && this.facebookUserId == 0');
        return;
      }
      if(this.googleUserId != 0 && this.facebookUserId != 0) {
        alert('this.googleUserId != 0 && this.facebookUserId != 0');
        return;
      }
      if(this.googleUserId != 0) {
        window.gapi.auth2.getAuthInstance().signOut();

        this.signedIn = false;
        this.googleUserId = 0;
      }
      if(this.facebookUserId != 0) {
        FB.getLoginStatus(function(response) {
          if (response && response.status === 'connected') {
            FB.logout(function(response) {
              this.signedIn = false;
              this.facebookUserId = 0;
            });
          }
        });

        this.signedIn = false;
        this.facebookUserId = 0;
      }
    },
    onGoogleSignInSuccess: function(googleUser) {
      const profile = googleUser.getBasicProfile();
      this.userName = profile.getName();
      this.googleUserId = profile.getId();

      this.userEmail = profile.getEmail();
      this.userImageUrl = profile.getImageUrl();

      this.signedIn = true;
    },
    onGoogleSignInError: function(error) {
      console.log('OH NOES', error);
    },
    onFacebookSignInSuccess: function(response) {
      FB.api('/me', {fields: 'id,name,email,picture'}, facebookUser => {
        this.userName = facebookUser.name;
        this.facebookUserId = facebookUser.id;

        this.userEmail = facebookUser.email;
        this.userImageUrl = facebookUser.picture.data.url;

        this.signedIn = true;
      });
    },
    onFacebookSignInError: function(error) {
      console.log('OH NOES', error);
    },
  },
  mounted () {
    this.$watch(
      //"$refs.picker.isVisible",
      "$refs.picker.isOpen",
      (new_value, old_value) => {
        if(old_value == false && new_value == true) {
          this.$refs.map.$el.style.zIndex = -1;
        }
        if(old_value == true && new_value == false) {
          this.$refs.map.$el.style.zIndex = 0;
        }
      }
    );
    this.$nextTick(() => {
      //this.$refs.myMap.mapObject.ANY_LEAFLET_MAP_METHOD();
    })
  }
})