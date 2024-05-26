<?php
header("Content-Type: application/json");

$host = 'localhost';
$db = 'hr';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT username, pass, email FROM accounts");
    $accounts = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT name, department, hiredate, salary FROM employee");
    $employees = $stmt->fetchAll();

    $combinedData = [
        'accounts' => $accounts,
        'employees' => $employees
    ];

    echo json_encode($combinedData);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'];
    $pass = $input['pass'];
    $email = $input['email'];
    $name = $input['name'];
    $department = $input['department'];
    $hiredate = $input['hiredate'];
    $salary = $input['salary'];

    $pdo->beginTransaction();

    try {
        // Insert into accounts table
        $stmt = $pdo->prepare("INSERT INTO accounts (username, pass, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $pass, $email]);
        $accountId = $pdo->lastInsertId();

        // Insert into employee table
        $stmt = $pdo->prepare("INSERT INTO employee (name, department, hiredate, salary, accountid) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $department, $hiredate, $salary, $accountId]);

        $pdo->commit();
        echo json_encode(['message' => 'User and employee added successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Failed to add user and employee: ' . $e->getMessage()]);
    }
}
?>
