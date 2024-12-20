<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: /');
    exit;
}

$db = new Database();
$image = $db->getImage($id);

if (!$image) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查看圖片 - AI 圖片庫</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="fixed-header">
        <div class="button-group">
            <a href="/" class="button">返回</a>
            <a href="/edit.php?id=<?= $id ?>" class="button">編輯</a>
        </div>
    </div>

    <div class="container">
        <div class="image-container">
            <img src="/image.php?id=<?= $id ?>" alt="AI 生成圖片" class="image-preview">
        </div>

        <div class="image-details">
            <div class="form-group">
                <label>類型：</label>
                <div><?= htmlspecialchars($image['type']) ?></div>
            </div>

            <div class="form-group">
                <label>關鍵詞：</label>
                <div class="keyword-chips">
                    <?php foreach ($image['keywords'] as $keyword): ?>
                        <a href="/?keywords=<?= urlencode($keyword) ?>" class="keyword-chip">
                            <?= htmlspecialchars($keyword) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>詳細資訊：</label>
                <div class="details-container">
                    <?php if ($image['type'] === 'SD'): ?>
                        <button class="action-button" onclick="copyDetails()">複製</button>
                    <?php else: ?>
                        <button class="action-button" onclick="downloadJSON()">下載 JSON</button>
                    <?php endif; ?>
                    <div style="white-space: pre-wrap;"><?= htmlspecialchars($image['details']) ?></div>
                </div>
            </div>

            <?php if ($image['is_hidden']): ?>
                <div class="form-group">
                    <div class="keyword-chip">R18</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const details = <?= json_encode($image['details']) ?>;

        function copyDetails() {
            navigator.clipboard.writeText(details).then(() => {
                const button = document.querySelector('.action-button');
                button.textContent = '已複製';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.textContent = '複製';
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('複製失敗：', err);
                alert('複製失敗');
            });
        }

        function downloadJSON() {
            try {
                // Parse the details as JSON to validate it
                const jsonData = JSON.parse(details);
                
                // Create a Blob with the JSON data
                const blob = new Blob([JSON.stringify(jsonData, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                
                // Create a temporary link and trigger download
                const a = document.createElement('a');
                a.href = url;
                a.download = 'workflow.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } catch (err) {
                console.error('JSON 解析失敗：', err);
                alert('無法下載 JSON：詳細資訊不是有效的 JSON 格式');
            }
        }
    </script>
</body>
</html>
