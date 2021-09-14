<?php 
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];
        $result = ['status' => 'error']; 
        $user = @$_SESSION['loggedas'];
        switch($action):
            case "logout":
                session_destroy();
                session_start();
            break;
            case "register":
                $username = htmlentities(strtolower(trim($_POST['username'])));
                $fullname = htmlentities(strtolower(trim($_POST['fullname'])));
                $year = htmlentities(strtolower(trim($_POST['year'])));
                $password = md5($_POST['password']);
                if (strtolower($username) !== "admin") {
                    $query = "SELECT COUNT(*) as x FROM users WHERE username = ?;";
                    $userExists = (int) $DB->q($query, [$username])->fetch(PDO::FETCH_ASSOC)['x'];
                    if ($userExists > 0) 
                        $result["msg"] = "This user name is already in use!";
                    else {
                        if ($DB->q("INSERT INTO users(fullname, username, password, year) VALUES (?, ?, ?, ?);", [$fullname, $username, $password, $year]))
                            $result['status'] = "success";
                    }
                    

                } else {
                    $result["msg"] = "This user name is restricted!";
                }
                

            break;
            case "login":
                $username = htmlentities(strtolower(trim($_POST['username'])));
                $password = md5($_POST['password']);
              
                $query = "SELECT * FROM users WHERE username = ?;";
                $user = $DB->q($query, [$username])->fetch(PDO::FETCH_ASSOC);
                if ($user['password'] === $password) {
                    session_regenerate_id();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['loggedas'] = $username;
                    $_SESSION['priv'] = $user['priv'];
                    $result['status'] = "success"; 
                }
                
            break;
            case "change-pw":
                $user = @$_SESSION['loggedas'];
                $newpw = md5($_POST['newpw']);
                $password = md5($_POST['password']);
              
                $query = "SELECT * FROM users WHERE username = ? ;";
                $User = $DB->q($query, [$user])->fetch(PDO::FETCH_ASSOC); 
                if ($User['password'] == $password) {
                    if ($DB->q("UPDATE users SET password = ? WHERE username = ?;", [$newpw, $user])) {
                        session_destroy();
                        $result['status'] = "success"; 
                    }
                }
            break;  

            case "add-class":
                $code = htmlentities(trim($_POST['code']));
                $subject = htmlentities(trim($_POST['subject']));
                $size = $_POST['size'];
                $chaps = $_POST['chaps'];
                $batch = $_POST['batch'];
                $query = "SELECT * FROM classes WHERE code = ? AND added_by = ?;";
                if (count($DB->q($query, [$code, $user])->fetchAll(PDO::FETCH_ASSOC)) > 0) {
                    $result['msg'] = "Class code exists!";
                } else {
                    if ($DB->q("INSERT INTO classes (code, subject, size, chaps, added_by, batch) VALUES (?, ?, ?, ?, ?, ?);", [$code, $subject, $size, $chaps, $user, $batch])) {
                        $result['status'] = "success";
                    }
                }


            break;
            case "delete-class":
                $id = $_POST['id'];
                $class = $DB->q("SELECT * FROM classes WHERE id = ?;", [$id])->fetch(PDO::FETCH_ASSOC);
                if ($class['added_by'] == $user) {
                    if ($DB->q("DELETE FROM classes WHERE id = ? AND added_by = ?;", [$id, $user])) {
                        $result['status'] = "success";
                    }

                }
            break;
            case "add-student":
                $firstname = htmlentities(trim($_POST['firstname']));
                $lastname = htmlentities(trim($_POST['lastname']));
                $email = trim($_POST['email']);
                $roll = $_POST['roll'];
                $class = $_POST["class"];

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    if ($DB->q("INSERT INTO students (firstname, lastname, email, roll, class, added_by) VALUES (?, ?, ?, ?, ?, ?);", 
                    [$firstname, $lastname, $email, $roll, $class, $user])) {
                        $result['status'] = "success";
                    }
                    
                } else {
                    $result['msg'] = "Email invalid!";
                }
            break;
            case "delete-student":
                $id = $_POST['id'];
                $student = $DB->q("SELECT * FROM students WHERE id = ?;", [$id])->fetch(PDO::FETCH_ASSOC);
                if ($student['added_by'] == $user) {
                    if ($DB->q("DELETE FROM students WHERE id = ? AND added_by = ?;", [$id , $user])) {
                        $result['status'] = "success";
                    }
                    if ($DB->q("DELETE FROM student_test WHERE student_id = ? AND added_by = ?;", [$id , $user])) {
                        $result['status'] = "success";
                    }
                    if ($DB->q("DELETE FROM student_tests WHERE student = ? AND added_by = ?;", [$id , $user])) {
                        $result['status'] = "success";
                    }

                }
            break;
            case "delete-student-test":
                $id = $_POST['id'];
                $test = $_POST['test'];
                if ($DB->q("DELETE FROM student_tests WHERE student = ? AND test = ? AND added_by = ?; 
                DELETE FROM student_test WHERE student_id = ? AND test = ? AND added_by = ?;", [$id, $test , $user, $id, $test , $user])) {
                    $result['status'] = "success";
                }

                
            break;
            case "add-student-marks":
                $data = $_POST['data'];
                $id = $_POST['id'];
                $test = $_POST['test'];
                
                $query = "SELECT * FROM student_tests WHERE student = ? AND test = ? AND added_by = ?;";
                if (count($DB->q($query, [$id, $test, $user])->fetchAll(PDO::FETCH_ASSOC)) > 0) {
                    $result['msg'] = "Test already exists for this student!";
                } else {

                    if ($DB->q("INSERT INTO student_tests (student, test, added_by) VALUES(?,?,?);", [$id, $test, $user])) {
                        $lastId = $DB->lastId();
                        $query = "SELECT * FROM student_tests WHERE id = ?;";
                        $date  =$DB->q($query, [$lastId])->fetch(PDO::FETCH_ASSOC)['date'];
                    foreach ($data as $k => $q)
                        $DB->q("INSERT INTO student_test (test, student_id, question, chapter, mark, added_by, date) VALUES (?, ?, ?, ?, ?, ?, ?)", [$test, $id, $q[0], preg_split("#\s#", $q[0])[1],    (float) $q[1], $user, $date]);
                        $result['status'] = "success";
                    }
                }
                
            break;
            case "updateRef":
                $id = $_POST['id'];
                $value = htmlentities(trim($_POST['v']));
                if ($DB->q("UPDATE student_tests SET reflection = ? WHERE id = ?;", [$value, $id]))
                    $result['status'] = "success";
                
            break;
            case "add-test":
                $data = $_POST['data'];
                $name = htmlentities(trim($_POST['name']));
                $class = htmlentities(trim($_POST['class']));
                $result['data'] = $data;
                $query = "SELECT * FROM tests WHERE name = ? AND added_by = ?;";
                if (count($DB->q($query, [$name, $user])->fetchAll(PDO::FETCH_ASSOC)) > 0) {
                    $result['msg'] = "Test name exists!";
                } else {
                    if ($DB->q("INSERT INTO tests (name, added_by, class) VALUES (?, ?, ?);", [$name, $user, $class])) {
                        $query = "SELECT * FROM tests WHERE name = ? AND added_by = ?;";
                        $date  =$DB->q($query, [$name, $user])->fetch(PDO::FETCH_ASSOC)['date'];
                        foreach ($data as $k => $q)
                            $DB->q("INSERT INTO test_questions (test_name, question, max_mark, added_by, date) VALUES (?, ?, ?, ?, ?)", [$name, $q[0] . " " . $q[1], (float) $q[2], $user, $date]);
                        $result['status'] = "success";
                    }
                }
            break;
            case "delete-test":
                $id = $_POST['id'];
                $test = $DB->q("SELECT * FROM tests WHERE id = ?;", [$id])->fetch(PDO::FETCH_ASSOC);
                if ($test['added_by'] == $user) {
                    if ($DB->q("DELETE FROM tests WHERE id = ?; DELETE FROM test_questions WHERE test_name = ? AND added_by = ?;", [$id, $test['name'], $user])) {
                        $result['status'] = "success";
                    }

                }
            break;

            case "get-test":
                $name = $_POST['name'];
                $query = "SELECT * FROM test_questions WHERE test_name = ? AND added_by = ?;";
                $questions = $DB->q($query, [$name, $user])->fetchAll(PDO::FETCH_ASSOC);
                $result['status'] = "success";
                $result['questions'] = $questions;
            break;

            case "results":
                $test = trim($_POST['test']);
                $student = $_POST['student'];
                $date = $_POST['date'];
                $query = "SELECT * FROM test_questions LEFT JOIN student_test ON test_questions.question = student_test.question WHERE test_questions.test_name = ? AND student_test.added_by = ? AND (student_test.date <= '$date' + interval 1 second and student_test.date >= '$date' - interval 1 second)  ;";
                $results = $DB->q($query, [$test, $user])->fetchAll(PDO::FETCH_ASSOC);
                $total = 0;
                $max_total = 0; 
                foreach($results as $r) {
                    $total +=  (float) $r['mark'];
                    $max_total += (float) $r['max_mark'];
                }

                $result['status'] = "success";
                $result['results'] = $results; 
                $result['total'] =  $total; 
                $result['max_total'] =  $max_total;
            break;
            default:
        endswitch;

        $result['pageload_time'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        echo json_encode($result);
    } else {
        // Do nothing
    }
?>
