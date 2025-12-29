// functions.js - router for hash-based navigation

(function($){
  'use strict';

  function initAdmin(){
    console.log('initAdmin: initializing via UserAdminService');
    if (UserAdminService && typeof UserAdminService.init === 'function') {
      UserAdminService.init();
    } else {
      console.error('UserAdminService.init not available');
    }
  }

  function initCategory(){
    console.log('initCategory: loading tracks via TrackService');
    if (TrackService && typeof TrackService.loadTracks === 'function') {
      TrackService.loadTracks();
    } else {
      console.error('TrackService.loadTracks not available');
    }
  }

  function initPlaylist(){
    console.log('initPlaylist: loading playlists via PlaylistService');
    if (PlaylistService && typeof PlaylistService.loadPlaylists === 'function') {
      PlaylistService.loadPlaylists();
    } else {
      console.error('PlaylistService.loadPlaylists not available');
    }
  }

  function handleRoute(hash){
    var clean = (hash || window.location.hash || '').replace(/^#/,'');
    if (!clean) clean = 'home';
    if (clean === 'admin') initAdmin();
    if (clean === 'category') initCategory();
    if (clean === 'playlist') initPlaylist();
  }

  $(function(){
    // run on initial load
    handleRoute(window.location.hash);
    // run on hash change
    window.addEventListener('hashchange', function(){ handleRoute(window.location.hash); });
  });

  // expose for testing/other modules
  window.AppFunctions = {
    initAdmin: initAdmin,
    initCategory: initCategory,
    initPlaylist: initPlaylist
  };

})(jQuery);
