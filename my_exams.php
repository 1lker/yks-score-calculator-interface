<?php
// Database connection details
$db_host = '127.0.0.1';
$db_user = 'root';
$db_password = 'root';
$db_db = 'mysql';
$db_port = 3306;

$exams = [];

// Try to connect to the database
try {
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_db, $db_port);

    // Check for connection errors
    if ($mysqli->connect_error) {
        throw new Exception('Connection failed: ' . $mysqli->connect_error);
    }

    // Fetch exams from the database
    $result = $mysqli->query("SELECT * FROM wp_yks_exams WHERE user_id = 1");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $exams[] = [
                'id' => $row['id'],  // Add this line to store the exam id
                'examName' => $row['exam_name'],
                'examDate' => $row['exam_date'],
                'scores' => [
                    'tyt' => [
                        'turkce' => ['dogru' => $row['tyt_turkce_dogru'], 'yanlis' => $row['tyt_turkce_yanlis']],
                        'sosyal' => ['dogru' => $row['tyt_sosyal_b_dogru'], 'yanlis' => $row['tyt_sosyal_b_yanlis']],
                        'matematik' => ['dogru' => $row['tyt_t_matematik_dogru'], 'yanlis' => $row['tyt_t_matematik_yanlis']],
                        'fen' => ['dogru' => $row['tyt_fen_b_dogru'], 'yanlis' => $row['tyt_fen_b_yanlis']]
                    ],
                    'ayt' => [
                        'matematik' => ['dogru' => $row['ayt_matematik_dogru'], 'yanlis' => $row['ayt_matematik_yanlis']],
                        'fizik' => ['dogru' => $row['ayt_fizik_dogru'], 'yanlis' => $row['ayt_fizik_yanlis']],
                        'kimya' => ['dogru' => $row['ayt_kimya_dogru'], 'yanlis' => $row['ayt_kimya_yanlis']],
                        'biyoloji' => ['dogru' => $row['ayt_biyoloji_dogru'], 'yanlis' => $row['ayt_biyoloji_yanlis']],
                        'edebiyat' => ['dogru' => $row['ayt_turk_dili_ve_edebiyati_dogru'], 'yanlis' => $row['ayt_turk_dili_ve_edebiyati_yanlis']],
                        'tarih1' => ['dogru' => $row['ayt_tarih1_dogru'], 'yanlis' => $row['ayt_tarih1_yanlis']],
                        'cografya1' => ['dogru' => $row['ayt_cografya1_dogru'], 'yanlis' => $row['ayt_cografya1_yanlis']],
                        'tarih2' => ['dogru' => $row['ayt_tarih2_dogru'], 'yanlis' => $row['ayt_tarih2_yanlis']],
                        'cografya2' => ['dogru' => $row['ayt_cografya2_dogru'], 'yanlis' => $row['ayt_cografya2_yanlis']],
                        'felsefe' => ['dogru' => $row['ayt_felsefe_grubu_dogru'], 'yanlis' => $row['ayt_felsefe_grubu_yanlis']],
                        'din' => ['dogru' => $row['ayt_dkab_dogru'], 'yanlis' => $row['ayt_dkab_yanlis']]
                    ],
                    'ydt' => [
                        'ydt' => ['dogru' => $row['ydt_dogru'], 'yanlis' => $row['ydt_yanlis']]
                    ]
                ]
            ];
        }
        $result->free();
    }

    $mysqli->close();
} catch (Exception $e) {
    // If database connection fails, use JSON file
    $exams = json_decode(file_get_contents('exams.json'), true) ?? [];
}

// Silme işlemi için yeni bir fonksiyon ekleyelim
function deleteExam($examId) {
    global $mysqli;
    $mysqli = new mysqli($GLOBALS['db_host'], $GLOBALS['db_user'], $GLOBALS['db_password'], $GLOBALS['db_db'], $GLOBALS['db_port']);
    $stmt = $mysqli->prepare("DELETE FROM wp_yks_exams WHERE id = ? AND user_id = 1");
    $stmt->bind_param("i", $examId);
    $result = $stmt->execute();
    $stmt->close();
    $mysqli->close();
    return $result;
}

// AJAX isteği kontrolü
if (isset($_POST['action']) && $_POST['action'] == 'delete_exam') {
    $examId = intval($_POST['exam_id']);
    $result = deleteExam($examId);
    echo json_encode(['success' => $result]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sınavlarım</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles-my-exams.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .card-header {
            cursor: pointer;
        }
        .card-header:hover {
            background-color: #f8f9fa;
        }
        .card-body ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        

    <div class="container-fluid py-5">
    <div class="row justify-content-center g-4">
        <div class="col-md-6 col-lg-3">
            <a href="index.php" class="text-decoration-none">
                <div class="ultra-modern-card" data-tilt>
                    <div class="card-content">
                        <div class="icon-container">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <h5 class="card-title">Hesapla</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="my_exams.php" class="text-decoration-none">
                <div class="ultra-modern-card" data-tilt>
                    <div class="card-content">
                        <div class="icon-container">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <h5 class="card-title">Sınavlarım</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="target.php" class="text-decoration-none">
                <div class="ultra-modern-card" data-tilt>
                    <div class="card-content">
                        <div class="icon-container">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <h5 class="card-title">Hedefim</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="statistics.php" class="text-decoration-none">
                <div class="ultra-modern-card" data-tilt>
                    <div class="card-content">
                        <div class="icon-container">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h5 class="card-title">İstatistiklerim</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
        <h1 class="text-center mb-4 title">Sınavlarım</h1>
        <div class="row">
            <?php foreach ($exams as $exam): ?>
            <div class="col-md-12 mb-4 exam-card" data-exam-id="<?php echo $exam['id']; ?>">
                <div class="card">
                    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $exam['id']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo htmlspecialchars($exam['examName']); ?></h5>
                                <small><?php echo htmlspecialchars($exam['examDate']); ?></small>
                            </div>
                            <div>
                                <button class="btn btn-danger btn-sm delete-exam">Sil</button>
                                <button class="btn btn-info btn-sm">Detaylar</button>
                            </div>
                        </div>
                    </div>
                    <div id="details-<?php echo $exam['id']; ?>" class="collapse">
                        <div class="card-body">
                            <p><strong>TYT:</strong></p>
                            <ul>
                                <li>Türkçe: <?php echo htmlspecialchars($exam['scores']['tyt']['turkce']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['tyt']['turkce']['yanlis']); ?> yanlış</li>
                                <li>Sosyal: <?php echo htmlspecialchars($exam['scores']['tyt']['sosyal']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['tyt']['sosyal']['yanlis']); ?> yanlış</li>
                                <li>Matematik: <?php echo htmlspecialchars($exam['scores']['tyt']['matematik']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['tyt']['matematik']['yanlis']); ?> yanlış</li>
                                <li>Fen: <?php echo htmlspecialchars($exam['scores']['tyt']['fen']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['tyt']['fen']['yanlis']); ?> yanlış</li>
                            </ul>
                            <p><strong>AYT SAY:</strong></p>
                            <ul>
                                <li>Matematik: <?php echo htmlspecialchars($exam['scores']['ayt']['matematik']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['matematik']['yanlis']); ?> yanlış</li>
                                <li>Fizik: <?php echo htmlspecialchars($exam['scores']['ayt']['fizik']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['fizik']['yanlis']); ?> yanlış</li>
                                <li>Kimya: <?php echo htmlspecialchars($exam['scores']['ayt']['kimya']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['kimya']['yanlis']); ?> yanlış</li>
                                <li>Biyoloji: <?php echo htmlspecialchars($exam['scores']['ayt']['biyoloji']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['biyoloji']['yanlis']); ?> yanlış</li>
                            </ul>
                            <p><strong>AYT SÖZ:</strong></p>
                            <ul>
                                <li>Edebiyat: <?php echo htmlspecialchars($exam['scores']['ayt']['edebiyat']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['edebiyat']['yanlis']); ?> yanlış</li>
                                <li>Tarih 1: <?php echo htmlspecialchars($exam['scores']['ayt']['tarih1']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['tarih1']['yanlis']); ?> yanlış</li>
                                <li>Coğrafya 1: <?php echo htmlspecialchars($exam['scores']['ayt']['cografya1']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['cografya1']['yanlis']); ?> yanlış</li>
                                <li>Tarih 2: <?php echo htmlspecialchars($exam['scores']['ayt']['tarih2']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['tarih2']['yanlis']); ?> yanlış</li>
                                <li>Coğrafya 2: <?php echo htmlspecialchars($exam['scores']['ayt']['cografya2']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['cografya2']['yanlis']); ?> yanlış</li>
                                <li>Felsefe: <?php echo htmlspecialchars($exam['scores']['ayt']['felsefe']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['felsefe']['yanlis']); ?> yanlış</li>
                                <li>DİN: <?php echo htmlspecialchars($exam['scores']['ayt']['din']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ayt']['din']['yanlis']); ?> yanlış</li>
                            </ul>
                            <p><strong>YDT:</strong></p>
                            <ul>
                                <li>YDT: <?php echo htmlspecialchars($exam['scores']['ydt']['ydt']['dogru']); ?> doğru, <?php echo htmlspecialchars($exam['scores']['ydt']['ydt']['yanlis']); ?> yanlış</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sınavı Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bu sınavı silmek istediğinizden emin misiniz?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Sil</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">İşlem Sonucu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        let examToDelete = null;

        $('.delete-exam').on('click', function() {
            examToDelete = $(this).closest('.exam-card');
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });

        $('#confirmDelete').on('click', function() {
            if (examToDelete) {
                var examId = examToDelete.data('exam-id');
                $.ajax({
                    url: 'my_exams.php',
                    type: 'POST',
                    data: {
                        action: 'delete_exam',
                        exam_id: examId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            examToDelete.remove();
                            showModal('Sınav başarıyla silindi.');
                        } else {
                            showModal('Sınav silinirken bir hata oluştu.');
                        }
                    },
                    error: function() {
                        showModal('İşlem sırasında bir hata oluştu.');
                    }
                });
                var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                deleteModal.hide();
            }
        });

        function showModal(message) {
            $('#modalMessage').text(message);
            var modal = new bootstrap.Modal(document.getElementById('resultModal'));
            modal.show();
        }
    });
    </script>
</body>
</html>