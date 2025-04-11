<?php


require_once '../config.php';
// navbar.php
include '../init.php';
include '../config.php';

// Function to get dashboard link based on user role
function getDashboardLink($usergroup) {
    switch ($usergroup) {
        case 'Admin':
            return "../Dashboard/admin_dashboard.php";
        case 'Doctor':
            return "../Dashboard/doctor_dashboard.php";
        case 'Lab Scientist':
            return "../Dashboard/lab_scientist_dashboard.php";
        default:
            return "../Dashboard/user_dashboard.php";
    }
}

// Function to get video consultation link based on user role
function getVideoConsultation($usergroup) {
    switch ($usergroup) {
        case 'Admin':
        case 'Doctor':
        case 'Lab Scientist':
            return "../Video_call/session.php";
        default:
            return "../Waiting_room/all_waiting_rooms.php";
    }
}

// Function to get waiting room link based on user role
function getWaitingRoomLink($usergroup) {
    switch ($usergroup) {
        case 'Admin':
            return "../Waiting_room/all_waiting_rooms.php";
        case 'Doctor':
            return "../Waiting_room/doctor_waiting_room.php";
        case 'Lab Scientist':
            return "../Waiting_room/laboratory_waiting_room.php";
        default:
            return "../Waiting_room/all_waiting_rooms.php";
    }
}

// Assuming $usergroup is set in the session or fetched from the database
$usergroup = $_SESSION['usergroup'] ?? 'Guest';


// ----------------------------------------------------
// Make sure the table exists (optional auto-create)
// ----------------------------------------------------
$conn->query("CREATE TABLE IF NOT EXISTS lab_test_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    content TEXT NOT NULL
)");

// ----------------------------------------------------
// Fetch existing templates
// ----------------------------------------------------
function fetchTemplates($conn) {
    $sql = "SELECT * FROM lab_test_templates ORDER BY id DESC";
    $result = $conn->query($sql);
    $templates = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $templates[] = $row;
        }
    }
    return $templates;
}

// ----------------------------------------------------
// Handle create template
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_template'])) {
    // Simple anti-spam check: wait 2 minutes
    if (!isset($_SESSION['last_submit']) || (time() - $_SESSION['last_submit']) > 2 * 60) {
        $templateName = $_POST['template_name'] ?? '';
        $templateContent = $_POST['template_content'] ?? '';

        $stmt = $conn->prepare("INSERT INTO lab_test_templates (name, content) VALUES (?, ?)");
        if (!$stmt) {
            error_log("SQL Preparation Error: " . $conn->error);
            $message = "Error preparing query: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $templateName, $templateContent);
            if ($stmt->execute()) {
                $message = "Template created successfully!";
                $_SESSION['last_submit'] = time();
            } else {
                error_log("SQL Execution Error: " . $stmt->error);
                $message = "Error creating template: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Please wait 2 minutes before creating another template.";
    }
}

// ----------------------------------------------------
// Handle update template
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_template'])) {
    $templateId = $_POST['template_id'] ?? '';
    $editName = $_POST['edit_name'] ?? '';
    $editContent = $_POST['edit_content'] ?? '';

    if (!empty($templateId)) {
        $stmt = $conn->prepare("UPDATE lab_test_templates SET name = ?, content = ? WHERE id = ?");
        if (!$stmt) {
            error_log("SQL Preparation Error: " . $conn->error);
            $message = "Error preparing update query: " . $conn->error;
        } else {
            $stmt->bind_param("ssi", $editName, $editContent, $templateId);
            if ($stmt->execute()) {
                $message = "Template updated successfully!";
            } else {
                error_log("SQL Execution Error: " . $stmt->error);
                $message = "Error updating template: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid template ID.";
    }
}

// ----------------------------------------------------
// Handle delete template
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_template'])) {
    $deleteId = $_POST['delete_id'] ?? '';
    if (!empty($deleteId)) {
        $stmt = $conn->prepare("DELETE FROM lab_test_templates WHERE id = ?");
        if (!$stmt) {
            error_log("SQL Preparation Error: " . $conn->error);
            $message = "Error preparing delete query: " . $conn->error;
        } else {
            $stmt->bind_param("i", $deleteId);
            if ($stmt->execute()) {
                $message = "Template deleted successfully!";
            } else {
                error_log("SQL Execution Error: " . $stmt->error);
                $message = "Error deleting template: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid template ID to delete.";
    }
}

// ----------------------------------------------------
// Fetch existing templates after all CRUD operations
// ----------------------------------------------------
$templates = fetchTemplates($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Lab Test Templates</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <!-- Bootstrap CSS -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <!-- TinyMCE (Stable Version 6; remove or replace the key if needed) -->
  <script
    src="https://cdn.tiny.cloud/1/u1rkztuuis5q511n45h77nbkmb1vs8c8qadu6p3l1y3nnuln/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"
  ></script>
  
  <script>
    // Initialize TinyMCE for "create" form's textarea
  document.addEventListener('DOMContentLoaded', function () {
  tinymce.init({
    selector: '#template_content',
    debug: true,
    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
    height: 300
  });
});

  </script>
</head>
<body class="bg-light">
<div class="container mt-5">
  <h1 class="text-center text-primary mb-4">Lab Test Template Management</h1>

  <!-- Show popup message if set -->
  <?php if (!empty($message)): ?>
    <div class="alert alert-info" role="alert" id="popup-message">
      <?= htmlspecialchars($message); ?>
    </div>
    <script>
      setTimeout(function() {
        var popup = document.getElementById('popup-message');
        if (popup) {
          popup.style.display = 'none';
        }
      }, 5000);
    </script>
  <?php endif; ?>
  <!-- navbar-->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="card mb-4" style="background-color:rgb(247, 227, 222);">
      
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" href="<?= getDashboardLink($usergroup); ?>">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= getWaitingRoomLink($usergroup); ?>">
                <i class="fas fa-users me-2"></i> Waiting Room
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= getVideoConsultation($usergroup); ?>">
                <i class="fas fa-video me-2"></i> Video Consultation
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../Laboratory/create_lab_test.php">
                <i class="fas fa-plus-circle me-2"></i> Create Lab Template
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../Laboratory/lab_requests.php">
                <i class="fas fa-file-alt me-2"></i> View Requested Laboratory
            </a>
        </li>
    </ul>
</div>

    </div>
</nav>
  <!-- Create New Template -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h2>Create New Lab Template</h2>
    </div>
    <div class="card-body">
      <form action="" method="post">
        <div class="mb-3">
          <label for="template_name" class="form-label">Template Name:</label>
          <input
            type="text"
            id="template_name"
            name="template_name"
            class="form-control"
            required
          />
        </div>
        <div class="mb-3">
          <label for="template_content" class="form-label">Template Content:</label>
          <textarea
            id="template_content"
            name="template_content"
            class="form-control"
          ></textarea>
        </div>
        <button type="submit" name="create_template" class="btn btn-success">
          Create Template
        </button>
      </form>
    </div>
  </div>

  <!-- Existing Templates -->
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h2>Existing Templates</h2>
    </div>
    <div class="card-body">
      <?php if (count($templates) > 0): ?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Template Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($templates as $template): ?>
              <tr>
                <td><?= htmlspecialchars($template['name']); ?></td>
                <td>
                  <!-- View Button -->
                  <button
                    class="btn btn-info btn-sm"
                    onclick="openViewModal(
                      '<?= $template['id']; ?>', 
                      `<?= htmlspecialchars($template['content'], ENT_QUOTES); ?>`
                    )"
                  >
                    View
                  </button>

                  <!-- Edit Button -->
                  <button
                    class="btn btn-warning btn-sm"
                    onclick="openEditModal(
                      '<?= $template['id']; ?>',
                      `<?= htmlspecialchars($template['name'], ENT_QUOTES); ?>`,
                      `<?= htmlspecialchars($template['content'], ENT_QUOTES); ?>`
                    )"
                  >
                    Edit
                  </button>

                  <!-- Delete Button -->
                  <button
                    class="btn btn-danger btn-sm"
                    onclick="openDeleteModal('<?= $template['id']; ?>')"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted">No templates found.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- View Modal -->
<div
  class="modal fade"
  id="viewModal"
  tabindex="-1"
  aria-labelledby="viewModalLabel"
  aria-hidden="true"
>
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalLabel">View Template</h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"
        ></button>
      </div>
      <div class="modal-body" id="viewContent">
        <!-- Content loaded dynamically -->
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div
  class="modal fade"
  id="editModal"
  tabindex="-1"
  aria-labelledby="editModalLabel"
  aria-hidden="true"
>
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Template</h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"
        ></button>
      </div>
      <form action="" method="post">
        <div class="modal-body">
          <input type="hidden" id="editTemplateId" name="template_id" />
          <div class="mb-3">
            <label for="editName" class="form-label">Template Name</label>
            <input
              type="text"
              id="editName"
              name="edit_name"
              class="form-control"
              required
            />
          </div>
          <div class="mb-3">
            <label for="editContent" class="form-label">Template Content</label>
            <textarea
              id="editContent"
              name="edit_content"
              class="form-control"
              required
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            name="update_template"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div
  class="modal fade"
  id="deleteModal"
  tabindex="-1"
  aria-labelledby="deleteModalLabel"
  aria-hidden="true"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Delete Template</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this template?</p>
          <input type="hidden" id="deleteTemplateId" name="delete_id" />
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-danger"
            name="delete_template"
          >
            Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>

<script>
  // ==============
  // View Modal
  // ==============
  function openViewModal(id, content) {
    document.getElementById('viewContent').innerHTML = content;
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    viewModal.show();
  }

  // ==============
  // Edit Modal
  // ==============
  function openEditModal(id, name, content) {
    // Fill hidden/input fields
    document.getElementById('editTemplateId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editContent').value = content;

    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();

    // Re-init TinyMCE for the edit textarea
    setTimeout(() => {
      if (tinymce.get('editContent')) {
        tinymce.get('editContent').destroy();
      }
      tinymce.init({
        selector: '#editContent',
        debug: true,
        plugins: 'advlist autolink lists link image charmap preview anchor ' +
                 'searchreplace visualblocks code fullscreen insertdatetime media table ' +
                 'paste code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | ' +
                 'alignleft aligncenter alignright alignjustify | bullist numlist ' +
                 'outdent indent | removeformat | help',
        height: 300
      });
    }, 200);
  }

  // ==============
  // Delete Modal
  // ==============
  function openDeleteModal(id) {
    document.getElementById('deleteTemplateId').value = id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
  }

  // ==============
  // Force TinyMCE to save content
  // into <textarea> on all form submissions
  // ==============
  const forms = document.querySelectorAll('form');
  forms.forEach((form) => {
    form.addEventListener('submit', function() {
      tinymce.triggerSave();
    });
  });
</script>

</body>
</html>
