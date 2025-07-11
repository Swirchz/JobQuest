<?php
include 'db_connect.php'; // Include connection

// FETCH jobs for viewing in table form
function getJobs() {
    global $conn;
    $sql = "SELECT * FROM applications WHERE archived = 0 ORDER BY date_applied DESC";
    return $conn->query($sql);
}

$jobs = getJobs();

// INSERT new job into the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $company_name = $_POST['company_name'];
    $job_title = $_POST['job_title'];
    $job_link = empty($_POST['job_link']) ? "N/A" : $_POST['job_link']; // this line
    $date_applied = $_POST['date_applied'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];

    // Handle optional file uploads
    $resume_file = $_FILES['resume']['name'] ?? null;
    $cover_letter_file = $_FILES['cover_letter']['name'] ?? null;
    $screenshot_file = $_FILES['screenshot']['name'] ?? null;

    // Upload handling (save to 'upload/' folder)
    $upload_dir = "upload/";
    $resume_path = $upload_dir . basename($resume_file);
    $cover_letter_path = $upload_dir . basename($cover_letter_file);
    $screenshot_path = $upload_dir . basename($screenshot_file);

    // Move files (you can add validation here)
    move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path);
    move_uploaded_file($_FILES['cover_letter']['tmp_name'], $cover_letter_path);
    move_uploaded_file($_FILES['screenshot']['tmp_name'], $screenshot_path);

    // Insert into database
    $sql = "INSERT INTO applications 
        (company_name, job_title, job_link, date_applied, status, notes, resume_file, cover_letter_file, screenshot_file) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", 
        $company_name, 
        $job_title, 
        $job_link, 
        $date_applied, 
        $status, 
        $notes, 
        $resume_file, 
        $cover_letter_file, 
        $screenshot_file
    );

    if ($stmt->execute()) {
        echo "<script>
                localStorage.setItem('jobAdded', 'true');
                window.location.href = 'index.php';
        </script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}



?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>JobQuest</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.html">JobQuest</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading"></div>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link" href="archive.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-archive"></i></div>
                                Archive
                            </a>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                 <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Dashboard</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
                 <!-- Add New Job Button (Opens Modal) -->
                        <div class="mb-3">
                            <button class="btn btn-success" id="addProject" data-bs-toggle="modal" data-bs-target="#addProjectModal">+ Add Job</button>
                        </div>
                
                        <!-- Job Table -->
                            <table class="table table-bordered table-striped text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Company</th>
                                        <th>Job Title</th>
                                        <th>Date Applied</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $i = 1;
                                        while ($row = $jobs->fetch_assoc()) {
                                    ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($row['date_applied']); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                        switch ($row['status']) {
                                                            case 'Applied': echo 'bg-primary'; break;
                                                            case 'Interview': echo 'bg-warning text-dark'; break;
                                                            case 'Offered': echo 'bg-info'; break;
                                                            case 'Accepted': echo 'bg-success'; break;
                                                            case 'Rejected': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary'; 
                                                        }
                                                    ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <!-- View Button -->
                                               <button class="btn btn-primary view-message-btn"
                                                    data-id="<?= $row['id'] ?>" 
                                                    data-company="<?= htmlspecialchars($row['company_name']) ?>" 
                                                    data-title="<?= htmlspecialchars($row['job_title']) ?>" 
                                                    data-link="<?= htmlspecialchars($row['job_link']) ?>" 
                                                    data-date="<?= htmlspecialchars($row['date_applied']) ?>" 
                                                    data-status="<?= htmlspecialchars($row['status']) ?>" 
                                                    data-notes="<?= htmlspecialchars($row['notes']) ?>">
                                                    View
                                                </button>

                                                <!-- Edit Button -->
                                                <a href="#" class="btn btn-warning btn-sm edit-btn" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-job_title="<?php echo htmlspecialchars($row['job_title']); ?>" 
                                                data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                                data-notes="<?php echo htmlspecialchars($row['notes']); ?>" 
                                                data-bs-toggle="modal" data-bs-target="#editModal">
                                                Edit
                                                </a>

                                                <!-- Archive Button -->
                                                <a href="archive.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm">Archive</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>


                            <!-- Add Job Modal -->
                            <div class="modal fade" id="addProjectModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Add New Job Application</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="addProjectForm" enctype="multipart/form-data" method="POST" action="index.php">
                                                <div class="mb-3">
                                                    <label class="form-label">Company Name:</label>
                                                    <input type="text" name="company_name" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Job Title:</label>
                                                    <input type="text" name="job_title" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Job Link <small class="text-muted">(optional)</small>:</label>
                                                    <input type="url" name="job_link" class="form-control" placeholder="https://example.com (or leave blank)">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Date Applied:</label>
                                                    <input type="date" name="date_applied" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status:</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="Applied">Applied</option>
                                                        <option value="Interview">Interview</option>
                                                        <option value="Offered">Offered</option>
                                                        <option value="Accepted">Accepted</option>
                                                        <option value="Rejected">Rejected</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Notes:</label>
                                                    <textarea name="notes" class="form-control" rows="3"></textarea>
                                                </div>
                                               
                                                <!--  <div class="mb-3">
                                                    <label class="form-label">Upload Resume (optional):</label>
                                                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Upload Cover Letter (optional):</label>
                                                    <input type="file" name="cover_letter" class="form-control" accept=".pdf,.doc,.docx">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Screenshot (optional):</label>
                                                    <input type="file" name="screenshot" class="form-control" accept="image/*">
                                                </div> -->
                                                <button type="submit" class="btn btn-primary">Add Job</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Add Success Modal -->
                            <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Success</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Job added successfully!</p> 
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" id="redirectButton">OK</button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        <!-- Update Modal -->
                        <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Job</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="update_job.php">
                                            <input type="hidden" name="id" id="edit_id">

                                            <div class="mb-3">
                                                <label for="edit_job_title" class="form-label">Job Title:</label>
                                                <input type="text" name="job_title" id="edit_job_title" class="form-control" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="edit_status" class="form-label">Status:</label>
                                                <select name="status" id="edit_status" class="form-select" required>
                                                    <option value="Applied">Applied</option>
                                                    <option value="Interview">Interview</option>
                                                    <option value="Offered">Offered</option>
                                                    <option value="Accepted">Accepted</option>
                                                    <option value="Rejected">Rejected</option>
                                                </select>
                                            </div>

                                             <div class="mb-3">
                                                <label for="edit_notes" class="form-label">Notes:</label>
                                                <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                                            </div>

                                            <div class="mt-3">
                                                <button type="submit" name="update" class="btn btn-success">Update</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Update Success Modal -->
                        <div class="modal fade" id="updatedSuccessModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Success</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Job updated successfully!</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" id="redirectAfterUpdate">OK</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- View Modal -->
                        <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="messageModalLabel">Job Application Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Company Name:</strong> <span id="modalCompany"></span></p>
                                        <p><strong>Job Title:</strong> <span id="modalTitle"></span></p>
                                        <p><strong>Job Link:</strong> 
                                            <a id="modalLink" href="#" target="_blank" class="d-block text-break"></a>
                                        </p>
                                        <p><strong>Date Applied:</strong> <span id="modalDate"></span></p>
                                        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                                        <p><strong>Notes:</strong></p>
                                        <p id="modalNotes"></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="confirmReadBtn">OK</button>
                                    </div>
                                </div>
                            </div>
                        </div>


            </div>
        </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; JobQuest 2025</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
        <!--Add Job Applications-->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                if (localStorage.getItem("jobAdded") === "true") {
                    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                    localStorage.removeItem("jobAdded");
                }

                document.getElementById("redirectButton").addEventListener("click", function() {
                    window.location.href = "index.php"; // Adjust if you want to go elsewhere
                });
            });
        </script>

        <!--Update Job Applications-->
      <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".edit-btn").forEach(button => {
            button.addEventListener("click", function () {
                document.getElementById("edit_id").value = this.getAttribute("data-id");
                document.getElementById("edit_job_title").value = this.getAttribute("data-job_title");
                document.getElementById("edit_status").value = this.getAttribute("data-status");
                document.getElementById("edit_notes").value = this.getAttribute("data-notes");
            });
        });

        // Show success modal if update was successful
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has("updated")) {
            const updatedSuccessModal = new bootstrap.Modal(document.getElementById("updatedSuccessModal"));
            updatedSuccessModal.show();
        }

        // Redirect after clicking OK on success modal
        document.getElementById("redirectAfterUpdate")?.addEventListener("click", function () {
            window.location.href = "index.php"; // Adjust if needed
        });
    });
</script>

<!--View Job Applications-->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".view-message-btn").forEach(button => {
            button.addEventListener("click", function () {
                document.getElementById("modalCompany").textContent = this.getAttribute("data-company");
                document.getElementById("modalTitle").textContent = this.getAttribute("data-title");
                document.getElementById("modalLink").textContent = this.getAttribute("data-link");
                document.getElementById("modalDate").textContent = this.getAttribute("data-date");
                document.getElementById("modalStatus").textContent = this.getAttribute("data-status");
                document.getElementById("modalNotes").textContent = this.getAttribute("data-notes");
                document.getElementById("modalLink").textContent = this.getAttribute("data-link");
                document.getElementById("modalLink").href = this.getAttribute("data-link");

                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                modal.show();
            });
        });

        document.getElementById("confirmReadBtn").addEventListener("click", function () {
            const modal = bootstrap.Modal.getInstance(document.getElementById('messageModal'));
            modal.hide();
        });
    });
</script>


    </body>
</html>
