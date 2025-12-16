/* auth.js - helper for authentication UI and AJAX
   - stores/reads token and user from localStorage
   - sets Authorization header for jQuery AJAX
   - updates header user-panel with login/register or username/logout/admin links
*/
(function(window, $){
  'use strict';

  function getToken() {
    return localStorage.getItem('token');
  }

  function getUser() {
    try { return JSON.parse(localStorage.getItem('user') || '{}'); } catch(e) { return {}; }
  }

  function isAdmin() {
    return localStorage.getItem('isAdmin') === '1';
  }

  function logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('role');
    localStorage.removeItem('isAdmin');
    // refresh UI
    updateUserPanel();
    // go to home
    window.location.hash = 'home';
  }

  function updateAjaxAuthHeader() {
    var token = getToken();
    if (token && window.jQuery && $.ajaxSetup) {
      $.ajaxSetup({ headers: { 'Authorization': 'Bearer ' + token } });
    }
  }

  function updateUserPanel() {
    var $panel = $('.user-panel');
    if (!$panel.length) return;

    var token = getToken();
    if (!token) {
      $panel.html('<a href="#login" class="login">Login</a> <a href="#register" class="register">Create an account</a>');
      updateAjaxAuthHeader();
      return;
    }

    var user = getUser();
    var name = user.name || user.email || 'User';
    var adminLink = isAdmin() ? ' <a href="#admin" class="admin">Admin</a>' : '';
    $panel.html('<span class="logged-in">' + name + '</span>' + adminLink + ' <a href="#" id="logoutLink">Logout</a>');

    $('#logoutLink').on('click', function(e){ e.preventDefault(); logout(); });
    updateAjaxAuthHeader();
  }

  // initialize on DOM ready
  $(function(){
    updateUserPanel();
  });

  // export small API
  window.AppAuth = {
    getToken: getToken,
    getUser: getUser,
    isAdmin: isAdmin,
    logout: logout,
    updateUserPanel: updateUserPanel
  };

})(window, window.jQuery);
