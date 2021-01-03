<?php
require_once('../../private/initialize.php');

$errors = [];
$username = '';
$password = '';

if(is_post_request()) {

  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $admin = find_admin_by_username($username);

  //validate form data
  if(is_blank($admin['username'])){
    $error[]="Username connot be blank";
  }
  if(is_blank($admin['hashed_password'])){
    $error[]="Password connot be blank";
  }
//if no errors, try login
  if(empty($errors)){
    //one var for login failures.
    $login_failure_msg = "Login was unsuccessful";

   
  }
  if($admin){

    if(password_verify($password, $admin['hashed_password'])){
      // password matches
      
  log_in_admin($admin);
  redirect_to(url_for('/staff/index.php'));
    } else {
      //username found, PW not match
      $errors[] = $login_failure_msg;
    }

  }
  else {
      //no username found
  $errors[] = $login_failure_msg;
  }

}

?>

<?php $page_title = 'Log in'; ?>
<?php include(SHARED_PATH . '/staff_header.php'); ?>

<div id="content">
    <h1>Log in</h1>

    <?php echo display_errors($errors); ?>

    <form action="login.php" method="post">
        Username:<br />
        <input type="text" name="username" value="<?php echo h($username); ?>" /><br />
        Password:<br />
        <input type="password" name="password" value="" /><br />
        <input type="submit" name="submit" value="Submit" />
    </form>

</div>

<?php include(SHARED_PATH . '/staff_footer.php'); ?>