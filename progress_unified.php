<?php
include 'db.php';

$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$saved = false;
$error = '';

// Catalog of all assessments and items (must match Parent Portal labels)
$catalog = [
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
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $labels = isset($_POST['label']) && is_array($_POST['label']) ? $_POST['label'] : [];
    $e1s = isset($_POST['e1']) && is_array($_POST['e1']) ? $_POST['e1'] : [];
    $e2s = isset($_POST['e2']) && is_array($_POST['e2']) ? $_POST['e2'] : [];
    $e3s = isset($_POST['e3']) && is_array($_POST['e3']) ? $_POST['e3'] : [];
    if ($studentId <= 0) {
        $error = 'Missing student.';
    } else {
        // Ensure table exists and student_id column
        $conn->query("CREATE TABLE IF NOT EXISTS grossmotor_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL DEFAULT 0,
            payload TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Check if student_id column exists before adding
        $check_col = $conn->query("SHOW COLUMNS FROM grossmotor_submissions LIKE 'student_id'");
        if ($check_col && $check_col->num_rows == 0) {
            $conn->query("ALTER TABLE grossmotor_submissions ADD COLUMN student_id INT NOT NULL DEFAULT 0");
        }
        
        // Check if index exists before creating
        $check_idx = $conn->query("SHOW INDEX FROM grossmotor_submissions WHERE Key_name = 'idx_gm_student_id'");
        if ($check_idx && $check_idx->num_rows == 0) {
            $conn->query("CREATE INDEX idx_gm_student_id ON grossmotor_submissions(student_id)");
        }

        // Build payload as array of {item, eval1, eval2, eval3}
        $payloadArr = [];
        $n = count($labels);
        for ($i = 0; $i < $n; $i++) {
            $label = isset($labels[$i]) ? trim($labels[$i]) : '';
            if ($label === '') continue;
            $e1 = isset($e1s[$i]) && $e1s[$i] !== '' ? (int)$e1s[$i] : null;
            $e2 = isset($e2s[$i]) && $e2s[$i] !== '' ? (int)$e2s[$i] : null;
            $e3 = isset($e3s[$i]) && $e3s[$i] !== '' ? (int)$e3s[$i] : null;
            $payloadArr[] = [ 'item' => $label, 'eval1' => $e1, 'eval2' => $e2, 'eval3' => $e3 ];
        }
        $payload = json_encode($payloadArr);
        $stmt = $conn->prepare("INSERT INTO grossmotor_submissions (student_id, payload) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param('is', $studentId, $payload);
            if ($stmt->execute()) { $saved = true; }
            $stmt->close();
        } else {
            $error = 'Save failed.';
        }
    }
}

// Load latest saved values for prefill
$prefill = [];
if ($studentId > 0) {
    $pf = $conn->prepare("SELECT payload FROM grossmotor_submissions WHERE student_id=? ORDER BY created_at DESC LIMIT 1");
    if ($pf) {
        $pf->bind_param('i', $studentId);
        if ($pf->execute()) {
            $res = $pf->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $arr = json_decode($row['payload'] ?? '[]', true);
                if (is_array($arr)) {
                    foreach ($arr as $it) {
                        $label = isset($it['item']) ? (string)$it['item'] : '';
                        if ($label !== '') {
                            $prefill[$label] = [
                                'e1' => isset($it['eval1']) ? $it['eval1'] : '',
                                'e2' => isset($it['eval2']) ? $it['eval2'] : '',
                                'e3' => isset($it['eval3']) ? $it['eval3'] : ''
                            ];
                        }
                    }
                }
            }
        }
        $pf->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unified Progress Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#F7F9FB; }
        .wrap { max-width:1100px; margin: 10px auto; padding: 12px; }
        .card { background:#fff; border-radius:12px; box-shadow:0 6px 24px rgba(0,0,0,.08); margin-bottom:14px; overflow:hidden; }
        .head { background:#F8F9FA; padding:12px 14px; font-weight:800; color:#1B5E20; display:flex; gap:8px; align-items:center; }
        .body { padding:12px 14px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:8px 10px; border-bottom:1px solid #eee; text-align:left; }
        th { color:#1B5E20; }
        input[type="number"] { width: 80px; padding:8px 10px; border:2px solid #E9ECEF; border-radius:8px; }
        .bar { display:flex; gap:10px; align-items:center; justify-content:space-between; margin-bottom:10px; }
        .btn { background:linear-gradient(135deg,#28A745,#20C997); color:#fff; border:0; padding:10px 14px; border-radius:10px; font-weight:800; cursor:pointer; }
        .alert { padding:10px 12px; border-radius:10px; font-weight:700; margin-bottom:10px; }
        .success { background:#D1E7DD; color:#0F5132; border:1px solid #BADBCC; }
        .error { background:#F8D7DA; color:#842029; border:1px solid #F5C2C7; }
        select { padding:8px 10px; border:2px solid #E9ECEF; border-radius:8px; }
    </style>
</head>
<body>
    <div class="wrap">
        <?php if ($saved): ?><div class="alert success">Saved successfully.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" action="">
            <div class="bar">
                <div style="display:flex; gap:8px; align-items:center;">
                    <label for="student_id" style="font-weight:800; color:#1B5E20;">Student:</label>
                    <select id="student_id" name="student_id" required>
                        <option value="">-- choose --</option>
                        <?php 
                        $st = $conn->query("SELECT id, first_name, last_name FROM students WHERE archived=0 ORDER BY last_name, first_name");
                        if ($st) { while($s = $st->fetch_assoc()) { $sid = intval($s['id']); $sel = ($sid===$studentId)?' selected':''; echo '<option value="'.$sid.'"'.$sel.'>'.htmlspecialchars($s['last_name'].', '.$s['first_name'])."</option>"; } }
                        ?>
                    </select>
                </div>
                <button class="btn" type="submit"><i class="fas fa-save"></i> Save All</button>
            </div>

            <?php foreach ($catalog as $section => $items): ?>
            <div class="card">
                <div class="head"><?php echo htmlspecialchars($section); ?></div>
                <div class="body">
                    <table>
                        <tr><th style="width:60%">Assessment Items</th><th>1st Evaluation</th><th>2nd Evaluation</th><th>3rd Evaluation</th></tr>
                        <?php foreach ($items as $label): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($label); ?></td>
                            <td><input type="number" name="e1[]" min="0" max="10" step="1" value="<?php echo isset($prefill[$label]['e1']) ? htmlspecialchars($prefill[$label]['e1']) : ''; ?>"></td>
                            <td><input type="number" name="e2[]" min="0" max="10" step="1" value="<?php echo isset($prefill[$label]['e2']) ? htmlspecialchars($prefill[$label]['e2']) : ''; ?>"></td>
                            <td><input type="number" name="e3[]" min="0" max="10" step="1" value="<?php echo isset($prefill[$label]['e3']) ? htmlspecialchars($prefill[$label]['e3']) : ''; ?>"></td>
                            <input type="hidden" name="label[]" value="<?php echo htmlspecialchars($label); ?>">
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </form>
    </div>
</body>
</html>


