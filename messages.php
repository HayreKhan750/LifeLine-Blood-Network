<?php
require_once 'includes/functions.php';
requireAuth();

$userId = $_SESSION['user_id'];

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['content'])) {
    validateCsrf();
    $receiverId = (int)$_POST['receiver_id'];
    $content = trim($_POST['content']);
    $subject = trim($_POST['subject'] ?? '');
    
    if (empty($content)) {
        setFlash('Message cannot be empty.', 'danger');
        redirect(baseUrl() . '/messages.php');
    }
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $receiverId, $subject, $content]);
    
    // Create notification for receiver
    $senderName = '';
    if (isDonor()) {
        $profile = getDonorProfile($pdo, $userId);
        $senderName = $profile['full_name'] ?? '';
    } elseif (isHospital()) {
        $profile = getHospitalProfile($pdo, $userId);
        $senderName = $profile['hospital_name'] ?? '';
    }
    
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'message', ?, ?, '/messages.php')");
    $stmt->execute([$receiverId, 'New message from ' . $senderName, 'You have received a new message. Click to view.']);
    
    setFlash('Message sent successfully!', 'success');
    redirect(baseUrl() . '/messages.php?conversation=' . $receiverId);
}

// Mark as read
if (isset($_GET['read'])) {
    $msgId = (int)$_GET['read'];
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$msgId, $userId]);
}

// Get conversations
$stmt = $pdo->prepare("
    SELECT 
        CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as other_id,
        MAX(m.created_at) as last_message,
        COUNT(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 END) as unread,
        (SELECT content FROM messages m2 WHERE m2.id = MAX(m.id)) as last_content
    FROM messages m
    WHERE m.sender_id = ? OR m.receiver_id = ?
    GROUP BY other_id
    ORDER BY last_message DESC
");
$stmt->execute([$userId, $userId, $userId, $userId]);
$conversations = $stmt->fetchAll();

// Get conversation messages
$activeConversation = isset($_GET['conversation']) ? (int)$_GET['conversation'] : null;
$messages = [];
$otherUser = null;

if ($activeConversation) {
    $stmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
        LIMIT 100
    ");
    $stmt->execute([$userId, $activeConversation, $activeConversation, $userId]);
    $messages = $stmt->fetchAll();
    
    // Mark all as read
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
    $stmt->execute([$userId, $activeConversation]);
    
    // Get other user info
    $otherUser = getUserById($pdo, $activeConversation);
}

$pageTitle = 'Messages';
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>&#9993; Messages</h1>
        <?php if (!$activeConversation && isHospital()): ?>
        <a href="<?php echo baseUrl(); ?>/find_donors.php" class="btn btn-small">Find Donors to Message</a>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 320px 1fr; gap: 24px;">
    <!-- Conversation List -->
    <div class="card" style="padding: 0; overflow: hidden; max-height: 600px; overflow-y: auto;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--glass-border); font-weight: 700; color: var(--text-primary);">
            Conversations
        </div>
        
        <?php if (count($conversations) > 0): ?>
            <?php foreach ($conversations as $conv): 
                $other = getUserById($pdo, $conv['other_id']);
                $otherProfile = null;
                $otherName = 'Unknown';
                if ($other) {
                    if ($other['role'] === 'donor') {
                        $otherProfile = getDonorProfile($pdo, $conv['other_id']);
                        $otherName = $otherProfile['full_name'] ?? 'Donor';
                    } elseif ($other['role'] === 'hospital') {
                        $otherProfile = getHospitalProfile($pdo, $conv['other_id']);
                        $otherName = $otherProfile['hospital_name'] ?? 'Hospital';
                    }
                }
                $isActive = $activeConversation == $conv['other_id'];
            ?>
            <a href="<?php echo baseUrl(); ?>/messages.php?conversation=<?php echo $conv['other_id']; ?>" 
               style="display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-bottom: 1px solid var(--glass-border); text-decoration: none; color: inherit; transition: var(--transition); background: <?php echo $isActive ? 'rgba(230,57,70,0.1)' : 'transparent'; ?>; border-left: 3px solid <?php echo $isActive ? 'var(--crimson)' : 'transparent'; ?>;"
               onmouseover="this.style.background='<?php echo $isActive ? 'rgba(230,57,70,0.15)' : 'var(--surface-hover)'; ?>'" 
               onmouseout="this.style.background='<?php echo $isActive ? 'rgba(230,57,70,0.1)' : 'transparent'; ?>'">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-crimson); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                    <?php echo strtoupper(substr($otherName, 0, 1)); ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;"><?php echo htmlspecialchars($otherName); ?></span>
                        <span style="font-size: 0.7rem; color: var(--text-muted);"><?php echo date('M j', strtotime($conv['last_message'])); ?></span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars(substr($conv['last_content'], 0, 40)); ?>
                    </div>
                </div>
                <?php if ($conv['unread'] > 0): ?>
                <span style="background: var(--crimson); color: #fff; font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: var(--radius-full); min-width: 20px; text-align: center;"><?php echo $conv['unread']; ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
        <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
            <div style="font-size: 2rem; margin-bottom: 8px;">&#9993;</div>
            <p style="font-size: 0.9rem;">No conversations yet.</p>
            <?php if (isHospital()): ?>
            <p style="font-size: 0.8rem; margin-top: 4px;">Find donors and start messaging!</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Message Area -->
    <div>
        <?php if ($activeConversation && $otherUser): ?>
        <div class="card" style="padding: 0; display: flex; flex-direction: column; height: 600px;">
            <!-- Header -->
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--glass-border); display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--gradient-crimson); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 1rem;">
                    <?php 
                    $oname = '';
                    if ($otherUser['role'] === 'donor') {
                        $op = getDonorProfile($pdo, $activeConversation);
                        $oname = $op['full_name'] ?? '';
                    } else {
                        $op = getHospitalProfile($pdo, $activeConversation);
                        $oname = $op['hospital_name'] ?? '';
                    }
                    echo strtoupper(substr($oname, 0, 1)); 
                    ?>
                </div>
                <div>
                    <div style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($oname); ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo ucfirst($otherUser['role']); ?></div>
                </div>
            </div>
            
            <!-- Messages -->
            <div style="flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($messages as $msg): 
                    $isMine = $msg['sender_id'] == $userId;
                ?>
                <div style="display: flex; justify-content: <?php echo $isMine ? 'flex-end' : 'flex-start'; ?>;">
                    <div style="max-width: 70%; padding: 12px 16px; border-radius: <?php echo $isMine ? '16px 16px 4px 16px' : '16px 16px 16px 4px'; ?>; background: <?php echo $isMine ? 'var(--gradient-crimson)' : 'var(--bg-dark-3)'; ?>; color: <?php echo $isMine ? '#fff' : 'var(--text-primary)'; ?>; border: <?php echo $isMine ? 'none' : '1px solid var(--glass-border)'; ?>;">
                        <?php if ($msg['subject']): ?>
                        <div style="font-weight: 700; font-size: 0.85rem; margin-bottom: 4px; <?php echo $isMine ? 'color:rgba(255,255,255,0.9)' : 'color:var(--crimson-light)'; ?>"><?php echo htmlspecialchars($msg['subject']); ?></div>
                        <?php endif; ?>
                        <div style="font-size: 0.9rem; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($msg['content'])); ?></div>
                        <div style="font-size: 0.7rem; opacity: 0.7; margin-top: 6px; text-align: right;">
                            <?php echo date('M j, g:i A', strtotime($msg['created_at'])); ?>
                            <?php if ($isMine && $msg['is_read']): ?>
                            <span style="margin-left: 4px;">&#10003;</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Input -->
            <div style="padding: 16px 24px; border-top: 1px solid var(--glass-border);">
                <form method="POST" action="" class="flex gap-2" style="align-items: flex-end;">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                    <input type="hidden" name="receiver_id" value="<?php echo $activeConversation; ?>">
                    <div style="flex: 1;">
                        <textarea name="content" required placeholder="Type your message..." style="min-height: 50px; resize: none;" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn" style="padding: 14px 20px;">&#10148;</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card" style="display: flex; align-items: center; justify-content: center; min-height: 400px; text-align: center;">
            <div>
                <div style="font-size: 4rem; margin-bottom: 16px; opacity: 0.3;">&#9993;</div>
                <h3 style="color: var(--text-primary); margin-bottom: 8px;">Select a Conversation</h3>
                <p style="color: var(--text-muted);">Choose a conversation from the sidebar to view messages.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
