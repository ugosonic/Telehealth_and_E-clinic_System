<?php
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        // Mock database connection
        $this->db = new mysqli('localhost', 'root', '', 'administrator');
        if ($this->db->connect_error) {
            die('Database connection failed: ' . $this->db->connect_error);
        }

        // Create mock users table
        $this->db->query("DROP TABLE IF EXISTS users");
        $this->db->query("
            CREATE TABLE users (
                registration_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                usergroup ENUM('Admin', 'IT', 'Doctor', 'Nurse', 'Lab Scientist', 'Pharmacist', 'User') NOT NULL
            )
        ");

        // Insert mock data
        $this->db->query("INSERT INTO users (username, usergroup) VALUES ('admin_user', 'Admin')");
        $this->db->query("INSERT INTO users (username, usergroup) VALUES ('it_user', 'IT')");
        $this->db->query("INSERT INTO users (username, usergroup) VALUES ('doctor_user', 'Doctor')");
        $this->db->query("INSERT INTO users (username, usergroup) VALUES ('nurse_user', 'Nurse')");
    }

    protected function tearDown(): void
    {
        // Drop mock users table and close the connection
        $this->db->query("DROP TABLE IF EXISTS users");
        $this->db->close();
    }

    public function testDatabaseConnection()
    {
        $this->assertNotNull($this->db, 'Database connection is null');
        $this->assertFalse($this->db->connect_error, 'Failed to connect to database: ' . $this->db->connect_error);
    }

    public function testRedirectionLogic()
    {
        // Simulate session data for different user groups
        $_SESSION['usergroup'] = 'Admin';
        $redirectUrl = $this->getRedirectUrl();
        $this->assertEquals('/My Clinic/Dashboard/admin_dashboard.php', $redirectUrl);

        $_SESSION['usergroup'] = 'IT';
        $redirectUrl = $this->getRedirectUrl();
        $this->assertEquals('/My Clinic/Dashboard/it_dashboard.php', $redirectUrl);

        $_SESSION['usergroup'] = 'Doctor';
        $redirectUrl = $this->getRedirectUrl();
        $this->assertEquals('/My Clinic/Dashboard/doctor_dashboard.php', $redirectUrl);

        $_SESSION['usergroup'] = 'Nurse';
        $redirectUrl = $this->getRedirectUrl();
        $this->assertEquals('/My Clinic/Dashboard/nurse_dashboard.php', $redirectUrl);

        $_SESSION['usergroup'] = 'UnknownGroup';
        $redirectUrl = $this->getRedirectUrl();
        $this->assertEquals('/My Clinic/Dashboard/user_dashboard.php', $redirectUrl);
    }

    public function testRedirectToLoginWhenNotLoggedIn()
    {
        unset($_SESSION['usergroup']);
        $redirectUrl = $this->getRedirectUrl();
        $this->assertEquals('./Login/login.php', $redirectUrl);
    }

    private function getRedirectUrl(): string
    {
        ob_start();
        include 'path/to/your/script.php'; // Replace with the actual path to the script
        $output = ob_get_clean();

        // Extract the "Location" header from the output
        foreach (headers_list() as $header) {
            if (strpos($header, 'Location:') === 0) {
                return trim(substr($header, 9));
            }
        }

        return '';
    }
}
?>