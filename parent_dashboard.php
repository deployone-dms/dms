<?php
include 'db.php';
session_start();
if (!isset($_SESSION['parent_id'])) {
    header('Location: parent_login.php');
    exit;
}
$parent_id = intval($_SESSION['parent_id']);
$parent_name = $_SESSION['parent_name'] ?? 'Parent';

// Handle link child request (no student names exposed in UI)
$link_success = '';
$link_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_student_id'])) {
    $sid = intval($_POST['link_student_id']);
    $verify_last = trim($_POST['verify_last'] ?? '');
    $relation = trim($_POST['relation'] ?? 'Parent');
    if ($sid <= 0 || $verify_last === '') {
        $link_error = 'Please provide Student ID and child\'s last name.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM students WHERE id=? AND archived=0 AND last_name = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('is', $sid, $verify_last);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows === 1) {
                try {
                    $ins = $conn->prepare("INSERT IGNORE INTO parent_students (parent_id, student_id, relation) VALUES (?,?,?)");
                    if ($ins) {
                        $ins->bind_param('iis', $parent_id, $sid, $relation);
                        if ($ins->execute()) {
                            $link_success = 'Child linked to your account.';
                        } else {
                            $link_error = 'Could not link at this time.';
                        }
                        $ins->close();
                    } else {
                        $link_error = 'Could not prepare link action.';
                    }
                } catch (Exception $e) {
                    $link_error = 'Database table not ready. Please contact administrator.';
                }
            } else {
                $link_error = 'No active student matches that ID and last name.';
            }
            $stmt->close();
        } else {
            $link_error = 'Verification failed.';
        }
    }
}

// Load linked students
$students = [];
try {
    $q = $conn->prepare("SELECT s.id, s.first_name, s.last_name, s.picture FROM parent_students ps JOIN students s ON s.id = ps.student_id WHERE ps.parent_id = ? AND s.archived = 0 ORDER BY s.last_name, s.first_name");
    if ($q) { 
        $q->bind_param('i', $parent_id); 
        $q->execute(); 
        $res = $q->get_result(); 
        while($row=$res->fetch_assoc()){ 
            $students[]=$row; 
        } 
        $q->close(); 
    }
} catch (Exception $e) {
    // Table doesn't exist yet, students array will be empty
    $students = [];
}

// Optionally load latest progress summary per student (example: grossmotor_submissions)
function load_latest_grossmotor($conn, $student_id) {
    try {
        // First check if the table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'grossmotor_submissions'");
        if (!$tableCheck || $tableCheck->num_rows == 0) {
            return ['created_at' => null, 'totals' => ['t1'=>0,'t2'=>0,'t3'=>0]];
        }
        
        // Check if student_id column exists
        $columnCheck = $conn->query("SHOW COLUMNS FROM grossmotor_submissions LIKE 'student_id'");
        if (!$columnCheck || $columnCheck->num_rows == 0) {
            // If no student_id column, try to get any record (fallback)
            $gm = $conn->prepare("SELECT payload, created_at FROM grossmotor_submissions ORDER BY created_at DESC LIMIT 1");
            if (!$gm) return ['created_at' => null, 'totals' => ['t1'=>0,'t2'=>0,'t3'=>0]];
            $gm->execute();
            $res = $gm->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $gm->close();
        } else {
            // Normal query with student_id
            $gm = $conn->prepare("SELECT payload, created_at FROM grossmotor_submissions WHERE student_id=? ORDER BY created_at DESC LIMIT 1");
            if (!$gm) return ['created_at' => null, 'totals' => ['t1'=>0,'t2'=>0,'t3'=>0]];
            $gm->bind_param('i', $student_id);
            $gm->execute();
            $res = $gm->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $gm->close();
        }
        
        if (!$row) return ['created_at' => null, 'totals' => ['t1'=>0,'t2'=>0,'t3'=>0]];
        
        $totals = ['t1'=>0,'t2'=>0,'t3'=>0];
        $data = json_decode($row['payload'] ?? '[]', true);
        if (is_array($data)) {
            foreach($data as $item){
                $totals['t1'] += isset($item['eval1']) && is_numeric($item['eval1']) ? (int)$item['eval1'] : 0;
                $totals['t2'] += isset($item['eval2']) && is_numeric($item['eval2']) ? (int)$item['eval2'] : 0;
                $totals['t3'] += isset($item['eval3']) && is_numeric($item['eval3']) ? (int)$item['eval3'] : 0;
            }
        }
        return [ 'created_at' => $row['created_at'], 'totals' => $totals ];
    } catch (Exception $e) {
        // Log error but don't break the page
        error_log("Error in load_latest_grossmotor: " . $e->getMessage());
        return ['created_at' => null, 'totals' => ['t1'=>0,'t2'=>0,'t3'=>0]];
    }
}

function load_grossmotor_details($conn, $student_id) {
    try {
        // First check if the table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'grossmotor_submissions'");
        if (!$tableCheck || $tableCheck->num_rows == 0) {
            return ['created_at' => null, 'items' => []];
        }
        
        // Check if student_id column exists
        $columnCheck = $conn->query("SHOW COLUMNS FROM grossmotor_submissions LIKE 'student_id'");
        if (!$columnCheck || $columnCheck->num_rows == 0) {
            // If no student_id column, try to get any record (fallback)
            $gm = $conn->prepare("SELECT payload, created_at FROM grossmotor_submissions ORDER BY created_at DESC LIMIT 1");
            if (!$gm) return ['created_at' => null, 'items' => []];
            $gm->execute();
            $res = $gm->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $gm->close();
        } else {
            // Normal query with student_id
            $gm = $conn->prepare("SELECT payload, created_at FROM grossmotor_submissions WHERE student_id=? ORDER BY created_at DESC LIMIT 1");
            if (!$gm) return ['created_at' => null, 'items' => []];
            $gm->bind_param('i', $student_id);
            $gm->execute();
            $res = $gm->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $gm->close();
        }
        
        if (!$row) return ['created_at' => null, 'items' => []];
        
        $items = json_decode($row['payload'] ?? '[]', true);
        if (!is_array($items)) $items = [];
        return [ 'created_at' => $row['created_at'], 'items' => $items ];
    } catch (Exception $e) {
        // Log error but don't break the page
        error_log("Error in load_grossmotor_details: " . $e->getMessage());
        return ['created_at' => null, 'items' => []];
    }
}

function load_recent_attendance($conn, $student_id, $days = 30) {
    $att = $conn->prepare("SELECT attendance_date, status FROM attendance_records WHERE student_id=? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY attendance_date DESC");
    if (!$att) return [ 'totals' => ['present'=>0,'absent'=>0], 'rows' => [] ];
    $att->bind_param('ii', $student_id, $days);
    $att->execute();
    $res = $att->get_result();
    $rows = [];
    $tot = ['present'=>0,'absent'=>0];
    while($r = $res->fetch_assoc()) {
        $rows[] = $r;
        $st = strtolower($r['status']);
        if (isset($tot[$st])) $tot[$st]++;
    }
    $att->close();
    return [ 'totals' => $tot, 'rows' => $rows ];
}

// Assessment catalog (report card) - authoritative item lists per category
$ASSESSMENT_CATALOG = [
    'Progress Assessment 1 - Gross Motor Emotional Domain' => [
        'Climbs on chair or other elevated piece of furniture like a bed without help',
        'Walks backwards',
        'Runs without tripping or falling',
        'Walks down stairs, two feet on each step, with one hand held',
        'Walks upstairs holding onto a handrail, two feet on each step',
        'Walks upstairs with alternate feet without holding onto a handrail',
        'Walks downstairs with alternate feet without holding onto a handrail',
        'Moves body part as directed',
        'Jumps up',
        'Throws ball overhead with direction',
        'Hops one to three steps on preferred foot',
        'Jumps and turns',
        'Dances patterns/joins group movement activities'
    ],
    'Progress Assessment 2 - Fine Motor Skills' => [
        'Picks up small objects with thumb and forefinger (pincer grasp)',
        'Uses crayons or markers to make marks on paper',
        'Stacks 2-3 blocks',
        'Turns pages of a book one at a time',
        'Uses spoon to feed self with minimal spilling',
        'Puts small objects into containers',
        'Uses both hands together (bilateral coordination)',
        'Draws simple shapes (circle, line)',
        'Uses scissors to cut paper',
        'Strings beads or similar objects',
        'Uses zippers, buttons, or snaps',
        'Copies simple patterns or designs',
        'Uses tools appropriately (hammer, screwdriver)'
    ],
    'Progress Assessment 3 - Receptive Language Domain' => [
        'Points to a family member when asked to do so',
        'Points to five body parts on himself when asked to do so',
        'Points to five named pictured objects when asked to do so',
        'Follows one-step instructions that include simple prepositions (e.g., in, on, under, etc.)',
        'Follows two-step instructions that include simple prepositions'
    ],
    'Feeding Sub-Domain Assessment' => [
        'Feeds self with finger food (e.g. biscuits, bread) using fingers',
        'Feeds self using fingers to eat rice/viands with spillage',
        'Feeds self using spoon with spillage',
        'Feeds self using fingers without spillage',
        'Feeds self using spoon without spillage',
        'Eats without need for spoonfeeding during any meal',
        'Helps hold cup for drinking',
        'Drinks from cup with spillage',
        'Drinks from cup unassisted',
        'Gets drink for self unassisted',
        'Pours from pitcher without spillage',
        'Prepares own food/snack',
        'Prepares meals for younger siblings/family members when no adult is around'
    ],
    'Progress Assessment 4.1 - Expressive Language Domain' => [
        'Says "mama" or "dada" or equivalent',
        'Says at least two words',
        'Says at least five words',
        'Says at least ten words',
        'Combines two words to make a phrase'
    ],
    'Progress Assessment 4.2 - Expressive Language Domain' => [
        'Uses "please" and "thank you" appropriately',
        'Asks questions using "what", "where", "who"',
        'Uses pronouns correctly (I, you, he, she, it)',
        'Uses past tense correctly',
        'Tells a simple story or describes an event'
    ],
    'Progress Assessment 4.3 - Expressive Language Domain' => [
        'Uses "yes" and "no" appropriately',
        'Uses "more" and "all done" appropriately',
        'Uses "help" and "stop" appropriately',
        'Uses "up" and "down" appropriately',
        'Uses "big" and "little" appropriately'
    ],
    'Progress Assessment 5 - Cognitive Domain' => [
        'Recognizes and names primary colors (red, blue, yellow, green)',
        'Counts from 1 to 10',
        'Recognizes basic shapes (circle, square, triangle, rectangle)',
        'Sorts objects by size (big/small) and color',
        'Completes simple puzzles (3-5 pieces)'
    ],
    'Progress Assessment 6 - Social-Emotional Domain' => [
        'Shows interest in other children',
        'Plays alongside other children (parallel play)',
        'Shares toys and materials with others',
        'Takes turns in simple games and activities',
        'Shows empathy and comfort to others when they are upset'
    ],
    'Progress Assessment 7 - Self-Help Skills' => [
        'Feeds self with spoon and fork',
        'Drinks from a cup without spilling',
        'Washes hands independently',
        'Puts on and takes off simple clothing items',
        'Uses the toilet independently'
    ]
];

// Normalize labels (strip numbering, punctuation, spaces; lowercase)
function normalize_label(string $text): string {
    $t = trim($text);
    // Remove leading numbering like "1.", "1 -", "1)"
    $t = preg_replace('/^\s*\d+\s*[\.)\-]*\s*/', '', $t);
    // Normalize quotes and dashes
    $t = str_replace(["“","”","’","–","—"], ['"','"','\'','-','-'], $t);
    // Lowercase
    $t = strtolower($t);
    // Remove punctuation except spaces and alphanumerics
    $t = preg_replace('/[^a-z0-9\s]/', '', $t);
    // Collapse whitespace
    $t = preg_replace('/\s+/', ' ', $t);
    return trim($t);
}

// Load all recent assessment submissions and map values into the catalog
function build_report_card(mysqli $conn, int $student_id, array $catalog): array {
    // Build normalized catalog map
    $catalogMap = [];
    foreach ($catalog as $cat => $items) {
        foreach ($items as $label) {
            $catalogMap[normalize_label($label)] = [$cat, $label];
        }
    }
    // Initialize report with blanks
    $report = [];
    foreach ($catalog as $cat => $items) {
        $report[$cat] = [];
        foreach ($items as $label) {
            $report[$cat][$label] = ['eval1' => '', 'eval2' => '', 'eval3' => '', 'filled' => false];
        }
    }
    // Pull recent submissions for this student; if none (legacy), fall back
    $sub = $conn->prepare("SELECT payload, created_at FROM grossmotor_submissions WHERE student_id=? ORDER BY created_at DESC LIMIT 50");
    if (!$sub) return $report;
    $sub->bind_param('i', $student_id);
    $sub->execute();
    $res = $sub->get_result();
    if (!$res || $res->num_rows === 0) {
        $fallback = $conn->query("SELECT payload, created_at FROM grossmotor_submissions ORDER BY created_at DESC LIMIT 10");
        if ($fallback) $res = $fallback;
    }
    while($row = $res->fetch_assoc()){
        $items = json_decode($row['payload'] ?? '[]', true);
        if (!is_array($items)) continue;
        foreach ($items as $it) {
            $raw = $it['item'] ?? ($it['title'] ?? '');
            if (!$raw) continue;
            $norm = normalize_label($raw);
            if (isset($catalogMap[$norm])) {
                [$cat, $label] = $catalogMap[$norm];
                if (isset($it['eval1'])) $report[$cat][$label]['eval1'] = (string)$it['eval1'];
                if (isset($it['eval2'])) $report[$cat][$label]['eval2'] = (string)$it['eval2'];
                if (isset($it['eval3'])) $report[$cat][$label]['eval3'] = (string)$it['eval3'];
                $report[$cat][$label]['filled'] = true;
            }
        }
    }
    return $report;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:linear-gradient(135deg,#F4EDE4,#E8F5E8); margin:0; }
        .topbar { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; background:#1B5E20; color:#fff; }
        .container { max-width:1100px; margin: 20px auto; padding: 0 16px; }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:16px; }
        .card { background:#fff; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,.08); overflow:hidden; }
        .card .head { padding:14px 16px; font-weight:700; color:#1B5E20; border-bottom:1px solid #eee; display:flex; align-items:center; gap:8px; }
        .card .body { padding:16px; }
        .student { display:flex; align-items:center; gap:12px; }
        .avatar { width:56px; height:56px; border-radius:50%; object-fit:cover; border:3px solid #1B5E20; }
        .btn { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#4DA3FF,#1E88E5); color:#fff; border:0; padding:10px 14px; border-radius:10px; font-weight:700; cursor:pointer; text-decoration:none; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding:8px 10px; border-bottom:1px solid #eee; text-align:left; }
        th { color:#1B5E20; }
    </style>
</head>
<body>
        <div class="topbar">
        <div><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($parent_name); ?></div>
        <div style="display:flex; gap:10px; align-items:center;">
            <a class="btn" style="background:linear-gradient(135deg,#6C757D,#495057)" href="parent_logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>
    <div class="container">
        <?php if ($link_success): ?><div class="card" style="margin-bottom:16px;"><div class="body" style="color:#0F5132; background:#D1E7DD; border:1px solid #BADBCC; border-radius:12px;">✔ <?php echo htmlspecialchars($link_success); ?></div></div><?php endif; ?>
        <?php if ($link_error): ?><div class="card" style="margin-bottom:16px;"><div class="body" style="color:#842029; background:#F8D7DA; border:1px solid #F5C2C7; border-radius:12px;">✖ <?php echo htmlspecialchars($link_error); ?></div></div><?php endif; ?>

        <div class="grid">
            <div class="card">
                <div class="head"><i class="fas fa-link"></i> Link a Child</div>
                <div class="body">
                    <form method="post" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; align-items:end;">
                        <div>
                            <label for="link_student_id" style="font-weight:700; color:#1B5E20;">Student ID</label>
                            <input id="link_student_id" name="link_student_id" type="number" min="1" placeholder="e.g., 123" style="width:100%; padding:10px; border:2px solid #E9ECEF; border-radius:10px;" required>
                        </div>
                        <div>
                            <label for="verify_last" style="font-weight:700; color:#1B5E20;">Child's Last Name</label>
                            <input id="verify_last" name="verify_last" type="text" placeholder="Verify last name" style="width:100%; padding:10px; border:2px solid #E9ECEF; border-radius:10px;" required>
                        </div>
                        <div>
                            <label for="relation" style="font-weight:700; color:#1B5E20;">Relation</label>
                            <input id="relation" name="relation" type="text" placeholder="e.g., Mother" style="width:100%; padding:10px; border:2px solid #E9ECEF; border-radius:10px;">
                        </div>
                        <div style="grid-column: 1 / -1; display:flex; justify-content:flex-end;">
                            <button class="btn" type="submit"><i class="fas fa-link"></i> Link Child</button>
                        </div>
                    </form>
                    <div style="font-size:12px; color:#6c757d; margin-top:8px;">Ask the school for your child's Student ID. We verify using last name to protect privacy.</div>
                </div>
            </div>
            <?php if (count($students) === 0): ?>
            <div class="card">
                <div class="head"><i class="fas fa-user-graduate"></i> Linked Students</div>
                <div class="body">
                    No linked students yet. Please contact the school to link your account to your child's record.
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($students as $s): $sid = intval($s['id']); $summary = load_latest_grossmotor($conn, $sid); $details = load_grossmotor_details($conn, $sid); $attendance = load_recent_attendance($conn, $sid, 30); ?>
            <div class="card">
                <div class="head"><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($s['last_name'].', '.$s['first_name']); ?></div>
                <div class="body">
                    <div class="student">
                        <img class="avatar" src="<?php echo htmlspecialchars($s['picture'] ?: 'yakaplogopo.jpg'); ?>" onerror="this.src='yakaplogopo.jpg'" />
                        <div>
                            <div style="font-weight:700; color:#1B5E20;">Latest Gross Motor</div>
                            <?php if ($summary): ?>
                            <div style="color:#555; font-size:14px;">Date: <?php echo date('M d, Y', strtotime($summary['created_at'])); ?></div>
                            <table style="margin-top:8px;">
                                <tr><th>Eval1</th><th>Eval2</th><th>Eval3</th></tr>
                                <tr><td><?php echo intval($summary['totals']['t1']); ?></td><td><?php echo intval($summary['totals']['t2']); ?></td><td><?php echo intval($summary['totals']['t3']); ?></td></tr>
                            </table>
                            <?php else: ?>
                            <div style="color:#777;">No assessments yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Report Card: All Progress Assessments -->
                    <div style="margin-top:14px;">
                        <div style="font-weight:700; color:#1B5E20; margin-bottom:10px; display:flex; align-items:center; gap:8px;"><i class="fas fa-clipboard-list"></i> Progress Assessments (Report Card)</div>
                        <?php $reportCard = build_report_card($conn, $sid, $ASSESSMENT_CATALOG); ?>
                        <?php foreach ($reportCard as $category => $items): ?>
                        <div style="border:1px solid #E9ECEF; border-radius:12px; margin-bottom:12px; overflow:hidden;">
                            <div style="background:#F8F9FA; padding:10px 12px; font-weight:700; color:#1B5E20;">
                                <?php echo htmlspecialchars($category); ?>
                            </div>
                            <div style="padding:10px 12px;">
                                <table>
                                    <tr><th style="width:60%">Assessment</th><th>Eval1</th><th>Eval2</th><th>Eval3</th></tr>
                                    <?php foreach ($items as $label => $vals): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($label); ?></td>
                                        <td><?php echo htmlspecialchars($vals['eval1']); ?></td>
                                        <td><?php echo htmlspecialchars($vals['eval2']); ?></td>
                                        <td><?php echo htmlspecialchars($vals['eval3']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top:16px;">
                        <div style="font-weight:700; color:#1B5E20; margin-bottom:10px; display:flex; align-items:center; gap:8px;"><i class="fas fa-calendar-check"></i> Attendance (Last 30 days)</div>
                        <?php if ($attendance && (count($attendance['rows']) > 0)): ?>
                        <div style="display:flex; gap:14px; margin-bottom:10px;">
                            <span style="background:#E6FFED; color:#1E7E34; border:1px solid #C3E6CB; padding:6px 10px; border-radius:999px; font-weight:600;">Present: <?php echo intval($attendance['totals']['present']); ?></span>
                        </div>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:10px;">
                            <?php foreach ($attendance['rows'] as $ar): $isPresent = strtolower($ar['status']) === 'present'; ?>
                            <div style="border:1px solid #E9ECEF; border-radius:12px; padding:12px; background:#FAFAFA; display:flex; align-items:center; justify-content:space-between; gap:8px;">
                                <div style="font-weight:600; color:#1B5E20;">&nbsp;<?php echo date('M d, Y', strtotime($ar['attendance_date'])); ?></div>
                                <span style="background:<?php echo $isPresent?'#E6FFED':'#FFF0F0'; ?>; color:<?php echo $isPresent?'#1E7E34':'#B02A37'; ?>; border:1px solid <?php echo $isPresent?'#C3E6CB':'#F5C2C7'; ?>; padding:6px 10px; border-radius:999px; font-weight:600; width:100px; text-align:center;">
                                    <?php echo htmlspecialchars(ucfirst($ar['status'])); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div style="color:#777;">No recent attendance records.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


