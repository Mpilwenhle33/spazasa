<?php
// views/messages.php - No emojis, consistent theme
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpazaSa — Messages</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .messages-page { min-height: 100vh; background: var(--cream); padding: 20px; }
        .messages-container { max-width: 700px; margin: 0 auto; background: var(--white); border-radius: var(--radius-lg); padding: 30px; box-shadow: var(--shadow); }
        .back-link { display: inline-block; margin-bottom: 20px; color: var(--teal); text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
        .conversation-item { display: block; padding: 15px; border-bottom: 1px solid #eee; text-decoration: none; color: inherit; transition: background 0.2s; }
        .conversation-item:hover { background: var(--cream); }
        .conversation-item .header { display: flex; justify-content: space-between; align-items: center; }
        .conversation-item .name { font-weight: 700; }
        .conversation-item .unread { background: #e74c3c; color: white; padding: 2px 10px; border-radius: 50px; font-size: 12px; }
        .conversation-item .preview { margin: 5px 0 0 0; color: #888; font-size: 14px; }
        .conversation-item .time { font-size: 12px; color: #999; }
        .empty-message { text-align: center; padding: 40px; color: #888; }
    </style>
</head>
<body>

<div class="messages-page">
    <div class="messages-container">
        <a href="index.php?action=marketplace" class="back-link">Back to Marketplace</a>
        <h1 style="font-size:24px;font-weight:700;margin-bottom:20px;">Messages</h1>

        <?php if (empty($conversations)): ?>
            <div class="empty-message">
                <p style="font-size:18px;">No conversations yet.</p>
                <p style="font-size:14px;">Start chatting with a seller or buyer!</p>
            </div>
        <?php else: ?>
            <?php foreach ($conversations as $conv): ?>
                <a href="index.php?action=chat&user=<?= $conv['other_user_id'] ?>" class="conversation-item">
                    <div class="header">
                        <span class="name"><?= htmlspecialchars($conv['other_name'] ?? $conv['other_username']) ?></span>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <span class="unread"><?= $conv['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="preview"><?= htmlspecialchars(substr($conv['last_message'] ?? '', 0, 60)) ?></p>
                    <div class="time">
                        <?= $conv['last_message_time'] ? date('Y-m-d H:i', strtotime($conv['last_message_time'])) : '' ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>