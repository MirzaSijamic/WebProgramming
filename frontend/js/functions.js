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

  function handleRoute(hash){
    var clean = (hash || window.location.hash || '').replace(/^#/,'');
    if (!clean) clean = 'home';
    if (clean === 'login') initLogin();
    if (clean === 'register') initRegister();
    if (clean === 'admin') initAdmin();
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
  };

})(jQuery);
