<?php
// Database connection details
$db_host = '127.0.0.1';
$db_user = 'root';
$db_password = 'root';
$db_db = 'mysql';
$db_port = 3306;

function debug_log($message) {
    error_log(print_r($message, true), 3, "debug.log");
}

// Net hesaplama fonksiyonu
function calculateNet($dogru, $yanlis) {
    return max(0, $dogru - ($yanlis * 0.25));
}


// Create a new mysqli instance
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_db, $db_port);

// Check for connection errors
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Assume user is logged in and has an ID of 1 (you should replace this with actual user authentication)
$user_id = 1;

// Fetch user's current target if exists
$stmt = $mysqli->prepare("SELECT * FROM wp_yks_targets WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$target = $result->fetch_assoc();

$message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'tyt_turkce', 'tyt_sosyal_b', 'tyt_t_matematik', 'tyt_fen_b',
        'ayt_matematik', 'ayt_fizik', 'ayt_kimya', 'ayt_biyoloji',
        'ayt_turk_dili_ve_edebiyati', 'ayt_tarih1', 'ayt_cografya1',
        'ayt_tarih2', 'ayt_cografya2', 'ayt_felsefe_grubu',
        'ayt_dkab', 'ydt'
    ];

    $insertFields = ['user_id'];
    $updateFields = [];
    $values = [$user_id];
    $types = 'i'; // for user_id

    foreach ($fields as $field) {
        $insertFields[] = "{$field}_dogru";
        $insertFields[] = "{$field}_yanlis";
        $updateFields[] = "{$field}_dogru = VALUES({$field}_dogru)";
        $updateFields[] = "{$field}_yanlis = VALUES({$field}_yanlis)";
        $values[] = !empty($_POST["{$field}_dogru"]) ? intval($_POST["{$field}_dogru"]) : 0;
        $values[] = !empty($_POST["{$field}_yanlis"]) ? intval($_POST["{$field}_yanlis"]) : 0;
        $types .= 'ii';
    }

    $insertFieldsStr = implode(', ', $insertFields);
    $updateFieldsStr = implode(', ', $updateFields);
    $placeholders = rtrim(str_repeat('?,', count($values)), ',');
    
    $query = "INSERT INTO wp_yks_targets ($insertFieldsStr) 
              VALUES ($placeholders)
              ON DUPLICATE KEY UPDATE $updateFieldsStr";

    debug_log("Query: " . $query);
    debug_log("Types: " . $types);
    debug_log("Values count: " . count($values));
    debug_log("Values: " . print_r($values, true));

    $stmt = $mysqli->prepare($query);
    
    if ($stmt) {
        $bindParams = array($types);
        foreach ($values as $key => $value) {
            $bindParams[] = &$values[$key];
        }
        
        try {
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            
            if ($stmt->execute()) {
                $message = "Hedef başarıyla kaydedildi.";
            } else {
                $message = "Hedef kaydedilirken bir hata oluştu: " . $stmt->error;
            }
        } catch (Exception $e) {
            $message = "Bağlama hatası: " . $e->getMessage();
            debug_log("Bağlama hatası: " . $e->getMessage());
        }
        
        $stmt->close();
    } else {
        $message = "Sorgu hazırlanırken bir hata oluştu: " . $mysqli->error;
    }

    debug_log("Final message: " . $message);
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hedef Belirleme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <style>
        .net-score {
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
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
        <h1 class="text-center mb-4 mt-5 text-dark">Hedef Belirleme</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (empty($target)): ?>
            <div class="alert alert-warning">Lütfen hedefinizi giriniz.</div>
        <?php endif; ?>
        
        <form id="targetForm" method="POST">
            <!-- TYT Hedefi -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">TYT Hedefi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $tytDersler = ['Türkçe' => 'turkce', 'Sosyal B.' => 'sosyal_b', 'T. Matematik' => 't_matematik', 'Fen B.' => 'fen_b'];
                        foreach ($tytDersler as $ders => $fieldName) {
                            $dogru = $target['tyt_' . $fieldName . '_dogru'] ?? '';
                            $yanlis = $target['tyt_' . $fieldName . '_yanlis'] ?? '';
                            $net = calculateNet($dogru, $yanlis);
                            echo '<div class="col-md-6 col-lg-3 mb-3">
                                <h6>' . $ders . '</h6>
                                <div class="input-group">
                                    <input type="number" class="form-control dogru-input" name="tyt_' . $fieldName . '_dogru" placeholder="Doğru" min="0" max="40" value="' . $dogru . '" data-field="tyt_' . $fieldName . '">
                                    <input type="number" class="form-control yanlis-input" name="tyt_' . $fieldName . '_yanlis" placeholder="Yanlış" min="0" max="40" value="' . $yanlis . '" data-field="tyt_' . $fieldName . '">
                                </div>
                                <small class="net-score" id="tyt_' . $fieldName . '_net">Net: ' . number_format($net, 2) . '</small>
                            </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

<!-- AYT Hedefi -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">AYT Hedefi</h5>
    </div>
    <div class="card-body">
        <!-- Sözel Bölümü -->
        <div class="card mb-3">
        <div class="card-header bg-white ayt-section">
                <h5 class="mb-0">Sözel</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $aytSozelDersler = [
                        'Türk Dili ve Edebiyatı' => ['turk_dili_ve_edebiyati', 24],
                        'Tarih-1' => ['tarih1', 10],
                        'Coğrafya-1' => ['cografya1', 6]
                    ];
                    foreach ($aytSozelDersler as $ders => $info) {
                        $fieldName = $info[0];
                        $maxSoru = $info[1];
                        $dogru = $target['ayt_' . $fieldName . '_dogru'] ?? '';
                        $yanlis = $target['ayt_' . $fieldName . '_yanlis'] ?? '';
                        $net = calculateNet($dogru, $yanlis);
                        echo '<div class="col-md-4 mb-3">
                            <h6>' . $ders . '</h6>
                            <div class="input-group mb-2">
                                <input type="number" class="form-control dogru-input" name="ayt_' . $fieldName . '_dogru" placeholder="Doğru" min="0" max="' . $maxSoru . '" value="' . $dogru . '" data-field="ayt_' . $fieldName . '">
                                <input type="number" class="form-control yanlis-input" name="ayt_' . $fieldName . '_yanlis" placeholder="Yanlış" min="0" max="' . $maxSoru . '" value="' . $yanlis . '" data-field="ayt_' . $fieldName . '">
                            </div>
                            <small class="net-score" id="ayt_' . $fieldName . '_net">Net: ' . number_format($net, 2) . '</small>
                        </div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Sosyal Bilimler Bölümü -->
        <div class="card mb-3">
            <div class="card-header bg-white ayt-section">
                <h5 class="mb-0">Sosyal Bilimler</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $aytSosyalDersler = [
                        'Tarih-2' => ['tarih2', 11],
                        'Coğrafya-2' => ['cografya2', 11],
                        'Felsefe Grubu' => ['felsefe_grubu', 12],
                        'Din Kültürü ve Ahlak Bilgisi' => ['dkab', 6]
                    ];
                    foreach ($aytSosyalDersler as $ders => $info) {
                        $fieldName = $info[0];
                        $maxSoru = $info[1];
                        $dogru = $target['ayt_' . $fieldName . '_dogru'] ?? '';
                        $yanlis = $target['ayt_' . $fieldName . '_yanlis'] ?? '';
                        $net = calculateNet($dogru, $yanlis);
                        echo '<div class="col-md-3 mb-3">
                            <h6>' . $ders . '</h6>
                            <div class="input-group mb-2">
                                <input type="number" class="form-control dogru-input" name="ayt_' . $fieldName . '_dogru" placeholder="Doğru" min="0" max="' . $maxSoru . '" value="' . $dogru . '" data-field="ayt_' . $fieldName . '">
                                <input type="number" class="form-control yanlis-input" name="ayt_' . $fieldName . '_yanlis" placeholder="Yanlış" min="0" max="' . $maxSoru . '" value="' . $yanlis . '" data-field="ayt_' . $fieldName . '">
                            </div>
                            <small class="net-score" id="ayt_' . $fieldName . '_net">Net: ' . number_format($net, 2) . '</small>
                        </div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Matematik ve Fen Bilimleri Bölümü -->
        <div class="card">
        <div class="card-header bg-white ayt-section">
                <h5 class="mb-0 ">Matematik ve Fen Bilimleri</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $aytFenDersler = [
                        'Matematik' => ['matematik', 40],
                        'Fizik' => ['fizik', 14],
                        'Kimya' => ['kimya', 13],
                        'Biyoloji' => ['biyoloji', 13]
                    ];
                    foreach ($aytFenDersler as $ders => $info) {
                        $fieldName = $info[0];
                        $maxSoru = $info[1];
                        $dogru = $target['ayt_' . $fieldName . '_dogru'] ?? '';
                        $yanlis = $target['ayt_' . $fieldName . '_yanlis'] ?? '';
                        $net = calculateNet($dogru, $yanlis);
                        echo '<div class="col-md-3 mb-3">
                            <h6>' . $ders . '</h6>
                            <div class="input-group mb-2">
                                <input type="number" class="form-control dogru-input" name="ayt_' . $fieldName . '_dogru" placeholder="Doğru" min="0" max="' . $maxSoru . '" value="' . $dogru . '" data-field="ayt_' . $fieldName . '">
                                <input type="number" class="form-control yanlis-input" name="ayt_' . $fieldName . '_yanlis" placeholder="Yanlış" min="0" max="' . $maxSoru . '" value="' . $yanlis . '" data-field="ayt_' . $fieldName . '">
                            </div>
                            <small class="net-score" id="ayt_' . $fieldName . '_net">Net: ' . number_format($net, 2) . '</small>
                        </div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
            <!-- YDT Hedefi -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">YDT Hedefi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-3">
                            <h6>Yabancı Dil Testi</h6>
                            <?php
                            $dogru = $target['ydt_dogru'] ?? '';
                            $yanlis = $target['ydt_yanlis'] ?? '';
                            $net = calculateNet($dogru, $yanlis);
                            ?>
                            <div class="input-group">
                                <input type="number" class="form-control dogru-input" name="ydt_dogru" placeholder="Doğru" min="0" max="80" value="<?php echo $dogru; ?>" data-field="ydt">
                                <input type="number" class="form-control yanlis-input" name="ydt_yanlis" placeholder="Yanlış" min="0" max="80" value="<?php echo $yanlis; ?>" data-field="ydt">
                            </div>
                            <small class="net-score" id="ydt_net">Net: <?php echo number_format($net, 2); ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Hedefimi Kaydet</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('targetForm');
            const inputs = form.querySelectorAll('.dogru-input, .yanlis-input');

            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateNet(this.getAttribute('data-field'));
                });
            });

            function updateNet(field) {
                const dogruInput = document.querySelector(`input[name="${field}_dogru"]`);
                const yanlisInput = document.querySelector(`input[name="${field}_yanlis"]`);
                const netDisplay = document.getElementById(`${field}_net`);

                if (dogruInput && yanlisInput && netDisplay) {
                    const dogru = parseInt(dogruInput.value) || 0;
                    const yanlis = parseInt(yanlisInput.value) || 0;
                    const net = Math.max(0, dogru - (yanlis * 0.25));
                    netDisplay.textContent = `Net: ${net.toFixed(2)}`;
                }
            }
        });
    </script>
</body>
</html>