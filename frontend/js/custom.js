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
  });

  app.route({
    view: "playlist",
    load: "playlist.html",
  });

  app.route({
    view: "home",
    load: "./test.html",
  });

  // run app
  app.run();

  // debug helper
  console.log('spapp initialized — defaultView hash:', window.location.hash, 'app object:', app);
});