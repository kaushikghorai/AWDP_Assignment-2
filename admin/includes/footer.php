    </div> <!-- admin-body -->
</div> <!-- admin-main -->

<script>
function toggleAdminSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside content
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

function confirmDelete(message, url) {
    if (confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.')) {
        window.location.href = url;
    }
}

// Check window resize for mobile toggle display
function handleResize() {
    const btn = document.getElementById('sidebarToggle');
    if (btn) {
        if (window.innerWidth <= 992) {
            btn.style.display = 'inline-flex';
        } else {
            btn.style.display = 'none';
        }
    }
}
window.addEventListener('resize', handleResize);
handleResize();
</script>
</body>
</html>
