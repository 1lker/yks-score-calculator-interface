<?php
// Database connection details
$db_host = '127.0.0.1';
$db_user = 'root';
$db_password = 'root';
$db_db = 'mysql';
$db_port = 3306;

$exams = [];
$target = null;

function turkceKarakterleriDonustur($string) {
    $bul = array('ü', 'Ü', 'ş', 'Ş', 'ç', 'Ç', 'ö', 'Ö', 'ğ', 'Ğ', 'ı', 'İ');
    $degistir = array('u', 'u', 's', 's', 'c', 'c', 'o', 'o', 'g', 'g', 'i', 'i');
    return strtolower(str_replace($bul, $degistir, $string));
}

// Try to connect to the database
try {
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_db, $db_port);

    if ($mysqli->connect_error) {
        throw new Exception('Connection failed: ' . $mysqli->connect_error);
    }

    // Fetch exams from the database
    $result = $mysqli->query("SELECT * FROM wp_yks_exams WHERE user_id = 1 ORDER BY exam_date ASC");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        $result->free();
    }

    // Fetch user's target
    $targetResult = $mysqli->query("SELECT * FROM wp_yks_targets WHERE user_id = 1");
    if ($targetResult) {
        $target = $targetResult->fetch_assoc();
        $targetResult->free();
    }

    $mysqli->close();
} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}

function calculateNet($dogru, $yanlis) {
    return max(0, $dogru - ($yanlis * 0.25));
}

$lessons = [
    'TYT' => ['turkce', 'sosyal_b', 't_matematik', 'fen_b'],
    'AYT' => ['matematik', 'fizik', 'kimya', 'biyoloji', 'turk_dili_ve_edebiyati', 'tarih1', 'cografya1', 'tarih2', 'cografya2', 'felsefe_grubu', 'dkab'],
    'YDT' => ['yabanci_dil']
];

$lessonData = [];
$dates = [];
$tytNetScores = [];
$aytSayNetScores = [];
$aytSozNetScores = [];
$aytEaNetScores = [];
$ydtNetScores = [];

foreach ($exams as $exam) {
    $dates[] = $exam['exam_date'];
    
    // TYT
    $tytNet = 0;
    foreach ($lessons['TYT'] as $lesson) {
        $tytNet += calculateNet($exam['tyt_' . $lesson . '_dogru'] ?? 0, $exam['tyt_' . $lesson . '_yanlis'] ?? 0);
    }
    $tytNetScores[] = $tytNet;

    // AYT SAY
    $aytSayNet = 0;
    foreach (['matematik', 'fizik', 'kimya', 'biyoloji'] as $lesson) {
        $aytSayNet += calculateNet($exam['ayt_' . $lesson . '_dogru'] ?? 0, $exam['ayt_' . $lesson . '_yanlis'] ?? 0);
    }
    $aytSayNetScores[] = $aytSayNet;

    // AYT SOZ
    $aytSozNet = 0;
    foreach (['turk_dili_ve_edebiyati', 'tarih1', 'cografya1', 'tarih2', 'cografya2', 'felsefe_grubu', 'dkab'] as $lesson) {
        $aytSozNet += calculateNet($exam['ayt_' . $lesson . '_dogru'] ?? 0, $exam['ayt_' . $lesson . '_yanlis'] ?? 0);
    }
    $aytSozNetScores[] = $aytSozNet;

    // AYT EA
    $aytEaNet = 0;
    foreach (['matematik', 'turk_dili_ve_edebiyati', 'tarih1', 'cografya1'] as $lesson) {
        $aytEaNet += calculateNet($exam['ayt_' . $lesson . '_dogru'] ?? 0, $exam['ayt_' . $lesson . '_yanlis'] ?? 0);
    }
    $aytEaNetScores[] = $aytEaNet;

    // YDT
    $ydtNet = calculateNet($exam['ydt_dogru'] ?? 0, $exam['ydt_yanlis'] ?? 0);
    $ydtNetScores[] = $ydtNet;

    // Lesson data
    foreach ($lessons as $examType => $examLessons) {
        foreach ($examLessons as $lesson) {
            $lessonData[$examType][$lesson][] = calculateNet(
                $exam[strtolower($examType) . '_' . $lesson . '_dogru'] ?? 0,
                $exam[strtolower($examType) . '_' . $lesson . '_yanlis'] ?? 0
            );
        }
    }
}

function calculateImprovementRate($scores) {
    if (count($scores) < 2) return 0;
    $firstScore = $scores[0];
    $lastScore = end($scores);
    if ($firstScore == 0) {
        return $lastScore > 0 ? 100 : 0;
    }
    return ($lastScore - $firstScore) / abs($firstScore) * 100;
}

function safeAverage($scores) {
    $count = count($scores);
    return $count > 0 ? array_sum($scores) / $count : 0;
}

$tytImprovementRate = calculateImprovementRate($tytNetScores);
$aytSayImprovementRate = calculateImprovementRate($aytSayNetScores);
$aytSozImprovementRate = calculateImprovementRate($aytSozNetScores);
$aytEaImprovementRate = calculateImprovementRate($aytEaNetScores);
$ydtImprovementRate = calculateImprovementRate($ydtNetScores);

$avgTYT = safeAverage($tytNetScores);
$avgAYTSay = safeAverage($aytSayNetScores);
$avgAYTSoz = safeAverage($aytSozNetScores);
$avgAYTEa = safeAverage($aytEaNetScores);
$avgYDT = safeAverage($ydtNetScores);

// Calculate target nets
$targetTYT = 0;
$targetAYTSay = 0;
$targetAYTSoz = 0;
$targetAYTEa = 0;
$targetYDT = 0;

if ($target) {
    foreach ($lessons['TYT'] as $lesson) {
        $targetTYT += calculateNet($target['tyt_' . $lesson . '_dogru'] ?? 0, $target['tyt_' . $lesson . '_yanlis'] ?? 0);
    }
    foreach (['matematik', 'fizik', 'kimya', 'biyoloji'] as $lesson) {
        $targetAYTSay += calculateNet($target['ayt_' . $lesson . '_dogru'] ?? 0, $target['ayt_' . $lesson . '_yanlis'] ?? 0);
    }
    foreach (['turk_dili_ve_edebiyati', 'tarih1', 'cografya1', 'tarih2', 'cografya2', 'felsefe_grubu', 'dkab'] as $lesson) {
        $targetAYTSoz += calculateNet($target['ayt_' . $lesson . '_dogru'] ?? 0, $target['ayt_' . $lesson . '_yanlis'] ?? 0);
    }
    foreach (['matematik', 'turk_dili_ve_edebiyati', 'tarih1', 'cografya1'] as $lesson) {
        $targetAYTEa += calculateNet($target['ayt_' . $lesson . '_dogru'] ?? 0, $target['ayt_' . $lesson . '_yanlis'] ?? 0);
    }
    $targetYDT = calculateNet($target['ydt_dogru'] ?? 0, $target['ydt_yanlis'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YKS Performans Analizi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles-statistics.css" rel="stylesheet">
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

<h1 class="text-center mb-5 mt-4 text-dark">YKS Performans Analizi</h1>
        
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">TYT Performansı</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="tytChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <p>Ortalama Net: <strong><?php echo number_format($avgTYT, 2); ?></strong></p>
                            <p>İlerleme Oranı: 
                                <span class="improvement-rate <?php echo $tytImprovementRate >= 0 ? 'positive-rate' : 'negative-rate'; ?>">
                                    <?php echo number_format($tytImprovementRate, 2); ?>%
                                </span>
                            </p>
                        </div>
                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="showLessonDetails('TYT')">Ders Bazlı Analiz</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">AYT SAY Performansı</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="aytSayChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <p>Ortalama Net: <strong><?php echo number_format($avgAYTSay, 2); ?></strong></p>
                            <p>İlerleme Oranı: 
                                <span class="improvement-rate <?php echo $aytSayImprovementRate >= 0 ? 'positive-rate' : 'negative-rate'; ?>">
                                    <?php echo number_format($aytSayImprovementRate, 2); ?>%
                                </span>
                            </p>
                        </div>
                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="showLessonDetails('AYT')">Ders Bazlı Analiz</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">AYT SÖZ Performansı</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="aytSozChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <p>Ortalama Net: <strong><?php echo number_format($avgAYTSoz, 2); ?></strong></p>
                            <p>İlerleme Oranı: 
                                <span class="improvement-rate <?php echo $aytSozImprovementRate >= 0 ? 'positive-rate' : 'negative-rate'; ?>">
                                    <?php echo number_format($aytSozImprovementRate, 2); ?>%
                                </span>
                            </p>
                        </div>
                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="showLessonDetails('AYT')">Ders Bazlı Analiz</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">AYT EA Performansı</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="aytEaChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <p>Ortalama Net: <strong><?php echo number_format($avgAYTEa, 2); ?></strong></p>
                            <p>İlerleme Oranı: 
                                <span class="improvement-rate <?php echo $aytEaImprovementRate >= 0 ? 'positive-rate' : 'negative-rate'; ?>">
                                    <?php echo number_format($aytEaImprovementRate, 2); ?>%
                                </span>
                            </p>
                        </div>
                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="showLessonDetails('AYT')">Ders Bazlı Analiz</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">YDT Performansı</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ydtChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <p>Ortalama Net: <strong><?php echo number_format($avgYDT, 2); ?></strong></p>
                            <p>İlerleme Oranı: 
                                <span class="improvement-rate <?php echo $ydtImprovementRate >= 0 ? 'positive-rate' : 'negative-rate'; ?>">
                                    <?php echo number_format($ydtImprovementRate, 2); ?>%
                                </span>
                            </p>
                        </div>
                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="showLessonDetails('YDT')">Ders Bazlı Analiz</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ders Bazlı Analiz Modal -->
    <div class="modal fade" id="lessonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ders Bazlı Analiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <canvas id="lessonChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script>
        const dates = <?php echo json_encode($dates); ?>;
        const tytNetScores = <?php echo json_encode($tytNetScores); ?>;
        const aytSayNetScores = <?php echo json_encode($aytSayNetScores); ?>;
        const aytSozNetScores = <?php echo json_encode($aytSozNetScores); ?>;
        const aytEaNetScores = <?php echo json_encode($aytEaNetScores); ?>;
        const ydtNetScores = <?php echo json_encode($ydtNetScores); ?>;
        const targetTYT = <?php echo $targetTYT; ?>;
        const targetAYTSay = <?php echo $targetAYTSay; ?>;
        const targetAYTSoz = <?php echo $targetAYTSoz; ?>;
        const targetAYTEa = <?php echo $targetAYTEa; ?>;
        const targetYDT = <?php echo $targetYDT; ?>;
        const lessonData = <?php echo json_encode($lessonData); ?>;

        function createChart(ctx, label, data, target, color) {
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: label,
                            data: data,
                            borderColor: color,
                            backgroundColor: color + '20',
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: color,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                        },
                        {
                            label: 'Hedef',
                            data: Array(dates.length).fill(target),
                            borderColor: '#ff6384',
                            borderDash: [5, 5],
                            tension: 0,
                            fill: false,
                            pointRadius: 0,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    family: "'Roboto', sans-serif"
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0,0,0,0.7)',
                            titleFont: {
                                size: 14,
                                family: "'Roboto', sans-serif",
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 12,
                                family: "'Roboto', sans-serif"
                            },
                            padding: 12,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 12,
                                    family: "'Roboto', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12,
                                    family: "'Roboto', sans-serif"
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }

        let lessonChart;

        function showLessonDetails(exam) {
            const modal = new bootstrap.Modal(document.getElementById('lessonModal'));
            modal.show();

            const ctx = document.getElementById('lessonChart').getContext('2d');
            if (lessonChart) {
                lessonChart.destroy();
            }

            const datasets = Object.entries(lessonData[exam]).map(([lesson, data], index) => ({
                label: lesson,
                data: data,
                borderColor: getRandomColor(),
                tension: 0.4,
                fill: false
            }));

            lessonChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: exam + ' Ders Bazlı Analiz'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tytCtx = document.getElementById('tytChart').getContext('2d');
            const aytSayCtx = document.getElementById('aytSayChart').getContext('2d');
            const aytSozCtx = document.getElementById('aytSozChart').getContext('2d');
            const aytEaCtx = document.getElementById('aytEaChart').getContext('2d');
            const ydtCtx = document.getElementById('ydtChart').getContext('2d');

            createChart(tytCtx, 'TYT Net', tytNetScores, targetTYT, '#4e73df');
            createChart(aytSayCtx, 'AYT SAY Net', aytSayNetScores, targetAYTSay, '#1cc88a');
            createChart(aytSozCtx, 'AYT SÖZ Net', aytSozNetScores, targetAYTSoz, '#36b9cc');
            createChart(aytEaCtx, 'AYT EA Net', aytEaNetScores, targetAYTEa, '#f6c23e');
            createChart(ydtCtx, 'YDT Net', ydtNetScores, targetYDT, '#858796');

            // Animasyon için
            anime({
                targets: '.card',
                translateY: [-30, 0],
                opacity: [0, 1],
                duration: 1500,
                delay: anime.stagger(200),
                easing: 'easeOutQuad'
            });

            anime({
                targets: '.improvement-rate',
                innerHTML: [0, el => parseFloat(el.innerHTML)],
                round: 2,
                duration: 2000,
                easing: 'easeInOutExpo'
            });
        });
    </script>
</body>
</html>