<?php
// Simple test to check assignments page
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing assignments.php dependencies...<br><br>";

try {
    require_once 'includes/config.php';
    echo "✅ Config loaded successfully<br>";
    
    // Test database connection
    $database = new Database();
    $pdo = $database->getConnection();
    echo "✅ Database connection successful<br>";
    
    // Test if assignments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'assignments'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Assignments table exists<br>";
    } else {
        echo "❌ Assignments table does not exist<br>";
        
        // Create the assignments table
        $createTable = "
        CREATE TABLE IF NOT EXISTS assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            subject VARCHAR(100),
            points INT DEFAULT 100,
            due_date DATETIME NOT NULL,
            teacher_id INT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($createTable);
        echo "✅ Created assignments table<br>";
    }
    
    // Test if student_assignments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'student_assignments'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Student_assignments table exists<br>";
    } else {
        echo "❌ Student_assignments table does not exist<br>";
        
        // Create the student_assignments table
        $createTable = "
        CREATE TABLE IF NOT EXISTS student_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            assignment_id INT NOT NULL,
            status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
            score DECIMAL(5,2),
            submitted_at TIMESTAMP NULL,
            graded_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES users(id),
            FOREIGN KEY (assignment_id) REFERENCES assignments(id)
        )";
        $pdo->exec($createTable);
        echo "✅ Created student_assignments table<br>";
    }
    
    // Check if there are any assignments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM assignments");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Total assignments in database: " . $count['count'] . "<br>";
    
    if ($count['count'] == 0) {
        echo "📝 Creating sample assignments...<br>";
        
        // Get a teacher user ID
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'guru' LIMIT 1");
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$teacher) {
            // Create a sample teacher
            $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Teacher Sample', 'teacher1', password_hash('password', PASSWORD_DEFAULT), 'guru']);
            $teacher_id = $pdo->lastInsertId();
        } else {
            $teacher_id = $teacher['id'];
        }
        
        // Create sample assignments
        $sampleAssignments = [
            [
                'title' => 'Essay on Mathematics',
                'description' => 'Write a 500-word essay about the importance of mathematics in daily life.',
                'subject' => 'Mathematics',
                'points' => 100,
                'due_date' => date('Y-m-d H:i:s', strtotime('+7 days'))
            ],
            [
                'title' => 'Science Experiment Report',
                'description' => 'Complete the physics experiment and submit your findings.',
                'subject' => 'Physics',
                'points' => 150,
                'due_date' => date('Y-m-d H:i:s', strtotime('+5 days'))
            ],
            [
                'title' => 'History Timeline',
                'description' => 'Create a timeline of major historical events in the 20th century.',
                'subject' => 'History',
                'points' => 80,
                'due_date' => date('Y-m-d H:i:s', strtotime('+10 days'))
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO assignments (title, description, subject, points, due_date, teacher_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleAssignments as $assignment) {
            $stmt->execute([
                $assignment['title'],
                $assignment['description'],
                $assignment['subject'],
                $assignment['points'],
                $assignment['due_date'],
                $teacher_id
            ]);
        }
        
        echo "✅ Created " . count($sampleAssignments) . " sample assignments<br>";
    }
    
    echo "<br>🎉 All checks passed! Try accessing assignments.php now.<br>";
    echo "<a href='assignments.php'>Go to Assignments Page</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString();
}
?>
