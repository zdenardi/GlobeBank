<?php

  // Performs all actions necessary to log in an admin
  function log_in_admin($admin) {
  // Renerating the ID protects the admin from session fixation.
    session_regenerate_id();
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['last_login'] = time();
    $_SESSION['username'] = $admin['username'];

    return true;
  }
function log_out_admin(){
  unset($_SESSION['admin_id']);
  unset($_SESSION['last_login']);
  unset($_SESSION['username']);

  //session_destroy(); optional, destroys whole sessions
  return true;
}
//is logged in() contains logic to check if request should be considered a "logged in " request or not
//It is the core of require_login(), but can be called on its own in other contexts (i.g display one link
//if an admin is logged in and display another if not)
function is_logged_in(){
  //having an admin_id in session serves dual purpose
  //-It's presence indicates the admin is logged in
  //-Its value tells which admin for looking up their record.
  return isset($_SESSION['admin_id']);
}

// call require login() at the top of any page that requires
//a valid login before access to the page

function require_login() {
  if(!is_logged_in()){
    redirect_to(url_for('/staff/login.php'));
  } else{
    //do nothing, let the page load
  }
}

?>
