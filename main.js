// Auto-refresh materials when subject changes
document.querySelectorAll('#subjectList a').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const subjectId = link.dataset.subjectId;
        loadMaterials(subjectId);
        loadAssignments(subjectId);
    });
});

function loadMaterials(subjectId) {
    fetch(`get_materials.php?subject_id=${subjectId}`)
        .then(response => response.json())
        .then(materials => {
            const container = document.getElementById('materialsList');
            if (materials.length === 0) {
                container.innerHTML = '<p>No materials found</p>';
                return;
            }
            
            container.innerHTML = materials.map(m => `
                <div class="material-item">
                    <div class="material-info">
                        <strong>${escapeHtml(m.title)}</strong>
                        <small>${escapeHtml(m.subject_name)} • Uploaded by ${escapeHtml(m.uploader_name)}</small>
                        ${m.description ? `<p>${escapeHtml(m.description)}</p>` : ''}
                        <a href="${m.file_path}" download class="btn-download">Download</a>
                    </div>
                    <div class="material-rating">
                        <span>Rating: ${m.avg_rating} ⭐</span>
                        <button class="rate-btn" data-material="${m.id}" data-rating="1">⭐</button>
                        <button class="rate-btn" data-material="${m.id}" data-rating="2">⭐⭐</button>
                        <button class="rate-btn" data-material="${m.id}" data-rating="3">⭐⭐⭐</button>
                        <button class="rate-btn" data-material="${m.id}" data-rating="4">⭐⭐⭐⭐</button>
                        <button class="rate-btn" data-material="${m.id}" data-rating="5">⭐⭐⭐⭐⭐</button>
                    </div>
                </div>
            `).join('');
            
            // Attach rating events
            document.querySelectorAll('.rate-btn').forEach(btn => {
                btn.addEventListener('click', rateMaterial);
            });
        });
}

function loadAssignments(subjectId) {
    fetch(`get_assignments.php?subject_id=${subjectId}`)
        .then(response => response.json())
        .then(assignments => {
            const container = document.getElementById('assignmentsList');
            if (assignments.length === 0) {
                container.innerHTML = '<p>No upcoming assignments</p>';
                return;
            }
            
            container.innerHTML = assignments.map(a => `
                <div class="assignment-item">
                    <strong>${escapeHtml(a.title)}</strong>
                    <small>${escapeHtml(a.subject_name)}</small>
                    <p>${escapeHtml(a.description)}</p>
                    <span class="due-date">Due: ${a.due_date}</span>
                </div>
            `).join('');
        });
}

function rateMaterial(e) {
    const materialId = e.target.dataset.material;
    const rating = e.target.dataset.rating;
    
    fetch('rate_material.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `material_id=${materialId}&rating=${rating}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload current subject materials
            const currentSubject = document.querySelector('#subjectList a.active')?.dataset.subjectId || 'all';
            loadMaterials(currentSubject);
        }
    });
}

// Upload material
document.getElementById('uploadForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('title', document.getElementById('materialTitle').value);
    formData.append('description', document.getElementById('materialDesc').value);
    formData.append('subject_id', document.getElementById('subjectId').value);
    formData.append('file', document.getElementById('materialFile').files[0]);
    
    fetch('upload_material.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const msgDiv = document.getElementById('uploadMessage');
        if (data.success) {
            msgDiv.innerHTML = '<div class="alert success">' + data.message + '</div>';
            document.getElementById('uploadForm').reset();
            loadMaterials('all');
        } else {
            msgDiv.innerHTML = '<div class="alert error">' + data.message + '</div>';
        }
        setTimeout(() => { msgDiv.innerHTML = ''; }, 3000);
    });
});

// Discussion functionality
let discussionInterval = null;

document.getElementById('discussionSubject')?.addEventListener('change', (e) => {
    const subjectId = e.target.value;
    const sendBtn = document.getElementById('sendMsgBtn');
    
    if (subjectId) {
        sendBtn.disabled = false;
        loadDiscussions(subjectId);
        
        if (discussionInterval) clearInterval(discussionInterval);
        discussionInterval = setInterval(() => loadDiscussions(subjectId), 5000);
    } else {
        sendBtn.disabled = true;
        if (discussionInterval) clearInterval(discussionInterval);
    }
});

function loadDiscussions(subjectId) {
    fetch(`get_discussions.php?subject_id=${subjectId}`)
        .then(response => response.json())
        .then(messages => {
            const container = document.getElementById('discussionMessages');
            if (messages.length === 0) {
                container.innerHTML = '<p>No messages yet. Start the discussion!</p>';
                return;
            }
            
            container.innerHTML = messages.map(m => `
                <div class="discussion-message">
                    <strong>${escapeHtml(m.fullname)}</strong>
                    <small>${new Date(m.created_at).toLocaleString()}</small>
                    <p>${escapeHtml(m.message)}</p>
                </div>
            `).join('');
            container.scrollTop = container.scrollHeight;
        });
}

document.getElementById('sendMsgBtn')?.addEventListener('click', () => {
    const subjectId = document.getElementById('discussionSubject').value;
    const message = document.getElementById('newMessage').value;
    
    if (!subjectId || !message.trim()) return;
    
    fetch('add_discussion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `subject_id=${subjectId}&message=${encodeURIComponent(message)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('newMessage').value = '';
            loadDiscussions(subjectId);
        }
    });
});

// Modal for adding assignments (course rep only)
const modal = document.getElementById('assignmentModal');
const showBtn = document.getElementById('showAddAssignmentBtn');
const closeBtn = document.querySelector('.close');

if (showBtn) {
    showBtn.onclick = () => { modal.style.display = 'block'; };
    closeBtn.onclick = () => { modal.style.display = 'none'; };
    window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; };
    
    document.getElementById('assignmentForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        
        fetch('add_assignment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `title=${encodeURIComponent(document.getElementById('assignTitle').value)}&description=${encodeURIComponent(document.getElementById('assignDesc').value)}&subject_id=${document.getElementById('assignSubject').value}&due_date=${document.getElementById('assignDueDate').value}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.style.display = 'none';
                loadAssignments('all');
                alert('Assignment created successfully!');
            }
        });
    });
}

// Helper function to escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Refresh notifications periodically
setInterval(() => {
    location.reload(); // Simple refresh - in production, use fetch
}, 30000);