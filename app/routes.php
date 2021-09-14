<?php
switch(@$_GET['p']) : 
        case "Test":
            $id = @$_GET['id'];
            $test = $DB->q("SELECT * FROM tests WHERE id = ?;", [$id])->fetch(PDO::FETCH_ASSOC);
            $load['test'] = $test;
            $load['questions'] = $DB->q("SELECT * FROM test_questions WHERE test_name = ? AND added_by = ?;", [$test['name'], $_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);


		   $content = "Test";
        break;
        case "Tests":
            $extra = "";
            if (@isset($_GET['q']) && @strlen(trim($_GET['q'])) > 0) {
                $q = addslashes(htmlentities(trim(@$_GET['q']))); 
                $extra = " AND tests.name LIKE '%$q%' OR classes.code LIKE '%$q%' OR classes.subject LIKE '%$q%'";
             }
            $load['tests'] = $DB->q("SELECT * FROM tests LEFT JOIN ( SELECT id as cid, code, size, subject, chaps, added_by as cadded_by, batch, date as cdate FROM classes) as classes ON tests.class = classes.cid WHERE tests.added_by = ? $extra;", [@$_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
            $load['classes'] = $DB->q("SELECT * FROM classes WHERE added_by = ?;", [@$_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
            $content = "Tests";
        break;
        case "Classes":
            $load['classes'] = $DB->q("SELECT * FROM classes WHERE added_by = ? ORDER BY code ASC;", [@$_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
            $content = "Classes";
        break;
                case "Class":
            $id = @$_GET['id'];
            $extra = "";
            $order = "ORDER BY firstname ASC";
            $sortables = ["firstname", "lastname", "roll", "email"];
            if (@isset($_GET['sort']) && @strlen(trim($_GET['sort'])) > 0) {
                $s = strtolower(trim($_GET['sort']));
                if (in_array($s, $sortables)) {
                    $o = @$_GET['order'];
                    $o = ($o=="DESC")?"DESC":"ASC";
                    $order = "ORDER BY $s $o";
                }
            }
            
            if (@isset($_GET['q']) && @strlen(trim($_GET['q'])) > 0) {
                $q = addslashes(htmlentities(trim(@$_GET['q']))); 
                $extra = " AND firstname LIKE '%$q%' OR lastname LIKE '%$q%' OR email LIKE '%$q%' ";
             }
            $load['class'] = $DB->q("SELECT * FROM classes WHERE id = ?;", [$id])->fetch(PDO::FETCH_ASSOC);
            $load['students'] = $DB->q("SELECT * FROM students WHERE class = ? AND added_by = ? $extra $order;", [$id, $_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
            
            
            
            $tests = $DB->q("SELECT * FROM tests WHERE class = ? AND added_by = ?;", [$id, $_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
            $Tests = [];
            foreach ($tests as $test) $Tests[] = "'" . $test["name"] . "'";
            $Tests = implode(",", $Tests);
            $results = $DB->q("SELECT SUM(mark) as total, test FROM student_test WHERE test IN ($Tests) AND added_by = ? GROUP BY test", [ $_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);

            $total = [];
            $labels = [];
            foreach ($results as $r) {
                $labels[] = $r['test'] ;
                $total[] = $r['total'];
                
            }
 
            @$load['labels'] = $labels;
            @$load['totals'] = $total; 

            
            $bests = $DB->q("SELECT SUM(student_test.mark) as total, SUM(test_questions.max_mark) as max_total, chapter FROM student_test LEFT JOIN test_questions ON test_questions.test_name = student_test.test AND student_test.question = test_questions.question WHERE student_test.test IN ($Tests) AND student_test.added_by = ? GROUP BY student_test.chapter;
            ;", [ $_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
            
            $total_lost = 0;

            $totals = [];
            $ptotals = [];
            foreach ($bests as $b) {
                $plabels[] = $b['chapter'];
                $totals[] = $b['max_total'] - $b['total'];
                $total_lost += $b['max_total'] - $b['total'];
            }
            foreach ($totals as $t) {
                $ptotals[] = 360*$t/$total_lost;
            } 
            @$max = array_keys($ptotals, max($ptotals))[0];
            @$load['focus'] = (isset($max))?"Chapter to be focused on by this class is chapter number " . $plabels[$max]:"";
            @$load['plabels'] = $plabels;  
            @$load['ptotals'] = $ptotals;  
            
            
            
            
            
            
            $content = "Class";
        break;
        case "Student":
            $id = @$_GET['id'];
            $load['tests'] = $DB->q("SELECT * FROM tests WHERE added_by = ? ;", [@$_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
            $load['student_tests'] = $DB->q("SELECT * FROM student_tests WHERE student = ? AND added_by = ? ORDER BY test ASC;", [$id, $_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
            $load['student'] = $DB->q("SELECT * FROM students WHERE id = ? AND added_by = ?;", [$id, $_SESSION['loggedas']])->fetch(PDO::FETCH_ASSOC);

            $bests = $DB->q("SELECT SUM(student_test.mark) as total, SUM(test_questions.max_mark) as max_total, chapter FROM student_test LEFT JOIN test_questions ON test_questions.test_name = student_test.test AND student_test.question = test_questions.question WHERE student_test.student_id = ? AND student_test.added_by = ? GROUP BY student_test.chapter;
            ;", [$id, $_SESSION['loggedas']])->fetchAll(PDO::FETCH_ASSOC);
         
            $totals = [];
            $ptotals = [];
           foreach ($bests as $b) {
                $plabels[] = $b['chapter'];
                $totals[] = $b['max_total'] - $b['total'];
                $total_lost += $b['max_total'] - $b['total'];
            }
            foreach ($totals as $t) {
                $ptotals[] = 360*$t/$total_lost;
            } 
            @$max = array_keys($ptotals, max($ptotals))[0];
            @$load['focus'] = (isset($max))?"Chapter to be focused on by " . $load['student']['firstname']." is chapter number " . $plabels[$max]:"";

            @$load['plabels'] = $plabels;  
            @$load['ptotals'] = $ptotals;  
            $content = "Student";
        break;
        default:
        $content = 'Classes'; 
    endswitch;

?>