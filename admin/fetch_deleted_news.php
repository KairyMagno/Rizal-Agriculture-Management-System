<?php

require '../includes/db.php';

$stmt = $db->prepare("SELECT id, title, content FROM news WHERE deleted_at IS NOT NULL");
$stmt->execute();
$deletedNews = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($deletedNews);

?>