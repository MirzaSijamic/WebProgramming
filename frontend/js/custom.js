$(document).ready(function () {

  // initialize and set templates directory to frontend/templates
  var app = $.spapp({ templateDir: 'templates/', defaultView:'home', pageNotFound: "error_404" }); // initialize

  app.route({
    view: "artist",
    load: "artist.html",
  });

  app.route({
    view: "blog",
    load: "blog.html",
  });

  app.route({
    view: "forms",
    load: "forms.html",
  });

  app.route({
    view: "category",
    load: "category.html",
    onReady: function() {
      console.debug('custom.js: category route onReady');
      var $sec = $('#category');
      console.debug('category section exists:', $sec.length, 'visible:', $sec.is(':visible'));
    }
  });

  app.route({
    view: "playlist",
    load: "playlist.html",
    onReady: function() { console.debug('custom.js: playlist route onReady'); if(window.AppFunctions && typeof window.AppFunctions.initPlaylist === 'function') window.AppFunctions.initPlaylist(); }
  });

  app.route({
    view: "home",
    load: "test.html",
    onCreate: function() {
      console.log('home onCreate: template is being loaded');
    },
    onReady: function() {
      console.log('home onReady: template loaded and inserted');
      // ensure any leftover preloader inside templates is removed
      var $p = $('#preloder');
      if($p.length) { $p.find('.loader').fadeOut(); $p.delay(100).fadeOut('fast', function(){ $p.remove(); }); }
    }
  });

  // ensure SPA route ready hooks call our centralized init functions
  app.route({
    view: 'login',
    load: 'login.html',
    onReady: function() { 
      console.debug('custom.js: login route onReady'); 
      UserService.setupLoginValidation();
    }
  });

  app.route({
    view: 'register',
    load: 'register.html',
    onReady: function() { 
      console.debug('custom.js: register route onReady'); 
      UserService.setupRegisterValidation();
    }
  });

  app.route({
    view: 'admin',
    load: 'admin.html',
    onReady: function() { console.debug('custom.js: admin route onReady'); if(window.AppFunctions && typeof window.AppFunctions.initAdmin === 'function') window.AppFunctions.initAdmin(); }
  });

  // run app
  app.run();

  // debug helper
  console.log('spapp initialized — defaultView hash:', window.location.hash, 'app object:', app);
});