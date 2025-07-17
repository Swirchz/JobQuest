<?php
// Connect to DB
include 'db_connect.php'; // Include connection

// If archive is triggered via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "UPDATE applications SET archived = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: archive.php?archived=true");
        exit();
    } else {
        echo "Error archiving job: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all archived jobs
$sql = "SELECT * FROM applications WHERE archived = 1 ORDER BY date_applied DESC";
$jobs = $conn->query($sql);

if (!$jobs) {
    die("Query failed: " . $conn->error);
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
                <h1 class="mt-4">Archived</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Archived</li>
                </ol>
                 
                
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
                                                    data-place="<?= htmlspecialchars($row['place']) ?>" 
                                                    data-status="<?= htmlspecialchars($row['status']) ?>" 
                                                    data-notes="<?= htmlspecialchars($row['notes']) ?>">
                                                    View
                                                </button>

                                                <!-- Delete Button (Trigger Modal) -->
                                                <button 
                                                    class="btn btn-danger btn-sm delete-btn" 
                                                    data-id="<?= $row['id']; ?>" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal">
                                                    Delete
                                                </button>


                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        

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
                                        <p><strong>Place</strong> <span id="modalPlace"></span></p>
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

                        <!-- Delete Confirmation Modal -->
                        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <form method="GET" action="delete.php">
                                <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                Are you sure you want to delete this job application?
                                <input type="hidden" name="id" id="delete_id">
                                </div>
                                <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Delete</button>
                                </div>
                            </form>
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

<!--View Job Applications-->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".view-message-btn").forEach(button => {
            button.addEventListener("click", function () {
                document.getElementById("modalCompany").textContent = this.getAttribute("data-company");
                document.getElementById("modalTitle").textContent = this.getAttribute("data-title");
                document.getElementById("modalLink").textContent = this.getAttribute("data-link");
                document.getElementById("modalDate").textContent = this.getAttribute("data-date");
                document.getElementById("modalPlace").textContent = this.getAttribute("data-place");
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

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".delete-btn");

    deleteButtons.forEach(button => {
      button.addEventListener("click", function () {
        const id = this.getAttribute("data-id");
        document.getElementById("delete_id").value = id;
      });
    });
  });
</script>


    </body>
</html>
