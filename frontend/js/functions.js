// functions.js - centralized jQuery handlers for login/register/admin
(function($){
  'use strict';

  function showAlert(container, message, type){
    var $c = $(container);
    var $alert = $('<div class="alert alert-' + (type||'info') + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
    $c.prepend($alert);
    setTimeout(function(){ $alert.alert('close'); }, 3000);
  }

  // helper: generate candidate backend base paths based on current location
  function getBackendCandidates(){
    var p = window.location.pathname || '/';
    var candidates = [];
    try{
      var idx = p.indexOf('/frontend');
      if (idx !== -1) candidates.push(p.substring(0, idx) + '/backend');
    }catch(e){}
    try{
      // remove last path segment and append /backend
      var removed = p.replace(/\/[^\/]*$/, '');
      if (removed && removed !== p) candidates.push(removed + '/backend');
    }catch(e){}
    // common fallback possibilities
    candidates.push('/WebProgramming/WebProgramming/backend');
    candidates.push('/WebProgramming/backend');
    candidates.push('/backend');
    // normalize and uniquify
    var seen = {};
    return candidates.map(function(c){ return c.replace(/\/+$/, ''); }).filter(function(c){ if(seen[c]) return false; seen[c]=1; return true; });
  }

  // ajax with fallback tries candidate bases until one succeeds or all fail
  function ajaxWithFallback(opts){
    var candidates = getBackendCandidates();
    var i = 0;
    function tryNext(){
      if (i >= candidates.length){
        if (opts.error) opts.error({ message: 'All backend candidates failed' });
        return;
      }
      var base = candidates[i++];
      var url = base + opts.path;
      console.debug('Trying backend URL:', url);
      $.ajax($.extend({}, opts.ajaxOpts, { url: url }))
        .done(function(){ if (opts.success) opts.success.apply(this, arguments); })
        .fail(function(jqXHR, textStatus, errorThrown){
          var status = jqXHR && jqXHR.status ? jqXHR.status : 0;
          console.warn('Request failed for', url, textStatus, status);
          // If resource not found or network error, try next candidate. Otherwise treat as final error (server responded 4xx/5xx).
          if (status === 0 || status === 404) {
            tryNext();
          } else {
            if (opts.error) opts.error(jqXHR);
          }
        });
    }
    tryNext();
  }

  // runtime flags
  var __loginBound = false;
  var __registerBound = false;

  // delegated fallback: if the direct binding didn't run, this ensures login still works
  $(document).off('submit.functions.delegate').on('submit.functions.delegate', '#loginForm', function(e){
    // if the normal initLogin bound the handler, skip delegated logic
    if (__loginBound) return;
    console.log('delegated login submit invoked (fallback)');
    e.preventDefault();
    var email = $('#loginEmail').val();
    var password = $('#loginPassword').val();
    ajaxWithFallback({
      path: '/auth/login',
      ajaxOpts: { method: 'POST', contentType: 'application/json', data: JSON.stringify({ email: email, password: password }), dataType: 'json' },
      success: function(json){
        if (json && json.data && json.data.token){
          var token = json.data.token;
          var user = $.extend({}, json.data);
          delete user.token;
          localStorage.setItem('token', token);
          localStorage.setItem('user', JSON.stringify(user));
          localStorage.setItem('role', user.role || 'user');
          localStorage.setItem('isAdmin', (user.role === 'admin') ? '1' : '0');
          showAlert('.spapp-auth-card', 'Logged in successfully.', 'success');
          if (window.AppAuth && typeof window.AppAuth.updateUserPanel === 'function') window.AppAuth.updateUserPanel();
          setTimeout(function(){ window.location.hash = 'home'; }, 1200);
        } else {
          showAlert('.spapp-auth-card', 'Login failed: ' + (json.error || json.message || 'Unknown error'), 'danger');
        }
      },
      error: function(){ showAlert('.spapp-auth-card', 'Login error: backend unreachable', 'danger'); }
    });
  });

  // delegated fallback for register form
  $(document).off('submit.functions.delegate.register').on('submit.functions.delegate.register', '#registerForm', function(e){
    if (__registerBound) return;
    console.log('delegated register submit invoked (fallback)');
    e.preventDefault();
    var name = $('#registerUsername').val();
    var email = $('#registerEmail').val();
    var password = $('#registerPassword').val();
    ajaxWithFallback({
      path: '/auth/register',
      ajaxOpts: { method: 'POST', contentType: 'application/json', data: JSON.stringify({ name: name, email: email, password: password }), dataType: 'json' },
      success: function(json){
        if (json && json.data){
          showAlert('.spapp-auth-card', 'Registration successful — please login', 'success');
          setTimeout(function(){ window.location.hash = 'login'; }, 800);
        } else if (json && json.error) {
          showAlert('.spapp-auth-card', 'Registration failed: ' + json.error, 'danger');
        } else {
          showAlert('.spapp-auth-card', 'Registration response: ' + JSON.stringify(json), 'info');
        }
      },
      error: function(){ console.error('Register all attempts failed'); showAlert('.spapp-auth-card', 'Register error: backend unreachable', 'danger'); }
    });
  });

  function initLogin(){
    var $form = $('#loginForm');
    if (!$form.length) return;
    console.log('initLogin: binding login form handlers');
    
    $form.off('submit.functions').on('submit.functions', function(e){
      console.log('loginForm submit handler invoked');
      e.preventDefault();
      var email = $('#loginEmail').val();
      var password = $('#loginPassword').val();
      ajaxWithFallback({
        path: '/auth/login',
        ajaxOpts: { method: 'POST', contentType: 'application/json', data: JSON.stringify({ email: email, password: password }), dataType: 'json' },
        success: function(json){
        if (json && json.data && json.data.token){
          var token = json.data.token;
          var user = $.extend({}, json.data);
          delete user.token;
          localStorage.setItem('token', token);
          localStorage.setItem('user', JSON.stringify(user));
          localStorage.setItem('role', user.role || 'user');
          localStorage.setItem('isAdmin', (user.role === 'admin') ? '1' : '0');

          showAlert('.spapp-auth-card', 'Logged in successfully.', 'success');
          if (window.AppAuth && typeof window.AppAuth.updateUserPanel === 'function') window.AppAuth.updateUserPanel();
          setTimeout(function(){ window.location.hash = 'home'; }, 1200);
        } else {
          showAlert('.spapp-auth-card', 'Login failed: ' + (json.error || json.message || 'Unknown error'), 'danger');
        }
        },
        error: function(err){
          console.error('Login all attempts failed', err);
          showAlert('.spapp-auth-card', 'Login error: backend unreachable', 'danger');
        }
      });
    __loginBound = true;
    });
  }

  function initRegister(){
    var $form = $('#registerForm');
    if (!$form.length) return;
    console.log('initRegister: binding register form handlers');
    $form.off('submit.functions').on('submit.functions', function(e){
      console.log('registerForm submit handler invoked');
      e.preventDefault();
      var name = $('#registerUsername').val();
      var email = $('#registerEmail').val();
      var password = $('#registerPassword').val();
      ajaxWithFallback({
        path: '/auth/register',
        ajaxOpts: { method: 'POST', contentType: 'application/json', data: JSON.stringify({ name: name, email: email, password: password }), dataType: 'json' },
        success: function(json){
          if (json && json.data){
            showAlert('.spapp-auth-card', 'Registration successful — please login', 'success');
            setTimeout(function(){ window.location.hash = 'login'; }, 800);
          } else if (json && json.error) {
            showAlert('.spapp-auth-card', 'Registration failed: ' + json.error, 'danger');
          } else {
            showAlert('.spapp-auth-card', 'Registration response: ' + JSON.stringify(json), 'info');
          }
        },
        error: function(err){ console.error('Register all attempts failed', err); showAlert('.spapp-auth-card', 'Register error: backend unreachable', 'danger'); }
      });
    __registerBound = true;
    });
  }

  var adminTable = null;
  function initAdmin(){
    var $table = $('#usersTable');
    if (!$table.length) return;

    // initialize or re-init DataTable
    if ($.fn.DataTable === undefined){
      console.error('DataTables plugin not loaded');
      return;
    }
    if (adminTable){
      try{ adminTable.destroy(); $table.empty(); }catch(e){/*ignore*/}
    }
    adminTable = $table.DataTable({
      columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'role' },
        { data: null, orderable: false, defaultContent: '' }
      ],
      pageLength: 10
    });

    function loadUsers(){
      ajaxWithFallback({
        path: '/users',
        ajaxOpts: { method: 'GET', dataType: 'json' },
        success: function(resp){
          var users = resp.data || resp;
          adminTable.clear();
          users.forEach(function(u){ adminTable.row.add(u); });
          adminTable.draw();
          // add action buttons
          $('#usersTable tbody tr').each(function(){
            var $tr = $(this); var data = adminTable.row($tr).data();
            if (!data) return;
            var actions = '<button class="btn btn-sm btn-primary btn-edit me-1" data-id="'+data.id+'">Update</button>' +
                          '<button class="btn btn-sm btn-danger btn-delete" data-id="'+data.id+'">Delete</button>';
            $tr.find('td').last().html(actions);
          });
        }, error: function(){ alert('Failed to load users'); } });
    }

    // initial load
    loadUsers();

    // delete handler
    $table.off('click.functions', '.btn-delete').on('click.functions', '.btn-delete', function(){
      var id = $(this).data('id');
      if (!confirm('Delete user ID ' + id + '?')) return;
      ajaxWithFallback({ path: '/users/' + id, ajaxOpts: { method: 'DELETE' }, success: function(){ alert('Deleted'); loadUsers(); }, error: function(){ alert('Delete failed'); } });
    });

    // edit handler
    $table.off('click.functions', '.btn-edit').on('click.functions', '.btn-edit', function(){
      var id = $(this).data('id');
      ajaxWithFallback({ path: '/users/' + id, ajaxOpts: { method: 'GET', dataType: 'json' }, success: function(resp){
        var user = resp.data || resp;
        $('#editUserId').val(user.id);
        $('#editUserName').val(user.name || '');
        $('#editUserEmail').val(user.email || '');
        $('#editUserRole').val(user.role || 'user');
        $('#editUserPassword').val('');
        var modalEl = document.getElementById('editUserModal');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
      }, error: function(){ alert('Failed to fetch user'); } });
    });

    // save changes
    $('#saveUserBtn').off('click.functions').on('click.functions', function(){
      var id = $('#editUserId').val();
      var payload = {
        name: $('#editUserName').val(),
        email: $('#editUserEmail').val(),
        role: $('#editUserRole').val()
      };
      var pwd = $('#editUserPassword').val(); if (pwd) payload.password = pwd;
      ajaxWithFallback({ path: '/users/' + id, ajaxOpts: { method: 'PUT', contentType: 'application/json', data: JSON.stringify(payload) }, success: function(){ alert('Saved'); var modalEl = document.getElementById('editUserModal'); var modal = bootstrap.Modal.getInstance(modalEl); if(modal) modal.hide(); loadUsers(); }, error: function(){ alert('Update failed'); } });
    });
  }

  function initCategory(){
    var $container = $('.songs-section .container');
    if (!$container.length) return;
    console.log('initCategory: loading tracks from backend');

    // add a visible loader so user sees activity
    var $loader = $container.find('.category-loading');
    if (!$loader.length) { $loader = $('<div class="category-loading" style="padding:14px; text-align:center;">Loading tracks…</div>'); $container.prepend($loader); }

    ajaxWithFallback({
      path: '/tracks',
      ajaxOpts: { method: 'GET', dataType: 'json' },
      success: function(resp){
        $loader.remove();
        var tracks = resp.data || resp || [];
        if (!Array.isArray(tracks)) tracks = [tracks];

        // remove existing song-item blocks only when we have real data
        if (tracks.length) {
          $container.find('.song-item').remove();
        }

        var $pagination = $container.find('.site-pagination');
        tracks.forEach(function(t, idx){
          var artists = t.artists || t.artist || 'Unknown';
          var title = t.name || t.title || 'Untitled';
          var img = t.image || ('img/songs/' + ((idx%8)+1) + '.jpg');
          var preview = t.preview_url || t.external_url || 'music-files/1.mp3';
          var jpAncestor = '.jp_container_cat_' + idx;

          var html = '\n<div class="song-item">\n  <div class="row">\n    <div class="col-lg-4">\n      <div class="song-info-box">\n        <img src="'+img+'" alt="">\n        <div class="song-info">\n          <h4>'+escapeHtml(artists)+'</h4>\n          <p>'+escapeHtml(title)+'</p>\n        </div>\n      </div>\n    </div>\n    <div class="col-lg-6">\n      <div class="single_player_container">\n        <div class="single_player">\n          <div class="jp-jplayer jplayer" data-ancestor="'+jpAncestor+'" data-url="'+preview+'"></div>\n          <div class="jp-audio '+jpAncestor.replace('.', '').trim()+'" role="application" aria-label="media player">\n            <div class="jp-gui jp-interface">\n              <div class="player_controls_box">\n                <button class="jp-prev player_button" tabindex="0"></button>\n                <button class="jp-play player_button" tabindex="0"></button>\n                <button class="jp-next player_button" tabindex="0"></button>\n                <button class="jp-stop player_button" tabindex="0"></button>\n              </div>\n              <div class="player_bars">\n                <div class="jp-progress">\n                  <div class="jp-seek-bar">\n                    <div>\n                      <div class="jp-play-bar"><div class="jp-current-time" role="timer" aria-label="time">0:00</div></div>\n                    </div>\n                  </div>\n                </div>\n                <div class="jp-duration ml-auto" role="timer" aria-label="duration">00:00</div>\n              </div>\n            </div>\n          </div>\n        </div>\n      </div>\n    </div>\n    <div class="col-lg-2">\n      <div class="songs-links">\n        <a href=""><img src="img/icons/p-1.png" alt=""></a>\n        <a href=""><img src="img/icons/p-2.png" alt=""></a>\n        <a href=""><img src="img/icons/p-3.png" alt=""></a>\n      </div>\n    </div>\n  </div>\n</div>\n';

          if ($pagination.length) {
            $pagination.before(html);
          } else {
            $container.append(html);
          }
        });
      },
      error: function(err){
        $loader.remove();
        console.error('initCategory: failed to load tracks', err);
        showAlert('.songs-section', 'Failed to load tracks from server', 'danger');
      }
    });
  }

  function handleRoute(hash){
    var clean = (hash || window.location.hash || '').replace(/^#/,'');
    if (!clean) clean = 'home';
    if (clean === 'login') initLogin();
    if (clean === 'register') initRegister();
    if (clean === 'admin') initAdmin();
    if (clean === 'category') initCategory();
  }

  $(function(){
    // run on initial load
    handleRoute(window.location.hash);
    // run on hash change
    window.addEventListener('hashchange', function(){ handleRoute(window.location.hash); });
  });

  // expose for testing/other modules
  window.AppFunctions = {
    initLogin: initLogin,
    initRegister: initRegister,
    initAdmin: initAdmin
    ,initCategory: initCategory
  };

  // simple HTML escaper to avoid injecting raw values
  function escapeHtml(text){
    if (text === null || text === undefined) return '';
    return String(text).replace(/[&<>"'`]/g, function(ch){ return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;', '`':'&#96;' }[ch]; });
  }

})(jQuery);
