<?php
$adminTitle = 'Customer Messages & Inquiries';
require_once __DIR__ . '/includes/header.php';

// Handle Delete Message
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Message deleted successfully.');
    } else {
        setFlashMessage('error', 'Failed to delete message: ' . $conn->error);
    }
    redirect(ADMIN_URL . '/messages.php');
}

// Handle Mark Read / Unread
if (isset($_GET['toggle_read'])) {
    $toggleId = intval($_GET['toggle_read']);
    $newStatus = intval($_GET['status']) === 1 ? 0 : 1;
    $stmt = $conn->prepare("UPDATE contact_messages SET is_read = ? WHERE id = ?");
    $stmt->bind_param("ii", $newStatus, $toggleId);
    $stmt->execute();
    redirect(ADMIN_URL . '/messages.php');
}

// Fetch all messages
$search = sanitize($_GET['search'] ?? '');
$sql = "SELECT * FROM contact_messages WHERE 1=1 ";
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%' OR message LIKE '%$search%') ";
}
$sql .= " ORDER BY is_read ASC, created_at DESC";

$messagesRes = $conn->query($sql);
?>

<div class="page-title-box">
    <div>
        <h1>Customer Inquiries</h1>
        <p>Review contact form submissions, customer inquiries, and feedback messages.</p>
    </div>
</div>

<div class="admin-card">
    <div class="card-header-flex">
        <h3 class="card-title">
            <i class="fas fa-inbox" style="color: var(--primary);"></i> Inquiries Inbox
        </h3>
        <form action="<?php echo ADMIN_URL; ?>/messages.php" method="GET" class="header-search">
            <i class="fas fa-search" style="color: var(--text-dim);"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search sender, subject...">
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Received Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($messagesRes && $messagesRes->num_rows > 0): ?>
                    <?php while ($msg = $messagesRes->fetch_assoc()): ?>
                        <tr style="<?php echo $msg['is_read'] ? 'opacity: 0.8;' : 'background: rgba(197, 160, 89, 0.03); font-weight: 500;'; ?>">
                            <td>
                                <?php if ($msg['is_read']): ?>
                                    <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--text-dim);">Read</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">New</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><strong><?php echo htmlspecialchars($msg['name']); ?></strong></div>
                                <div style="font-size: 0.78rem; color: var(--text-muted);"><?php echo htmlspecialchars($msg['email']); ?></div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($msg['subject'] ? $msg['subject'] : 'Inquiry'); ?></strong>
                            </td>
                            <td style="max-width: 320px; color: var(--text);">
                                <?php echo htmlspecialchars(truncateText($msg['message'], 100)); ?>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn btn-sm btn-outline" onclick="viewMsg(<?php echo htmlspecialchars(json_encode($msg)); ?>)" title="View Full Message">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="<?php echo ADMIN_URL; ?>/messages.php?toggle_read=<?php echo $msg['id']; ?>&status=<?php echo $msg['is_read']; ?>" 
                                       class="btn btn-sm btn-outline" title="<?php echo $msg['is_read'] ? 'Mark Unread' : 'Mark Read'; ?>">
                                        <i class="fas fa-<?php echo $msg['is_read'] ? 'envelope' : 'envelope-open'; ?>"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('Delete this message permanently?', '<?php echo ADMIN_URL; ?>/messages.php?delete=<?php echo $msg['id']; ?>')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 35px; color: var(--text-muted);">
                            No customer messages received yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Message View Modal -->
<div class="modal-overlay" id="msgModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalSubject">Message Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('msgModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                <div>
                    <h4 id="modalSender" style="font-size: 1rem; margin-bottom: 2px;"></h4>
                    <a id="modalEmail" href="" style="color: var(--primary); font-size: 0.85rem; text-decoration: none;"></a>
                </div>
                <div id="modalDate" style="font-size: 0.8rem; color: var(--text-muted);"></div>
            </div>

            <div style="font-size: 0.9rem; line-height: 1.7; color: var(--text); white-space: pre-wrap;" id="modalBody"></div>
        </div>
        <div class="modal-footer">
            <a id="modalReply" href="" class="btn btn-primary"><i class="fas fa-reply"></i> Reply via Email</a>
            <button type="button" class="btn btn-outline" onclick="closeModal('msgModal')">Close</button>
        </div>
    </div>
</div>

<script>
function viewMsg(msg) {
    document.getElementById('modalSubject').textContent = msg.subject || 'Customer Inquiry';
    document.getElementById('modalSender').textContent = msg.name;
    document.getElementById('modalEmail').textContent = msg.email;
    document.getElementById('modalEmail').href = 'mailto:' + msg.email;
    document.getElementById('modalReply').href = 'mailto:' + msg.email + '?subject=Re: ' + encodeURIComponent(msg.subject || 'Inquiry from LUXE');
    document.getElementById('modalDate').textContent = msg.created_at;
    document.getElementById('modalBody').textContent = msg.message;
    openModal('msgModal');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
