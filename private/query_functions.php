<?php

  // Subjects

  function find_all_subjects($options = [])
  {
      global $db;
      $visible = $options['visible'] ?? false;


      $sql = "SELECT * FROM subjects ";
      if ($visible) {
          $sql .="WHERE visible = true ";
      }
      $sql .= "ORDER BY position ASC";
      //echo $sql;
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      return $result;
  }

  function find_subject_by_id($id, $options = [])
  {
      global $db;
      $visible = $options['visbile'] ?? false;
      $sql = "SELECT * FROM subjects ";
      $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
      if ($visible) {
          $sql .= "AND visible = true";
      }
      // echo $sql;
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      $subject = mysqli_fetch_assoc($result);
      mysqli_free_result($result);
      return $subject; // returns an assoc. array
  }

  function validate_subject($subject)
  {
      $errors = [];

      // menu_name
      if (is_blank($subject['menu_name'])) {
          $errors[] = "Name cannot be blank.";
      } elseif (!has_length($subject['menu_name'], ['min' => 2, 'max' => 255])) {
          $errors[] = "Name must be between 2 and 255 characters.";
      }

      // position
      // Make sure we are working with an integer
      $postion_int = (int) $subject['position'];
      if ($postion_int <= 0) {
          $errors[] = "Position must be greater than zero.";
      }
      if ($postion_int > 999) {
          $errors[] = "Position must be less than 999.";
      }

      // visible
      // Make sure we are working with a string
      $visible_str = (string) $subject['visible'];
      if (!has_inclusion_of($visible_str, ["0","1"])) {
          $errors[] = "Visible must be true or false.";
      }

      return $errors;
  }

  function insert_subject($subject)
  {
      global $db;

      $errors = validate_subject($subject);
      if (!empty($errors)) {
          return $errors;
      }
      
      $sql = "INSERT INTO subjects ";
      $sql .= "(menu_name, position, visible) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $subject['menu_name']) . "',";
      $sql .= "'" . db_escape($db, $subject['position']) . "',";
      $sql .= "'" . db_escape($db, $subject['visible']) . "'";
      $sql .= ")";
      shift_subject_positions(0, $subject['position'], 0);
      $result = mysqli_query($db, $sql);
      // For INSERT statements, $result is true/false
      if ($result) {
          return true;
      } else {
          // INSERT failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }

  function update_subject($subject)
  {
      global $db;

      $errors = validate_subject($subject);
      if (!empty($errors)) {
          return $errors;
      }
      $id = $subject['id'];
      $old_subject=find_subject_by_id($id);
      
      $sql = "UPDATE subjects SET ";
      $sql .= "menu_name='" . db_escape($db, $subject['menu_name']) . "', ";
      $sql .= "position='" . db_escape($db, $subject['position']) . "', ";
      $sql .= "visible='" . db_escape($db, $subject['visible']) . "' ";
      $sql .= "WHERE id='" . db_escape($db, $subject['id']) . "' ";
      $sql .= "LIMIT 1";
      shift_subject_positions($old_subject['position'], $subject['position'], $id);

      $result = mysqli_query($db, $sql);
      // For UPDATE statements, $result is true/false
      if ($result) {
          return true;
      } else {
          // UPDATE failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }

  function delete_subject($id)
  {
      global $db;
      $subject = find_subject_by_id($id);
      shift_subject_positions($subject['position'], 0, $subject['id']);
      $sql = "DELETE FROM subjects ";
      $sql .= "WHERE id='" . db_escape($db, $subject['id']) . "' ";
      $sql .= "LIMIT 1";
      $result = mysqli_query($db, $sql);

      // For DELETE statements, $result is true/false
      if ($result) {
        
          return true;
      } else {
          // DELETE failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }

  // Pages

  function find_all_pages($options = [])
  {
      global $db;
      $visible = $options['visible'] ?? false;
      $sql = "SELECT * FROM pages ";
      if ($visible) {
          $sql .= "WHERE visible = true ";
      }
      $sql .= "ORDER BY subject_id ASC, position ASC";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      return $result;
  }

  function find_page_by_id($id, $options = [])
  {
      global $db;
      $visible = $options['visible'] ?? false;
      $sql = "SELECT * FROM pages ";
      $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
      if ($visible) {
          $sql .= "AND visible = true";
      }
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      $page = mysqli_fetch_assoc($result);
      mysqli_free_result($result);
      return $page; // returns an assoc. array
  }

  function validate_page($page)
  {
      $errors = [];

      // subject_id
      if (is_blank($page['subject_id'])) {
          $errors[] = "Subject cannot be blank.";
      }

      // menu_name
      if (is_blank($page['menu_name'])) {
          $errors[] = "Name cannot be blank.";
      } elseif (!has_length($page['menu_name'], ['min' => 2, 'max' => 255])) {
          $errors[] = "Name must be between 2 and 255 characters.";
      }
      $current_id = $page['id'] ?? '0';
      if (!has_unique_page_menu_name($page['menu_name'], $current_id)) {
          $errors[] = "Menu name must be unique.";
      }


      // position
      // Make sure we are working with an integer
      $postion_int = (int) $page['position'];
      if ($postion_int <= 0) {
          $errors[] = "Position must be greater than zero.";
      }
      if ($postion_int > 999) {
          $errors[] = "Position must be less than 999.";
      }

      // visible
      // Make sure we are working with a string
      $visible_str = (string) $page['visible'];
      if (!has_inclusion_of($visible_str, ["0","1"])) {
          $errors[] = "Visible must be true or false.";
      }

      // content
      if (is_blank($page['content'])) {
          $errors[] = "Content cannot be blank.";
      }

      return $errors;
  }

  function insert_page($page)
  {
      global $db;

      $errors = validate_page($page);
      if (!empty($errors)) {
          return $errors;
      }

      $sql = "INSERT INTO pages ";
      $sql .= "(subject_id, menu_name, position, visible, content) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $page['subject_id']) . "',";
      $sql .= "'" . db_escape($db, $page['menu_name']) . "',";
      $sql .= "'" . db_escape($db, $page['position']) . "',";
      $sql .= "'" . db_escape($db, $page['visible']) . "',";
      $sql .= "'" . db_escape($db, $page['content']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
      // For INSERT statements, $result is true/false
      if ($result) {
          return true;
      } else {
          // INSERT failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }

  function update_page($page)
  {
      global $db;

      $errors = validate_page($page);
      if (!empty($errors)) {
          return $errors;
      }

      $sql = "UPDATE pages SET ";
      $sql .= "subject_id='" . db_escape($db, $page['subject_id']) . "', ";
      $sql .= "menu_name='" . db_escape($db, $page['menu_name']) . "', ";
      $sql .= "position='" . db_escape($db, $page['position']) . "', ";
      $sql .= "visible='" . db_escape($db, $page['visible']) . "', ";
      $sql .= "content='" . db_escape($db, $page['content']) . "' ";
      $sql .= "WHERE id='" . db_escape($db, $page['id']) . "' ";
      $sql .= "LIMIT 1";

      $result = mysqli_query($db, $sql);
      // For UPDATE statements, $result is true/false
      if ($result) {
          return true;
      } else {
          // UPDATE failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }

  function delete_page($id)
  {
      global $db;

      $sql = "DELETE FROM pages ";
      $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
      $sql .= "LIMIT 1";
      $result = mysqli_query($db, $sql);

      // For DELETE statements, $result is true/false
      if ($result) {
          return true;
      } else {
          // DELETE failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }

  function find_pages_by_subject_id($subject_id, $options=[])
  {
      global $db;

      $visible = $options['visible'] ?? false;

      $sql = "SELECT * FROM pages ";
      $sql .= "WHERE subject_id='" . db_escape($db, $subject_id) . "' ";
      if ($visible) {
          $sql .= "AND visible = true ";
      }
      $sql .= "ORDER BY position ASC";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      return $result; // returns an assoc. array
  }

  function count_pages_by_subject_id($subject_id, $options=[])
  {
      global $db;
  
      $visible = $options['visible'] ?? false;
  
      $sql = "SELECT COUNT(id) FROM pages ";
      $sql .= "WHERE subject_id='" . db_escape($db, $subject_id) . "' ";
      if ($visible) {
          $sql .= "AND visible = true ";
      }
      $sql .= "ORDER BY position ASC";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);

      $row= mysqli_fetch_row($result);
      mysqli_free_result($result);
      $count = $row[0];


      return $count; // returns
  }
  function count_subjects()
  {
      global $db;
  
      
  
      $sql = "SELECT COUNT(id) FROM subjects ";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);

      $row= mysqli_fetch_row($result);
      mysqli_free_result($result);
      $count = $row[0];


      return $count; // returns
  }

//Admins

  function find_all_admins()
  {
      global $db;
    
      $sql = "SELECT * FROM admins ";
      $sql .= "ORDER BY last_name ASC, first_name ASC";
      //echo $sql;
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      return $result;
  }

  function validate_admins($admin, $options=[])
  {
      $password_required=$options['password_required'] ?? true;
      $errors = [];

      // first_name
      if (is_blank($admin['first_name'])) {
          $errors[] = "First name cannot be blank.";
      } elseif (!has_length($admin['first_name'], ['min' => 2, 'max' => 255])) {
          $errors[] = "First name must be between 2 and 255 characters.";
      }

      // last_name
      if (is_blank($admin['last_name'])) {
          $errors[] = "Last name cannot be blank.";
      } elseif (!has_length($admin['last_name'], ['min' => 2, 'max' => 255])) {
          $errors[] = "Last name must be between 2 and 255 characters.";
      }
      //email
      if (is_blank($admin['email'])) {
          $errors[] ="Email cannot be blank.";
      } elseif (!filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) {
          $errors[]="Please provide a valid email.";
      }
      if (!has_length($admin['email'], ['min' => 2, 'max' => 255])) {
          $errors[] ="Email must be less than 255 characters";
      }

      //username
      if (is_blank($admin['username'])) {
          $errors[] ="Please enter a username!";
      } elseif (!has_length($admin['username'], ['min' => 8, 'max' => 255])) {
          $errors [] = "Username must have between 8 and 255 characters";
      }
      $admin_set= find_all_admins();
      while ($admin_db = mysqli_fetch_assoc($admin_set)) {
          $admin_db_username = $admin_db['username'];
          if ($admin_db_username === $admin['username']) {
              $errors [] = "Username already exists!";
          }
      }
      

      //password
      if ($password_required) {
          $uppercase = preg_match('@[A-Z]@', $admin['password']);
          $lowercase = preg_match('@[a-z]@', $admin['password']);
          $number = preg_match('@[0-9]@', $admin['password']);
          $specialChars = preg_match('@[^\w]@', $admin['password']);
        
          if (is_blank($admin['password'])) {
              $errors[]="Please provide a password";
          } elseif (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($admin['password']) < 4) {
              $errors []="Password must be : 4 Characters, Contain 1 uppercase, 1 lowercase, 1 number and one symbol";
          }
        
          if (is_blank($admin['confirm_password'])) {
              $errors[] = "Please confirm your password" ;
          } elseif ($admin['confirm_password'] !== $admin['password']) {
              $errors['Your password and confirmed password do not match.'];
          }
  
  
          return $errors;
      }
  }
  function insert_admin($admin)
  {
      global $db;

      $errors = validate_admins($admin);
      if (!empty($errors)) {
          return $errors;
      }

      $hashed_password = password_hash($admin['password'], PASSWORD_DEFAULT);

      $sql = "INSERT INTO admins ";
      $sql .= "(first_name, last_name, email, username, hashed_password) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $admin['first_name']) . "',";
      $sql .= "'" . db_escape($db, $admin['last_name']) . "',";
      $sql .= "'" . db_escape($db, $admin['email']) . "',";
      $sql .= "'" . db_escape($db, $admin['username']) . "',";
      $sql .= "'" . db_escape($db, $hashed_password) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
      // For INSERT statements, $result is true/false
      if ($result) {
          return true;
      } else {
          // INSERT failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }

  function find_admin_by_id($id)
  {
      global $db;
      
      $sql = "SELECT * FROM admins ";
      $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      $admin = mysqli_fetch_assoc($result);
      mysqli_free_result($result);
      return $admin; // returns an assoc. array
  }

  function update_admin($admin)
  {
      global $db;

      $password_sent = !is_blank($admin['password']);

      $errors = validate_admins($admin, ['password_required' => $password_sent]);
      if (!empty($errors)) {
          return $errors;
      }
      $hashed_password = password_hash($admin['password'], PASSWORD_DEFAULT);

      $sql = "UPDATE admins SET ";
      $sql .= "first_name='" . db_escape($db, $admin['first_name']) . "', ";
      $sql .= "last_name='" . db_escape($db, $admin['last_name']) . "', ";
      $sql .= "email='" . db_escape($db, $admin['email']) . "',";
      if ($password_sent) {
          $sql .= "hashed_password='" . db_escape($db, $hashed_password) . "', ";
      }
      $sql .= "username='" . db_escape($db, $admin['username']) . "' ";
      $sql .= "WHERE id='" . db_escape($db, $admin['id']) . "' ";
      $sql .= "LIMIT 1";

      $result = mysqli_query($db, $sql);
      // For UPDATE statements, $result is true/false
      if ($result) {
          return true;
      } else {
          // UPDATE failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }

  function delete_admin($id)
  {
      global $db;

      $sql = "DELETE FROM admins ";
      $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
      $sql .= "LIMIT 1";
      $result = mysqli_query($db, $sql);

      // For DELETE statements, $result is true/false
      if ($result) {
          return true;
      } else {
          // DELETE failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }
  function find_admin_by_username($username)
  {
      global $db;
      
      $sql = "SELECT * FROM admins ";
      $sql .= "WHERE username='" . db_escape($db, $username) . "' ";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      $admin = mysqli_fetch_assoc($result);
      mysqli_free_result($result);
      return $admin; // returns an assoc. array
  }


  //Helper Functions
  //Subject Position
  function shift_subject_positions($start_pos, $end_pos, $current_id)
  {
      global $db;
      
      if ($start_pos == 0) {
          //new subject, must add +1 to all subject['position']>=$end_pos
          $sql = "UPDATE subjects ";
          $sql .= "SET position = position +1 ";
          $sql .= "WHERE position >=" . $end_pos . " ";
      } elseif ($end_pos == 0) {
          //deleting subject, -1 to all subject['position]>$start_pos
          $sql = "UPDATE subjects ";
          $sql .= "SET position = position -1 ";
          $sql .= "WHERE position >=" . $start_pos . " ";
      } elseif ($start_pos < $end_pos) {
          //moving subject up positions, -1 subject['positions'] > $start_pos but <= $end_pos
        $sql = "UPDATE subjects ";
        $sql .= "SET position = position -1 ";
        $sql .= "WHERE position >" . $start_pos . " ";
        $sql .= "AND position <=". $end_pos . " ";
          

      } elseif ($start_pos > $end_pos) {
          //moving subject down positions, +1 subject['position'] > $start_pos but <= $end_pos
          $sql = "UPDATE subjects ";
        $sql .= "SET position = position +1 ";
        $sql .= "WHERE position <" . $start_pos . " ";
        $sql .= "AND position >=bnm,./". $end_pos . " ";
      }
      //make sure you excluse the subject you are working with which is $current_id
      $sql .= "AND id != $current_id";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      mysqli_free_result($result);
      if ($result) {
          return true;
      } else {
          // UPDATE failed
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
      }
  }
