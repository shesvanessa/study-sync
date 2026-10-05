<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$subjects = getSubjects();
$materials = getMaterials();
$assignments = getAssignments();
$notifications = getUnreadNotifications($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudySync - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">📚 StudySync</div>
        <div class="nav-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
            <?php if ($_SESSION['role'] === 'course_rep'): ?>
                <span class="badge">Course Rep</span>
            <?php endif; ?>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3>Subjects</h3>
            <ul id="subjectList">
                <li><a href="#" data-subject-id="all">All Subjects</a></li>
                <?php foreach ($subjects as $subject): ?>
                    <li><a href="#" data-subject-id="<?php echo $subject['id']; ?>">
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </a></li>
                <?php endforeach; ?>
            </ul>
            
            <?php if ($_SESSION['role'] === 'course_rep'): ?>
                <div class="admin-section">
                    <h3>Admin Actions</h3>
                    <button id="showAddAssignmentBtn" class="btn-small">+ Add Assignment</button>
                </div>
            <?php endif; ?>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Notifications -->
            <div class="card">
                <h3>🔔 Notifications</h3>
                <div id="notificationArea">
                    <?php if (empty($notifications)): ?>
                        <p>No new notifications</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notification"><?php echo htmlspecialchars($notif['message']); ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upload Section -->
            <div class="card">
                <h3>📤 Upload Study Material</h3>
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="text" id="materialTitle" placeholder="Title" required>
                    <textarea id="materialDesc" placeholder="Description (optional)" rows="2"></textarea>
                    <select id="subjectId" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="file" id="materialFile" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt" required>
                    <button type="submit">Upload Material</button>
                </form>
                <div id="uploadMessage"></div>
            </div>

            <!-- Materials List -->
            <div class="card">
                <h3>📖 Study Materials</h3>
                <div id="materialsList">
                    <?php foreach ($materials as $material): ?>
                        <div class="material-item">
                            <div class="material-info">
                                <strong><?php echo htmlspecialchars($material['title']); ?></strong>
                                <small><?php echo htmlspecialchars($material['subject_name']); ?> • Uploaded by <?php echo htmlspecialchars($material['uploader_name']); ?></small>
                                <?php if ($material['description']): ?>
                                    <p><?php echo htmlspecialchars($material['description']); ?></p>
                                <?php endif; ?>
                                <a href="<?php echo $material['file_path']; ?>" download class="btn-download">Download</a>
                            </div>
                            <div class="material-rating">
                                <span>Rating: <?php echo number_format($material['avg_rating'], 1); ?> ⭐</span>
                                <button class="rate-btn" data-material="<?php echo $material['id']; ?>" data-rating="1">⭐</button>
                                <button class="rate-btn" data-material="<?php echo $material['id']; ?>" data-rating="2">⭐⭐</button>
                                <button class="rate-btn" data-material="<?php echo $material['id']; ?>" data-rating="3">⭐⭐⭐</button>
                                <button class="rate-btn" data-material="<?php echo $material['id']; ?>" data-rating="4">⭐⭐⭐⭐</button>
                                <button class="rate-btn" data-material="<?php echo $material['id']; ?>" data-rating="5">⭐⭐⭐⭐⭐</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Assignments -->
            <div class="card">
                <h3>📝 Upcoming Assignments</h3>
                <div id="assignmentsList">
                    <?php if (empty($assignments)): ?>
                        <p>No upcoming assignments</p>
                    <?php else: ?>
                        <?php foreach ($assignments as $assignment): ?>
                            <div class="assignment-item">
                                <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                <small><?php echo htmlspecialchars($assignment['subject_name']); ?></small>
                                <p><?php echo htmlspecialchars($assignment['description']); ?></p>
                                <span class="due-date">Due: <?php echo $assignment['due_date']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Discussion Forum -->
            <div class="card">
                <h3>💬 Discussion Forum</h3>
                <select id="discussionSubject">
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="discussionMessages" class="discussion-box"></div>
                <textarea id="newMessage" placeholder="Type your message..." rows="3"></textarea>
                <button id="sendMsgBtn" disabled>Send Message</button>
            </div>
        </main>
    </div>

    <!-- Add Assignment Modal (Course Rep only) -->
    <?php if ($_SESSION['role'] === 'course_rep'): ?>
    <div id="assignmentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Add New Assignment</h3>
            <form id="assignmentForm">
                <input type="text" id="assignTitle" placeholder="Assignment Title" required>
                <textarea id="assignDesc" placeholder="Description" rows="3"></textarea>
                <select id="assignSubject" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" id="assignDueDate" required>
                <button type="submit">Create Assignment</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="js/main.js"></script>
</body>
</html>