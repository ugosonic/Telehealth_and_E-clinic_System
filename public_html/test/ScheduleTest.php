<?php

use PHPUnit\Framework\TestCase;

class ScheduleTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        // Mock database connection
        $this->db = new mysqli('localhost', 'root', '', 'test_database');
        if ($this->db->connect_error) {
            die('Database connection failed: ' . $this->db->connect_error);
        }

        // Create necessary mock tables for testing
        $this->db->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            usergroup ENUM('Admin', 'Doctor', 'Patient') NOT NULL,
            password VARCHAR(255) NOT NULL
        )");

        $this->db->query("CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            appointment_date DATE NOT NULL,
            status ENUM('Upcoming', 'Past', 'Cancelled') NOT NULL,
            FOREIGN KEY (patient_id) REFERENCES users(id),
            FOREIGN KEY (doctor_id) REFERENCES users(id)
        )");

        // Insert mock data
        $this->db->query("INSERT INTO users (username, usergroup, password) VALUES 
            ('doctor1', 'Doctor', 'password123'),
            ('patient1', 'Patient', 'password123')
        ");

        $this->db->query("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES 
            (2, 1, CURDATE(), 'Upcoming'),
            (2, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Past'),
            (2, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Cancelled')
        ");
    }

    protected function tearDown(): void
    {
        // Clean up the database
        $this->db->query("DROP TABLE IF EXISTS appointments");
        $this->db->query("DROP TABLE IF EXISTS users");
        $this->db->close();
    }

    public function testGetUserDetails()
    {
        require_once '../functions.php';

        $username = 'doctor1';
        $userDetails = getUserDetails($this->db, $username);
        $this->assertNotEmpty($userDetails, "Failed to fetch user details.");
        $this->assertEquals($userDetails['usergroup'], 'Doctor');
    }

    public function testGetAppointments()
    {
        require_once '../functions.php';

        $sqlCondition = "doctor_id = 1";
        $today = date('Y-m-d');
        $appointments = getAppointments($this->db, $sqlCondition, $today, 0, 10);

        $this->assertArrayHasKey('upcoming', $appointments, "Failed to fetch upcoming appointments.");
        $this->assertArrayHasKey('past', $appointments, "Failed to fetch past appointments.");
        $this->assertArrayHasKey('cancelled', $appointments, "Failed to fetch cancelled appointments.");
    }

    public function testPagination()
    {
        $total_records = 3; // Mock total records
        $results_per_page = 10;
        $total_pages = ceil($total_records / $results_per_page);

        $this->assertEquals($total_pages, 1, "Pagination calculation is incorrect.");
    }

    public function testSqlCondition()
    {
        require_once '../functions.php';

        $usergroup = 'Doctor';
        $username = 'doctor1';
        $sqlCondition = getSqlCondition($usergroup, $username);

        $this->assertStringContainsString("doctor_id = 1", $sqlCondition, "SQL condition for Doctor is incorrect.");
    }

    public function testAuthenticationRedirect()
    {
        $_SESSION['username'] = null; // Simulate an unauthenticated user

        ob_start();
        require_once '../schedule.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Location: ./login/login.php', $output, "Authentication redirect is not working.");
    }
}
?>
