<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Envoyer un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $destinataire_id = $_POST['destinataire_id'];
    $annonce_id = $_POST['annonce_id'] ?? null;
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, annonce_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $destinataire_id, $annonce_id, $message]);
        header('Location: messages.php?success=1');
        exit;
    }
}

// Marquer un message comme lu
if (isset($_GET['read'])) {
    $message_id = $_GET['read'];
    $stmt = $pdo->prepare("UPDATE messages SET lu = TRUE WHERE id = ? AND destinataire_id = ?");
    $stmt->execute([$message_id, $user_id]);
}

// Supprimer une conversation
if (isset($_GET['delete_conversation'])) {
    $other_user_id = $_GET['delete_conversation'];
    $stmt = $pdo->prepare("DELETE FROM messages WHERE (expediteur_id = ? AND destinataire_id = ?) OR (expediteur_id = ? AND destinataire_id = ?)");
    $stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id]);
    header('Location: messages.php');
    exit;
}

// Récupérer les conversations
$stmt = $pdo->prepare("SELECT DISTINCT 
                        CASE 
                            WHEN expediteur_id = ? THEN destinataire_id
                            ELSE expediteur_id
                        END as other_user_id,
                        u.nom as other_user_name,
                        (SELECT message FROM messages WHERE 
                            (expediteur_id = ? AND destinataire_id = other_user_id) OR 
                            (expediteur_id = other_user_id AND destinataire_id = ?) 
                         ORDER BY date_envoi DESC LIMIT 1) as last_message,
                        (SELECT date_envoi FROM messages WHERE 
                            (expediteur_id = ? AND destinataire_id = other_user_id) OR 
                            (expediteur_id = other_user_id AND destinataire_id = ?) 
                         ORDER BY date_envoi DESC LIMIT 1) as last_date,
                        (SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND expediteur_id = other_user_id AND lu = FALSE) as unread
                       FROM messages m
                       JOIN users u ON (u.id = CASE WHEN expediteur_id = ? THEN destinataire_id ELSE expediteur_id END)
                       WHERE expediteur_id = ? OR destinataire_id = ?
                       ORDER BY last_date DESC");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

// Récupérer les messages d'une conversation spécifique
$current_conversation = null;
$messages = [];
if (isset($_GET['chat_with'])) {
    $other_user_id = $_GET['chat_with'];
    
    // Marquer comme lus
    $stmt = $pdo->prepare("UPDATE messages SET lu = TRUE WHERE expediteur_id = ? AND destinataire_id = ?");
    $stmt->execute([$other_user_id, $user_id]);
    
    // Récupérer les messages
    $stmt = $pdo->prepare("SELECT m.*, u.nom as expediteur_nom 
                           FROM messages m
                           JOIN users u ON m.expediteur_id = u.id
                           WHERE (expediteur_id = ? AND destinataire_id = ?) OR (expediteur_id = ? AND destinataire_id = ?)
                           ORDER BY m.date_envoi ASC");
    $stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id]);
    $messages = $stmt->fetchAll();
    
    // Récupérer les infos de l'autre utilisateur
    $stmt = $pdo->prepare("SELECT nom, telephone, email FROM users WHERE id = ?");
    $stmt->execute([$other_user_id]);
    $current_conversation = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .conversation-list {
            max-height: 600px;
            overflow-y: auto;
        }
        .conversation-item {
            cursor: pointer;
            transition: background 0.2s;
            border-left: 3px solid transparent;
        }
        .conversation-item:hover, .conversation-item.active {
            background: #f8f9fa;
            border-left-color: #ffc107;
        }
        .unread-badge {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }
        .message {
            margin-bottom: 15px;
            display: flex;
        }
        .message.sent {
            justify-content: flex-end;
        }
        .message.received {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            position: relative;
        }
        .message.sent .message-bubble {
            background: #ffc107;
            color: #000;
        }
        .message.received .message-bubble {
            background: white;
            border: 1px solid #dee2e6;
        }
        .message-time {
            font-size: 0.7rem;
            margin-top: 5px;
            opacity: 0.7;
        }
        .online-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .online {
            background: #28a745;
        }
        .offline {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> SenAutoMarket
            </a>
            <a href="index.php" class="btn btn-outline-light">Accueil</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <!-- Liste des conversations -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-comments"></i> Conversations</h5>
                    </div>
                    <div class="conversation-list">
                        <?php if (empty($conversations)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune conversation</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $conv): ?>
                                <a href="messages.php?chat_with=<?= $conv['other_user_id'] ?>" 
                                   class="text-decoration-none text-dark">
                                    <div class="conversation-item p-3 <?= (isset($_GET['chat_with']) && $_GET['chat_with'] == $conv['other_user_id']) ? 'active' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?= htmlspecialchars($conv['other_user_name']) ?>
                                                    <?php if ($conv['unread'] > 0): ?>
                                                        <span class="unread-badge"><?= $conv['unread'] ?></span>
                                                    <?php endif; ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars(substr($conv['last_message'], 0, 50)) ?>
                                                    <?= strlen($conv['last_message']) > 50 ? '...' : '' ?>
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('d/m H:i', strtotime($conv['last_date'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Zone de chat -->
            <div class="col-md-8">
                <?php if ($current_conversation): ?>
                    <div class="card">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-circle"></i> 
                                        <?= htmlspecialchars($current_conversation['nom']) ?>
                                    </h5>
                                    <small class="text-muted">
                                        <i class="fas fa-phone"></i> <?= $current_conversation['telephone'] ?? 'Non renseigné' ?>
                                    </small>
                                </div>
                                <div>
                                    <a href="messages.php?delete_conversation=<?= $_GET['chat_with'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Supprimer cette conversation ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chat-messages" id="chatMessages">
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?= $msg['expediteur_id'] == $user_id ? 'sent' : 'received' ?>">
                                    <div class="message-bubble">
                                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                        <div class="message-time">
                                            <?= date('H:i', strtotime($msg['date_envoi'])) ?>
                                            <?php if ($msg['expediteur_id'] == $user_id): ?>
                                                <i class="fas fa-check <?= $msg['lu'] ? 'text-primary' : 'text-muted' ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="card-footer">
                            <form method="POST" action="" id="messageForm">
                                <input type="hidden" name="destinataire_id" value="<?= $_GET['chat_with'] ?>">
                                <input type="hidden" name="annonce_id" value="<?= $_GET['annonce_id'] ?? '' ?>">
                                <input type="hidden" name="send_message" value="1">
                                <div class="input-group">
                                    <textarea name="message" class="form-control" rows="2" 
                                              placeholder="Écrivez votre message..." required></textarea>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-paper-plane"></i> Envoyer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-comment-dots fa-4x text-muted mb-3"></i>
                            <h5>Sélectionnez une conversation</h5>
                            <p class="text-muted">Choisissez une conversation pour commencer à discuter</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll automatique vers le bas des messages
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Auto-refresh des messages toutes les 30 secondes
        <?php if (isset($_GET['chat_with'])): ?>
        setInterval(function() {
            location.reload();
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>